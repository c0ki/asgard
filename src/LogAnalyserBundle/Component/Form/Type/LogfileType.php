<?php

namespace LogAnalyserBundle\Component\Form\Type;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use LogAnalyserBundle\Component\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class LogfileType extends AbstractType
{

    const CACHE_PATH = 'loganalyser/logfiles/';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('server',
            'choice',
            array(
                'label' => "Serveur",
                'empty_value' => 'Choisissez le serveur',
                'choices' => array_combine(array_keys($this->container->getParameter('logs_servers')), array_keys($this->container->getParameter('logs_servers'))),
            ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->get('server')->addEventListener(FormEvents::POST_SUBMIT, array($this, 'onServerPostSubmit'));

    }

    public function formUpdate(FormInterface $form, $server)
    {
        $values = $this->listLogFiles($server);

        $form->add('log_file', 'choice', array(
            'label' => "Fichier de log",
            'empty_value' => 'Choisissez le fichier de log',
            'choices' => $values,
            'disabled' => empty($values) ? true : false,
        ));
    }

    public function listLogFiles($serverId, $logDir = null)
    {
        $servers = $this->container->getParameter('logs_servers');
        if (!array_key_exists($serverId, $servers)) {
            return array();
        }

        $logFiles = array();
        try {
            $logDirectory = new File($servers[$serverId]);
            if ($logDir) {
                $logDirectory = new File($servers[$serverId] . $logDir . DIRECTORY_SEPARATOR);
            }
            $logFiles = $logDirectory->listFiles();
        }
        catch (Exception $e) {
        }

        $listLogFiles = array();
        foreach ($logFiles as $logFile) {
            if ($logFile->isDir()) {
                $subdir = $logFile->getBasename() . DIRECTORY_SEPARATOR;
                $listSubLogFiles = $this->listLogFiles($serverId, $subdir);
                $listSubLogFiles = array_combine(
                    array_map(function($k) use ($subdir) { return $subdir.$k; }, array_keys($listSubLogFiles)), $listSubLogFiles);
                $listSubLogFiles = array_map(function($k) use ($subdir) { return $subdir.$k; }, $listSubLogFiles);
                $listLogFiles = array_merge($listLogFiles, $listSubLogFiles);
            }
            try {
                $listLogFiles[$logFile->getBasename()] = "{$logFile->getBasename()} [{$logFile->getSize(true)}]";
            }
            catch (\Exception $e) {
            }
        }

        return $listLogFiles;
    }

    public function saveLogfile($serverId, $logfile)
    {
        $identifier = uniqid();
        $servers = $this->container->getParameter('logs_servers');
        if (!array_key_exists($serverId, $servers)) {
            return array();
        }
        $logfile = new File($servers[$serverId] . DIRECTORY_SEPARATOR . $logfile);

        $cacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_PATH;

        $logfile->copy($cacheDir, $identifier);

        return $identifier;
    }

    public function getLogfile($identifier)
    {
        $cacheDir = $this->container->getParameter('kernel.cache_dir') . DIRECTORY_SEPARATOR . self::CACHE_PATH;
        $logfile = new File($cacheDir . DIRECTORY_SEPARATOR . $identifier);
        if (!$logfile->isFile()) {
            throw new FileNotFoundException($logfile->getPathname());
        }
        return $logfile;
    }

    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
//        var_dump(2, $data);
        $this->formUpdate($event->getForm(), $data);
    }

    public function onServerPostSubmit(FormEvent $event)
    {
        // Il est important de récupérer ici $event->getForm()->getData(),
        // car $event->getData() vous renverra la données initiale (vide)
        $server = $event->getForm()->getData();

        // puisque nous avons ajouté l'écouteur à l'enfant, il faudra passer
        // le parent aux fonctions de callback!
        $this->formUpdate($event->getForm()->getParent(), $server);
    }

    public function getName()
    {
        return 'logfile';
    }
}