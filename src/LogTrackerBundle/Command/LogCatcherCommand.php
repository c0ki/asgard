<?php

namespace LogTrackerBundle\Command;

use Core\ProjectBundle\Component\Helper\ProjectHelper;
use Core\ProjectBundle\Component\Helper\DaemonHelper;
use Core\ProjectBundle\Entity\Domain;
use Core\ProjectBundle\Entity\Project;
use Core\ProjectBundle\Entity\Daemon;
use LogTrackerBundle\Component\Helper\LogFileHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Core\CoreBundle\Component\File\File;

class LogCatcherCommand extends ContainerAwareCommand
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

    /**
     * @var int
     */
    protected $nbLogCatched = 0;

    protected function configure() {
        $this
            ->setName("logtracker:logcatcher")
            ->setDescription("Log catcher")
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
        $this->validate($input, $output);
    }

    protected function validate(InputInterface $input, OutputInterface $output) {
        if ($input->hasOption('project') && $input->getOption('project')) {
            $this->project = $this->projectHelper->getProjectByName($input->getOption('project'));
            if (is_null($this->project)) {
                $this->logger->error("Log catcher: The \"--project\" option requires a valid project");
                throw new \InvalidArgumentException('The "--project" option requires a valid project');
            }
        }

        if ($input->hasOption('domain') && $input->getOption('domain')) {
            $this->domain = $this->projectHelper->getDomainByName($input->getOption('domain'));
            if (is_null($this->domain)) {
                $this->logger->error("Log catcher: The \"--domain\" option requires a valid domain");
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain.');
            }
            elseif (!is_null($this->project) && !$this->project->getDomains()->contains(($this->domain))) {
                $this->logger->error("Log catcher: The \"--domain\" option requires a valid domain to \"{$this->project->getLabel()}\" project");
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain to "'
                                                    . $this->project->getLabel() . '" project.');
            }
        }

        if ($input->hasOption('daemon') && $input->getOption('daemon')) {
            $this->daemon = $this->daemonHelper->getDaemonByName($input->getOption('daemon'));
            if (is_null($this->daemon)) {
                $this->logger->error("Log catcher: The \"--daemon\" option requires a valid application");
                throw new \InvalidArgumentException('The "--daemon" option requires a valid application.');
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (is_null($this->project)) {
            $this->logger->debug("Log catcher to all projects");
            $projects = $this->projectHelper->listProjects();
            if (empty($projects)) {
                $this->logger->debug("Log catcher: No project");

                return;
            }
            foreach ($projects as $project) {
                $this->project = $project;
                $this->logCatcherProject();
            }
        }
        else {
            $this->logCatcherProject();
        }

        if ($this->nbLogCatched > 0) {
            $this->logger->info("Log catcher: Launch log consolidation to {$this->nbLogCatched} logfiles");
            $command = $this->getApplication()->find('logtracker:logconsolidation');
            $arguments = array(
                'command' => 'logtracker:logconsolidation',
            );
            $command->run(new ArrayInput($arguments), $output);
        }

        $time = number_format((microtime(true) - $this->times[0]), 2);
        $this->logger->info("Log catcher: Duration: {$time}s");
    }

    protected function logCatcherProject() {
        $this->logger->debug("Log catcher: Project '{$this->project->getLabel()}'");
        if (is_null($this->domain)) {
            $domains = $this->project->getDomains();
            if (empty($domains)) {
                $this->logger->debug("Log catcher: No domain");

                return;
            }
            foreach ($domains as $domain) {
                $this->domain = $domain;
                $this->logCatcherDomain();
            }
        }
        else {
            $this->logCatcherDomain();
        }
    }

    protected function logCatcherDomain() {
        $this->logger->debug("Log catcher: Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}'");
        if (is_null($this->daemon)) {
            $daemons =
                $this->daemonHelper->findDaemonsLinked(array('project' => $this->project, 'domain' => $this->domain));
            if (empty($daemons)) {
                $this->logger->debug("Log catcher: No daemon");

                return;
            }
            foreach ($daemons as $daemon) {
                $this->daemon = $daemon;
                $this->logCatcherDaemon();
            }
        }
        else {
            $this->logCatcherDaemon();
        }
    }

    protected function logCatcherDaemon() {
        $this->logger->debug("Log catcher: Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}': Daemon '{$this->daemon->getLabel()}'");

        // Get indexer.logfiles.directories
        $indexerDir = $this->getContainer()->getParameter('indexer.logfiles.directories');

        $logFiles = $this->logFileHelper->listLogs(array('project' => $this->project, 'domain' => $this->domain,
                                                         'daemon'  => $this->daemon));
        if (empty($logFiles)) {
            $this->logger->debug("Log catcher: No logfile");

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
                elseif (preg_match('/access/', $file->getFilename()) || preg_match('/varnish/', $file->getFilename())) {
                    $type = 'access';
                }
                $newFilename =
                    "{$this->project->getName()}.{$this->domain->getName()}.{$logfile->getLink()->getServer()}.{$this->daemon->getName()}.{$type}-{$file->getFilename()}";
                try {
                    $newFile = $file->copy($indexerDir, $newFilename);
                    $this->logger->debug("Log catcher: copy file '{$file->getPathname()}' to '{$newFile->getPathname()}'");
                    if ($newFile->getExtension() == 'gz') {
                        $uncompressFile = $newFile->uncompress();
                        $this->logger->debug("Log catcher: uncompress file '{$newFile->getPathname()}' to '{$uncompressFile->getPathname()}'");
                    }
                    $this->nbLogCatched++;
                }
                catch (\Exception $e) {
                    $message = $e->getMessage() . ($e->getPrevious() ? $e->getPrevious()->getMessage() : '');
                    $this->logger->warning("Log catcher: no copy file '{$file->getPathname()}' to '{$newFile->getPathname()}': {$message}");
                }
            }
        }
    }

} 