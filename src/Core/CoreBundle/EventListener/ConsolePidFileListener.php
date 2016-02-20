<?php

namespace Core\CoreBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\ArgvInput;
use Monolog\Logger;

class ConsolePidFileListener
{
    /**
     * Logger object
     * @var \Monolog\Logger
     */
    protected $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $inputDefinition = $event->getCommand()->getApplication()->getDefinition();

        // add the option to the application's input definition
        $inputDefinition->addOption(
            new InputOption('pidfile', null, InputOption::VALUE_OPTIONAL, 'The location of the PID file that should be created for this process', null)
        );

        // merge the application's input definition
        $event->getCommand()->mergeApplicationDefinition();

        $input = new ArgvInput();

        // we use the input definition of the command
        $input->bind($event->getCommand()->getDefinition());

        $pidFile = $input->getOption('pidfile');

        if ($pidFile !== null) {
            if (file_exists($pidFile)) {
                $pidLocked = file_get_contents($pidFile);
                $checkPid = trim(`ps -p {$pidLocked} -o pid=`);
                if (!empty($checkPid)) {
                    $this->logger->warning("[STOP] Command {$event->getCommand()->getName()} already running in another process");
                    exit();
                }
                unlink($pidFile);
            }
            file_put_contents($pidFile, getmypid());
        }
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $pidFile = $event->getInput()->getOption('pidfile');

        if ($pidFile !== null && file_exists($pidFile) && file_get_contents($pidFile) == getmypid()) {
            unlink($pidFile);
        }
    }
}

