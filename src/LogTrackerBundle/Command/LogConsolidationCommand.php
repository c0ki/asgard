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

    protected function configure() {
        $this
            ->setName("logtracker:logconsolidation")
            ->setDescription("Log consolidation")
            ->addOption('project', 'p', InputOption::VALUE_REQUIRED, 'Project')
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Domain')
            ->addOption('daemon', null, InputOption::VALUE_REQUIRED, 'Daemon');
    }

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $this->logger = $this->getContainer()->get('monolog.logger.console');
        $this->projectHelper = $this->getContainer()->get('project_helper');
        $this->daemonHelper = $this->getContainer()->get('daemon_helper');
        $this->logFileHelper = $this->getContainer()->get('logfile_helper');
    }

    protected function interact(InputInterface $input, OutputInterface $output) {
        if ($input->hasOption('project')) {
            $this->project = $this->projectHelper->getProjectByName($input->getOption('project'));
            if (is_null($this->project)) {
                throw new \InvalidArgumentException('The "--project" option requires a valid project');
            }
        }

        if ($input->hasOption('domain')) {
            $this->domain = $this->projectHelper->getDomainByName($input->getOption('domain'));
            if (is_null($this->domain)) {
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain.');
            }
            elseif (!is_null($this->project) && !$this->project->getDomains()->contains(($this->domain))) {
                throw new \InvalidArgumentException('The "--domain" option requires a valid domain to "' . $this->project->getLabel() . '" project.');
            }
        }

        if ($input->hasOption('app')) {
//            $this->domain = $this->projectHelper->getDomainByName($input->getOption('app'));
//            if (is_null($this->domain)) {
//                throw new \InvalidArgumentException('The "--app" option requires a valid application.');
//            }
//            elseif (!$this->project->getDomains()->contains(($this->domain))) {
//                throw new \InvalidArgumentException('The "--app" option requires a valid application to "' . $this->project->getLabel() . '" project on "' . $this->domain->getLabel() . '" domain.');
//            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (is_null($this->project)) {
            $this->logger->info("Log consolidation to all projects");
            foreach ($this->projectHelper->listProjects() as $project) {
                $this->project = $project;
                $this->logConsolidationProject();
            }
        }
        else {
            $this->logConsolidationProject();
        }





exit();


//        /** @var \Ind\CoreBundle\Component\Core\SignalSlot\Repository $repository */
//        $repository = $this->getContainer()->get('ezpublish.api.repository');
//        /** @var \Ind\CoreBundle\Component\Core\SignalSlot\SearchDaemon $searchDaemon */
//        $searchDaemon = $repository->getSearchService();
//        /** @var \Ind\CoreBundle\Component\Core\SignalSlot\LocationService $locationService */
//        $locationService = $repository->getLocationService();

        // Get locations to purge (parameter or into file)
        $locationsIds = $input->getOption('locationsIds');
        if (!empty($locationsIds)) {
            $locationsIds = explode(',', $locationsIds);
            $locationsIds = array_map('trim', $locationsIds);
            $locationsIds = array_filter($locationsIds);
            $locationsIds = array_filter($locationsIds, 'is_numeric');
            if (!empty($locationsIds)) {
                $labelLocationsIds = implode(', ', $locationsIds);
                $this->logger->info("Cache initialization: LocationsIds: {$labelLocationsIds}");
            }
        }

        if (empty($locationsIds)) {
            $locationsIds = array();
            $contents = $searchService->findContentByParams(array('ContentTypeIdentifier' => 'marche'));
            $nbLocations = 0;
            foreach ($contents as $content) {
                $locations = $locationService->loadLocations($content->contentInfo);
                foreach ($locations as $location) {
                    $nbLocations++;
                    $locationsIds[] = $location->id;
                }
            }
            $this->logger->debug("Cache initialization: Marche: {$nbLocations} locations");
            $contents = $searchService->findContentByParams(array('ContentTypeIdentifier' => 'univers'));
            $nbLocations = 0;
            foreach ($contents as $content) {
                $locations = $locationService->loadLocations($content->contentInfo);
                foreach ($locations as $location) {
                    $nbLocations++;
                    $locationsIds[] = $location->id;
                }
            }
            $this->logger->debug("Cache initialization: Univers: {$nbLocations} locations");
        }

        // Get number process to use
        $nbProcess = $input->getOption('process');
        if (empty($nbProcess) || !is_numeric($nbProcess) || $nbProcess < 1) {
            $nbProcess = 1;
        }

        $startTime = microtime(true);

        $counter = 0;
        $countTotal = count($locationsIds);

        foreach ($locationsIds as $locationId) {
            $counter++;
            $this->doCall($locationId, $nbProcess, $counter, $countTotal);
        }

        $this->doWait();

        $time = number_format((microtime(true) - $startTime), 2);
        $this->logger->debug("Cache initialization: Duration: {$time}s");
    }

    private $listProcessId = array();
    private $uid = null;

    protected function doWait() {
        foreach ($this->listProcessId as $pid) {
            pcntl_waitpid($pid, $status);
            $this->logger->debug("Process {$pid} ended");
        }
    }

    protected function doCall($locationId, $maxProcess, $counter, $countTotal) {
        if (count($this->listProcessId) >= $maxProcess) {
            $pid = pcntl_wait($status);
            unset($this->listProcessId[$pid]);
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->logger->error('Cache initialization: error during duplication');
            exit();
        }
        elseif ($pid) {
            $this->listProcessId[$pid] = $pid;
            return;
        }
        else {
            // Child process
            $this->uid = getmypid();
            $this->call($locationId, $counter, $countTotal);
            exit();
        }
    }

    protected function call($locationId, $counter, $countTotal) {
        /** @var \Symfony\Cmf\Component\Routing\ChainRouter $router */
        $router = $this->getContainer()->get('router');
        /** @var \Buzz\Browser $httpBrowser */
        $httpBrowser = $this->getContainer()->get('ezpublish.http_cache.purge_client.browser');
        $httpBrowser->getClient()->setTimeout(2);

        /** @var \eZ\Publish\Core\Persistence\Doctrine\ConnectionHandler $dbHandler */
        $dbHandler = $this->getContainer()->get('ezpublish.api.storage_engine.legacy.dbhandler');
        $dbHandler->getConnection()->close();
        $dbHandler->getConnection()->connect();

        try {
            $url = $router->generate('ez_urlalias',
                                     array('locationId' => $locationId, 'dynamicSiteaccess' => true));
        }
        catch (\Exception $e) {
            $url = null;
            $message = $e->getMessage() . ($e->getPrevious() ? ": " . $e->getPrevious()->getMessage() : '');
            $this->logger->warning("Cache initialization [uid:{$this->uid}]: L/{$locationId}: generate url: {$message}");
        }

        // Clear http cache
        $purgeServers = $this->getContainer()->getParameter('server.purge.list');
        foreach ($purgeServers as $site) {
            if (empty($site)) continue;
            $nbSites = count($site);
            $siteCounter = 0;
            foreach ($site as $server) {
                $siteCounter++;
                $startLocalTimeLocation = microtime(true);
                try {
                    $urlComplete = $server . ':' . $this->getContainer()->getParameter('server.cache.port') . $url;
                    /** @var \Buzz\Message\Response $response */
                    $response = $httpBrowser->call($urlComplete, 'GET', array('X-Backend-Ez' => false));
                    if ($response->isSuccessful()) {
                        $this->logger->debug("Cache initialization [uid:{$this->uid}]: L/{$locationId} [{$counter}/{$countTotal}] [{$siteCounter}/{$nbSites}]: url:{$urlComplete} [{$this->getTime($startLocalTimeLocation)}s]");
                        break;
                    }
                    else {
                        $this->logger->warning("Cache initialization [uid:{$this->uid}]: L/{$locationId} [{$counter}/{$countTotal}] [{$siteCounter}/{$nbSites}]: url:{$urlComplete} [response not successful]");
                    }
                }
                catch (\Exception $e) {
                    $message = $e->getMessage() . ($e->getPrevious() ? ": " . $e->getPrevious()->getMessage() : '');
                    $this->logger->warning("Cache initialization [uid:{$this->uid}]: L/{$locationId} [{$counter}/{$countTotal}] [{$siteCounter}/{$nbSites}]: error: {$message}");
                }
            }
        }
    }

    private function getTime($time) {
        return number_format((microtime(true) - $time), 2);
    }

    protected function logConsolidationProject() {
        $this->logger->debug("Project '{$this->project->getLabel()}': Log consolidation");
        if (is_null($this->domain)) {
            foreach ($this->project->getDomains() as $domain) {
                $this->domain = $domain;
                $this->logConsolidationDomain();
            }
        }
        else {
            $this->logConsolidationDomain();
        }
    }

    protected function logConsolidationDomain() {
        $this->logger->debug("Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}': Log consolidation");
        if (is_null($this->daemon)) {
            foreach ($this->daemonHelper->listDaemons($this->project, $this->domain) as $daemon) {
                $this->daemon = $daemon;
                $this->logConsolidationDaemon();
            }
        }
        else {
            $this->logConsolidationDaemon();
        }
    }

    protected function logConsolidationDaemon() {
        $this->logger->debug("Project '{$this->project->getLabel()}': Domain '{$this->domain->getLabel()}': Daemon '{$this->daemon->getLabel()}': Log consolidation");

        // Get indexer.logfiles.directories
        $indexerDir = $this->getContainer()->getParameter('indexer.logfiles.directories');

        $logFiles = $this->logFileHelper->listLogs(array('project' => $this->project, 'domain' => $this->domain, 'daemon' => $this->daemon));

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
                    $extension = pathinfo(substr($file->getFilename(), 0, -1 * strlen($extension) - 1), PATHINFO_EXTENSION) . ".{$extension}";
                }
                $newFilename = "{$this->project->getName()}.{$this->domain->getName()}.{$this->daemon->getName()}-{$type}.{$extension}";
                $this->logger->debug("Log consolidation: copy file '{$file->getPath()}/{$file->getFilename()}' to '{$indexerDir}/{$newFilename}'");
                $file->copy($indexerDir, $newFilename);
            }
        }

        // Start indexation
        $indexer = $this->getContainer()->get('core.indexer.solr');
        $indexer->importData('asgard_logs');

        exit();
//        http://XXX:8983/solr/asgard_logs/dataimport
//command=full-import
//clean=true
//commit=true
//wt=json
//indent=true
//verbose=false
//optimize=false
//debug=false

//command=full-import
//clean=true
//commit=true
//wt=json
//indent=true
//rows=10
//verbose=false
//optimize=false
//debug=false

    }

} 