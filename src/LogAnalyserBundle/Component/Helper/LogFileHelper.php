<?php

namespace LogAnalyserBundle\Component\Helper;

use Symfony\Component\DependencyInjection\ContainerInterface;
use LogAnalyserBundle\Component\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LogFileHelper
{

    const TYPE_APACHE_ACCESSLOG = 'apacheaccesslog';
    const TYPE_APACHE_ERRORLOG = 'apacheerrorlog';
    const TYPE_EZ_LOG = 'ezlog';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getLogType(File $logFile)
    {
        $firstLine = $logFile->openFile()->fgets();
//        var_dump($firstLine);
//        preg_match('#^\[\w{3} \w{3} \d{2} \d{2}:\d{2}:\d{2} \d{4}\] \[\w+\] \[client \d+\.\d+\.\d+\.\d+\] .*$#', $firstLine, $matches);
//        preg_match('#^\[\w{3} \w{3} \d{2} \d{2}:\d{2}:\d{2} \d{4}\] \[\w+\] (\[client \d+\.\d+\.\d+\.\d+\])?.*$#', $firstLine, $matches);
//        var_dump($matches);
//        exit();

        if (preg_match('#^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] .*$#', $firstLine)) {
            return self::TYPE_EZ_LOG;
        }
        elseif (preg_match('#^\d+\.\d+\.\d+\.\d+[\s-]*\[\d{2}/\w{3}/\d{4}:\d{2}:\d{2}:\d{2}(\s+[\+\-]?\d+)?\] "[^"]*" \d+ \d+ "[^"]*" "[^"]*"$#', $firstLine)) {
            return self::TYPE_APACHE_ACCESSLOG;
        }
        elseif (preg_match('#^\[\w{3} \w{3} \d{2} \d{2}:\d{2}:\d{2} \d{4}\] \[\w+\] (\[client \d+\.\d+\.\d+\.\d+\])?.*$#', $firstLine)) {
            return self::TYPE_APACHE_ERRORLOG;
        }

        throw new FileException(sprintf('Undefined log file type "%s"', $logFile->getPathname()));
    }

    public function getLogInfo(File $logFile, $type = null)
    {
        if (empty($type)) {
            $type = $this->getLogType($logFile);
        }

        $logInfo = array(
            'dateStart' => null,
            'dateEnd' => null,
            'type' => $type,
            'typeLabel' => null,
        );

        if ($type == self::TYPE_APACHE_ACCESSLOG) {
            $logInfo['typeLabel'] = 'access.log';
            $firstLine = $logFile->openFile()->fgets();
            if (preg_match('#\[(\d+/\w+/\d+:\d+:\d+:\d+(\s+[\+\-]?\d+)?)\]#', $firstLine, $matches)) {
                $logInfo['dateStart'] = strtotime($matches[1]);
            }
            $lastLine = `tail -n 1 {$logFile->getPathname()}`;
            if (preg_match('#\[(\d+/\w+/\d+:\d+:\d+:\d+(\s+[\+\-]?\d+)?)\]#', $lastLine, $matches)) {
                $logInfo['dateEnd'] = strtotime($matches[1]);
            }
        }
        elseif ($type == self::TYPE_APACHE_ERRORLOG) {
            $logInfo['typeLabel'] = 'error.log';
            $firstLine = $logFile->openFile()->fgets();
            if (preg_match('#\[(\w{3} \w{3} \d{2} \d{2}:\d{2}:\d{2} \d{4})\]#', $firstLine, $matches)) {
                $logInfo['dateStart'] = strtotime($matches[1]);
            }
            $lastLine = `tail -n 1 {$logFile->getPathname()}`;
            if (preg_match('#\[(\w{3} \w{3} \d{2} \d{2}:\d{2}:\d{2} \d{4})\]#', $lastLine, $matches)) {
                $logInfo['dateEnd'] = strtotime($matches[1]);
            }
        }

        return $logInfo;
    }

    public function analyseLog(File $logFile, $type = null, $data)
    {
        if (empty($type)) {
            $type = $this->getLogType($logFile);
        }

        $firstCommand = "cat {$logFile->getPathname()}";
        if ((!isset($data['static']) || !$data['static'])
            && ($type == self::TYPE_APACHE_ACCESSLOG
                || $type == self::TYPE_APACHE_ERRORLOG)
        ) {
            $firstCommand = "awk '($7 !~ /(\.css|\.js|\.jpg|\.gif|\.png|\.ttf)$/)' {$logFile->getPathname()}";
        }

        $results = null;

        if (isset($data['analyse'])) {
            switch ($data['analyse']) {
                case 'code' && $type == self::TYPE_APACHE_ACCESSLOG:
                    $results = `{$firstCommand} | cut -d'"' -f3 | cut -d' ' -f2 | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'code'];
                    break;
                case 'page':
                    $results =
                        `{$firstCommand} | cut -d'"' -f2,3 | awk '{print $4" "$2}' | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'code', 'page'];
                    break;
                case '404':
                    $results =
                        `{$firstCommand} | cut -d'"' -f2,3 | awk '$4=404{print $4" "$2}' | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'code', 'page'];
                    break;
                case 'type' && $type == self::TYPE_APACHE_ERRORLOG:
                    $results = `{$firstCommand} | cut -d"[" -f3 | cut -d] -f1 | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'type'];
                    break;
                case 'error' && $type == self::TYPE_APACHE_ERRORLOG:
                    $results = `{$firstCommand} | cut -d']' -f4- | cut -d' ' -f2- | awk -F', referer' '{print $1}' | sort | uniq -c | sort -rg`;
                    $keys = ['nb', 'erreur'];
                    break;
                case 'firstlines':
                    $results = `{$firstCommand} | head -n50`;
                    $keys = ['ligne'];
                    break;
                case 'lastlines':
                    $results = `{$firstCommand} | tail -n50`;
                    $keys = ['ligne'];
                    break;
            }

            if ($results) {
                $results = array_filter(explode("\n", $results));
                array_walk($results, function (&$value) use ($keys) {
                    $value = array_combine($keys, explode(' ', trim($value), count($keys)));
                });
            }
        }

        return $results;
    }

}
