<?php

namespace ViewerBundle\Component\Helper;


use Core\ProjectBundle\Component\Helper\ProjectHelper;
use Core\ProjectBundle\Entity\Domain;
use Core\ProjectBundle\Entity\Project;
use Symfony\Bridge\Doctrine\RegistryInterface;

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

    /**
     * @var ProjectHelper
     */
    private $projectHelper;

    public function __construct(RegistryInterface $doctrine, ProjectHelper $projectHelper)
    {
        $this->doctrine = $doctrine;
        $this->serverUrlRepository = $this->doctrine->getRepository('ViewerBundle:ServerUrl');
        $this->projectHelper = $projectHelper;
    }

    /**
     * @return \ViewerBundle\Entity\ServerUrl[]
     */
    public function getServerUrls()
    {
        $criteria = array();
        $criteria['project'] = $this->projectHelper->getProject();
        $criteria['domain'] = $this->projectHelper->getDomain();
        $criteria = array_filter($criteria);

        $serverUrls = $this->getServerUrlsByCriteria($criteria);
        if (array_key_exists('project', $criteria)) {
            $serverUrls = $serverUrls[$criteria['project']];
            if (array_key_exists('domain', $criteria)) {
                $serverUrls = $serverUrls[$criteria['domain']];
            }
        }
        elseif (array_key_exists('domain', $criteria)) {
            foreach ($serverUrls as &$projectServerUrls) {
                if (array_key_exists($criteria['domain'], $projectServerUrls)) {
                    $projectServerUrls = $projectServerUrls[$criteria['domain']];
                }
                else {
                    $projectServerUrls = null;
                }
            }
        }
        $serverUrls = array_filter($serverUrls);

        return $serverUrls;
    }

    /**
     * @param Project     $project
     * @param Domain|null $domain
     * @return \ViewerBundle\Entity\ServerUrl[]
     */
    public function getServerUrlsByProject(Project $project, Domain $domain = null)
    {
        $criteria = array();
        $criteria['project'] = $project;
        $criteria['domain'] = $domain;
        $criteria = array_filter($criteria);

        return $this->serverUrlRepository->findBy($criteria);
    }

    /**
     * @param Array $criteria
     * @return \ViewerBundle\Entity\ServerUrl[]
     */
    public function getServerUrlsByCriteria(Array $criteria)
    {
        $serverUrls = array();
        $criteria = array_filter($criteria);
        $allServerUrls = $this->serverUrlRepository->findBy($criteria);
        foreach ($allServerUrls as $serverUrl) {
            $serverUrls[(string)$serverUrl->getProject()][(string)$serverUrl->getDomain()][$serverUrl->getId()] = $serverUrl;
        }

        return $serverUrls;
    }


    /**
     * @param Integer $id
     * @return \ViewerBundle\Entity\ServerUrl
     */
    public function getServerUrl($id)
    {
        return $this->serverUrlRepository->find($id);
    }

}