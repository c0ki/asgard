<?php

namespace LogTrackerBundle\Component\Helper;


use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LogFileHelper
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
        $this->repository = $this->doctrine->getRepository('LogTrackerBundle:LogFile');
    }

    /**
     * @return \LogTrackerBundle\Entity\LogFile[]
     */
    public function listLogs($criteria) {
        return $this->repository->findBy($criteria);
    }

}