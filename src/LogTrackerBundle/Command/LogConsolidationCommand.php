<?php

namespace LogTrackerBundle\Command;

use Core\CoreBundle\Component\Indexer\SolrIndexer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Core\CoreBundle\Component\File\File;

class LogConsolidationCommand extends ContainerAwareCommand
{
    /**
     * Logger
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var SolrIndexer
     */
    protected $indexer;

    /**
     * @var File[]
     */
    protected $logfiles = array();

    /**
     * @var array
     */
    protected $times = array();

    protected function configure() {
        $this
            ->setName("logtracker:logconsolidation")
            ->setDescription("Log consolidation")
            ->addOption('logfiles', 'l', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Log files');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->times = array(microtime(true));
        $this->logger = $this->getContainer()->get('monolog.logger.console');
        $this->indexer = $this->getContainer()->get('core.indexer.solr');
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        if ($input->hasOption('logfiles') && $input->getOption('logfiles')) {
            $this->logfiles = array();
            foreach ($input->getOption('logfiles') as $logfile) {
                $this->logfiles = new File($logfile);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        // Get directories
        $indexerDirectory = new File($this->getContainer()->getParameter('indexer.logfiles.directories'));
        $tmpDirectory = new File($indexerDirectory->getPathname() . DIRECTORY_SEPARATOR . 'tmp', false);
        $saveDirectory = new File($indexerDirectory->getPathname() . DIRECTORY_SEPARATOR . 'save' . DIRECTORY_SEPARATOR . date('YmdHis', $this->times[0]), false);

        if (!empty($this->logfiles)) {
            if (!$tmpDirectory->isDir()) {
                mkdir($tmpDirectory->getPathname());
            }
            foreach ($indexerDirectory->listFiles() as $file) {
                $file->move($tmpDirectory->getPathname());
            }
            foreach ($this->logfiles as $file) {
                $file->move($indexerDirectory->getPathname());
            }
        }
        $nbFilesToIndex = count($indexerDirectory->listFiles());

        if ($nbFilesToIndex == 0) {
            $this->logger->info("Log consolidation: no files to index");
        }
        else {
            $this->logger->info("Log consolidation: indexed {$nbFilesToIndex} files");
            $status = $this->indexer->importData('asgard_logs');
            $status = str_replace('=', ': ', http_build_query($status, null, ', '));
            $this->logger->info("Log consolidation: indexed {$status}");

            if (!$saveDirectory->isDir()) {
                mkdir($saveDirectory->getPathname());
            }
            foreach ($indexerDirectory->listFiles() as $file) {
                $file->move($saveDirectory->getPathname());
            }
        }

        if ($tmpDirectory->isDir()) {
            foreach ($tmpDirectory->listFiles() as $file) {
                $file->move($indexerDirectory->getPathname());
            }
            $tmpDirectory->unlink();
        }

        $time = number_format((microtime(true) - $this->times[0]), 2);
        $this->logger->info("Log consolidation: Duration: {$time}s");
    }

} 