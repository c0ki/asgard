<?php

//namespace Symfony\Component\HttpFoundation\File;
namespace LogAnalyserBundle\Component\File;

use Symfony\Component\HttpFoundation\File\File as BaseFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

/**
 * A file in the file system or access via ssh.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @api
 */
class File extends BaseFile
{

    /**
     * Constructs a new file from the given path.
     *
     * @param string $path The path to the file
     */
    public function __construct($path)
    {
        if (preg_match('#^ssh://(.*)$#', $path, $matches)) {
            $path = 'ssh2.sftp://' . $matches[1];
        }
        parent::__construct($path, false);
        $this->setInfoClass(__CLASS__);
    }

    public function listFiles()
    {
        if (!$this->isReadable()) {
            throw new AccessDeniedException($this->getPathname());
        }
        elseif (!$this->isDir()) {
            throw new FileException("The file {$this->getPathname()} is not a directory");
        }
        $files = scandir($this->getPathname());
        $files = array_values(array_filter($files, function ($filename) {
            return ($filename != '.' && $filename != '..');
        }));
        array_walk($files, function (&$file) {
            $file = new self($this->getPathname() . DIRECTORY_SEPARATOR . $file);
            if (!$file->isReadable()) {
                $file = null;
            }
        });
        $files = array_values(array_filter($files));
        return $files;
    }

    public function getSize($humanReadable = false)
    {
        if (!$this->isReadable()) {
            throw new AccessDeniedException($this->getPathname());
        }
        elseif ($this->isDir()) {
            throw new FileException("The file {$this->getPathname()} is a directory");
        }
        $size = parent::getSize();
        if ($humanReadable) {
            $units = array('K', 'M', 'G');
            $unit = null;
            while ($size > 1024) {
                $size = $size / 1024;
                $unit = array_shift($units);
            }
            if ($unit) {
                $size = number_format($size, 1) . $unit;
            }
        }
        return $size;
    }

    /**
     * return bool
     */
    public function isReadable()
    {
        return true;
//        Stream error, always return false :-(
//        return parent::isReadable();
    }

    public function copy($directory, $name = null)
    {
        $target = $this->getTargetFile($directory, $name);

        if (!@copy($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }
        @chmod($target, 0666 & ~umask());

        return $target;
    }

}
