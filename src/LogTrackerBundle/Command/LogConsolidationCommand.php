<?php

namespace LogTrackerBundle\Command;

use Core\ProjectBundle\Component\Helper\ProjectHelper;
use Core\ProjectBundle\Component\Helper\DaemonHelper;
use Core\ProjectBundle\Entity\Domain;
use Core\ProjectBundle\Entity\Project;
use Core\ProjectBundle\Entity\Daemon;
use LogTrackerBundle\Component\Helper\LogFileHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Core\CoreBundle\Component\File\File;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class LogConsolidationCommand extends ContainerAwareCommand
{
    /**
     * Logger
     * @var \Monolog\Logger
     */
    protected $logger;

    /**
     * @var ProjectHelper
     */
    protected $projectHelper;

    /**
     * @var DaemonHelper
     */
    protected $daemonHelper;

    /**
     * @var LogFileHelper
     */
    protected $logFileHelper;

    /**
     * @var Project
     */
    protected $project = null;

    /**
     * @var Domain
     */
    protected $domain = null;

    /**
     * @var Daemon
     */
    protected $daemon = null;

    /**
     * @var array
     */
    protected $times = array();

    protected function configure() {
        $this
            ->setName("logtracker:logconsolidation")
            ->setDescription("Log consolidation")
            ->addOption('project', 'p', InputOption::VALUE_REQUIRED, 'Project')
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Domain')
            ->addOption('daemon', null, InputOption::VALUE_REQUIRED, 'Daemon');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->times = array(microtime(true));
        $this->logger = $this->getContainer()->get('monolog.logger.console');
        $this->projectHelper = $this->getContainer()->get('project_helper');
        $this->daemonHelper = $this->getContainer()->get('daemon_helper');
        $this->logFileHelper = $this->getContainer()->get('logfile_helper');
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        if ($input->hasOption('project') && $input->getOption('project')) {
            $this->project = $this->projectHelper->getProjectByName($input->getOption('project'));
            if (is_null($this->project)) {
                $this->logger->error("Log consolidation: The \"--project\" option requires a valid project");
                throw new \InvalidArgumentException('The "--project" option requires a valid project');
            }
        }

        if ($input->hasOption('domain') && $input->getOption('domain')) {
            $this->domain = $this->projectHelper->getDomainByName($input->getOption('domain'));
            if (is_null($this->domain)) {
                $this->logger->error("Log consolidation: The \"--domain\" option requires a valid domain");
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain.');
            }
            elseif (!is_null($this->project) && !$this->project->getDomains()->contains(($this->domain))) {
                $this->logger->error("Log consolidation: The \"--domain\" option requires a valid domain to \"{$this->project->getLabel()}\" project");
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain to "'
                                                    . $this->project->getLabel() . '" project.');
            }
        }

        if ($input->hasOption('daemon') && $input->getOption('daemon')) {
            $this->daemon = $this->daemonHelper->getDaemonByName($input->getOption('daemon'));
            if (is_null($this->daemon)) {
                $this->logger->error("Log consolidation: The \"--daemon\" option requires a valid application");
                throw new \InvalidArgumentException('The "--daemon" option requires a valid application.');
            }
            elseif (!is_null($this->project) && !$this->daemon->getProjects()->contains(($this->project))) {
                $this->logger->error("Log consolidation: The \"--daemon\" option requires a valid daemon to \"{$this->project->getLabel()}\" project");
                throw new \InvalidArgumentException('The "--daemon" option requires a valid daemon to "'
                                                    . $this->project->getLabel() . '" project.');
            }
            elseif (!is_null($this->domain) && !$this->daemon->getDomains()->contains(($this->domain))) {
                $this->logger->error("Log consolidation: The \"--daemon\" option requires a valid daemon to \"{$this->domain->getLabel()}\" domain");
                throw new \InvalidArgumentException('The "--daemon" option requires a valid daemon to "'
                                                    . $this->domain->getLabel() . '" domain.');
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (is_null($this->project)) {
            $this->logger->debug("Log consolidation to all projects");
            $projects = $this->projectHelper->listProjects();
            if (empty($projects)) {
                $this->logger->debug("Log consolidation: No project");

                return;
            }
            foreach ($projects as $project) {
                $this->project = $project;
                $this->logConsolidationProject();
            }
        }
        else {
            $this->logConsolidationProject();
        }

        $time = number_format((microtime(true) - $this->times[0]), 2);
        $this->logger->info("Log consolidation: Duration: {$time}s");
    }

    protected function logConsolidationProject() {
        $this->logger->debug("Log consolidation: Project '{$this->project->getLabel()}'");
        if (is_null($this->domain)) {
            $domains = $this->project->getDomains();
            if (empty($domains)) {
                $this->logger->debug("Log consolidation: No domain");

                return;
            }
            foreach ($domains as $domain) {
                $this->domain = $domain;
                $this->logConsolidationDomain();
            }
        }
        else {
            $this->logConsolidationDomain();
        }
    }

    protected function logConsolidationDomain() {
        $this->logger->debug("Log consolidation: Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}'");
        if (is_null($this->daemon)) {
            $daemons =
                $this->daemonHelper->findDaemonsLinked(array('project' => $this->project, 'domain' => $this->domain));
            if (empty($daemons)) {
                $this->logger->debug("Log consolidation: No daemon");

                return;
            }
            foreach ($daemons as $daemon) {
                $this->daemon = $daemon;
                $this->logConsolidationDaemon();
            }
        }
        else {
            $this->logConsolidationDaemon();
        }
    }

    protected function logConsolidationDaemon() {
        $this->logger->debug("Log consolidation: Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}': Daemon '{$this->daemon->getLabel()}'");

        // Get indexer.logfiles.directories
        $indexerDir = $this->getContainer()->getParameter('indexer.logfiles.directories');

        $logFiles = $this->logFileHelper->listLogs(array('project' => $this->project, 'domain' => $this->domain,
                                                         'daemon'  => $this->daemon));
        if (empty($logFiles)) {
            $this->logger->debug("Log consolidation: No logfile");

            return;
        }

        foreach ($logFiles as $logfile) {
            $file = new File($logfile->getPath(), false);
            if ($file->isDir()) {
                $files = $file->listFiles($logfile->getMask());
            }
            else {
                $files = array($file);
            }

            foreach ($files as $file) {
                $type = 'unknown';
                if (preg_match('/error/', $file->getFilename())) {
                    $type = 'error';
                }
                elseif (preg_match('/access/', $file->getFilename())) {
                    $type = 'access';
                }
                $extension = $file->getExtension();
                if (is_numeric($extension)) {
                    $extension =
                        pathinfo(substr($file->getFilename(), 0, -1 * strlen($extension) - 1), PATHINFO_EXTENSION)
                        . ".{$extension}";
                }
                $newFilename =
                    "{$this->project->getName()}.{$this->domain->getName()}.{$this->daemon->getName()}-{$type}.{$extension}";
                $this->logger->debug("Log consolidation: copy file '{$file->getPath()}/{$file->getFilename()}' to '{$indexerDir}/{$newFilename}'");
                $file->copy($indexerDir, $newFilename);
            }
        }

        // Start indexation
        $indexer = $this->getContainer()->get('core.indexer.solr');
        $status = $indexer->importData('asgard_logs');
        $status = str_replace('=', ': ', http_build_query($status, null, ', '));
        $this->logger->debug("Log consolidation: indexed {$status}");
    }

} 