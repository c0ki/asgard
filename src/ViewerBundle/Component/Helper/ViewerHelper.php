<?php

namespace ViewerBundle\Component\Helper;


use Symfony\Bridge\Doctrine\RegistryInterface;
use Core\ProjectBundle\Entity\Project;
use Core\ProjectBundle\Entity\Domain;

class ViewerHelper
{

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $serverUrlRepository;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
        $this->serverUrlRepository = $this->doctrine->getRepository('ViewerBundle:ServerUrl');
    }

    /**
     * @param Project     $project
     * @param Domain|null $domain
     * @return \ViewerBundle\Entity\ServerUrl[]
     */
    public function getServerUrls(Project $project, Domain $domain = null)
    {
        $criteria = array();
        $criteria['project'] = $project;
        if (!empty($domain)) $criteria['domain'] = $domain;

        return $this->serverUrlRepository->findBy($criteria);
    }

}