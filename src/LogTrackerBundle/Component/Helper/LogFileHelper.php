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

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $linkRepository;

    public function __construct(RequestStack $request, RegistryInterface $doctrine) {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository('LogTrackerBundle:LogFile');
        $this->linkRepository = $this->doctrine->getRepository('CoreProjectBundle:Link');
    }

    /**
     * @param array|null $criteria
     * @return \LogTrackerBundle\Entity\LogFile[]
     */
    public function listLogs(array $criteria = null) {
        $logs = array();
        $criteria = array_filter($criteria);
        $links = $this->linkRepository->findBy($criteria);
        foreach ($links as $link) {
            foreach ($this->repository->findBy(array('link' => $link)) as $log) {
                if (!in_array($log, $logs)) {
                    $logs[] = $log;
                }
            }
        }

        return $logs;
    }

    /**
     * @param $id
     * @return \LogTrackerBundle\Entity\LogFile
     */
    public function getLogById($id) {
        return $this->repository->findOneBy(array('id' => $id));
    }

}