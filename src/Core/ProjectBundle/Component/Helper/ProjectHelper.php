<?php

namespace Core\ProjectBundle\Component\Helper;


use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectHelper
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
    protected $projectRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $domainRepository;

    public function __construct(RequestStack $request, RegistryInterface $doctrine)
    {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->projectRepository = $this->doctrine->getRepository('CoreProjectBundle:Project');
        $this->domainRepository = $this->doctrine->getRepository('CoreProjectBundle:Domain');
    }

    /**
     * @return \Core\ProjectBundle\Entity\Project[]
     */
    public function listProjects()
    {
        return $this->projectRepository->findAll();
    }

    /**
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function getProjectByName($name)
    {
        return $this->projectRepository->findOneBy(array('name' => $name));
    }

    /**
     * @return boolean
     */
    public function hasProjectByName($name)
    {
        return ($this->getProjectByName($name) ? true : false);
    }

    /**
     * @return null|\Core\ProjectBundle\Entity\Project
     */
    public function getProject()
    {
        if ($this->masterRequest->attributes->has('@project')) {
            return $this->getProjectByName($this->masterRequest->attributes->get('@project'));
        }
        return null;
    }

    /**
     * @return boolean
     */
    public function hasProject()
    {
        return $this->masterRequest->attributes->has('@project');
    }


    /**
     * @return \Core\ProjectBundle\Entity\Domain[]
     */
    public function listDomains()
    {
        return $this->domainRepository->findAll();
    }


    /**
     * @return \Core\ProjectBundle\Entity\Domain
     */
    public function getDomainByName($name)
    {
        return $this->domainRepository->findOneBy(array('name' => $name));
    }

    /**
     * @return boolean
     */
    public function hasDomainByName($name)
    {
        return ($this->getDomainByName($name) ? true : false);
    }

    /**
     * @return null|\Core\ProjectBundle\Entity\Domain
     */
    public function getDomain()
    {
        if ($this->masterRequest->attributes->has('@domain')) {
            return $this->getDomainByName($this->masterRequest->attributes->get('@domain'));
        }
        return null;
    }

    /**
     * @return boolean
     */
    public function hasDomain()
    {
        return $this->masterRequest->attributes->has('@domain');
    }



}