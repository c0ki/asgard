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

    public function __construct(RequestStack $request, RegistryInterface $doctrine) {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository('CoreProjectBundle:Daemon');
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