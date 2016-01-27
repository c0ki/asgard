<?php

namespace Core\ProjectBundle\Component\Helper;


use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class DaemonHelper
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $masterRequest;

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $linkRepository;

    public function __construct(RequestStack $request, RegistryInterface $doctrine) {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository('CoreProjectBundle:Daemon');
        $this->linkRepository = $this->doctrine->getRepository('CoreProjectBundle:Link');
    }

    /**
     * @return \Core\ProjectBundle\Entity\Daemon[]
     */
    public function listDaemons() {
        if (empty($this->daemons)) {
            $this->daemons = $this->repository->findAll();
        }

        return $this->daemons;
    }

    /**
     * @var \Core\ProjectBundle\Entity\Daemon[]
     */
    private $daemons = array();

    /**
     * @param array $criteria
     * @return \Core\ProjectBundle\Entity\Daemon[]
     */
    public function findDaemonsLinked(array $criteria) {
        $daemons = array();
        $links = $this->linkRepository->findBy($criteria);
        foreach ($links as $link) {
            if (!in_array($link->getDaemon(), $daemons)) {
                $daemons[] = $link->getDaemon();
            }
        }

        return $daemons;
    }

    /**
     * @return \Core\ProjectBundle\Entity\Daemon
     */
    public function getDaemonByName($name) {
        if (!array_key_exists($name, $this->daemonsByName)) {
            $this->daemonsByName[$name] = $this->repository->findOneBy(array('name' => $name));
        }

        return $this->daemonsByName[$name];
    }

    /**
     * @var \Core\ProjectBundle\Entity\Daemon[]
     */
    private $daemonsByName = array();

    /**
     * @return boolean
     */
    public function hasDaemonByName($name) {
        return ($this->getDaemonByName($name) ? true : false);
    }


}