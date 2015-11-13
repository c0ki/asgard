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
    protected $environmentRepository;

    public function __construct(RequestStack $request, RegistryInterface $doctrine)
    {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->projectRepository = $this->doctrine->getRepository('CoreProjectBundle:Project');
        $this->environmentRepository = $this->doctrine->getRepository('CoreProjectBundle:Environment');
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
     * @return \Core\ProjectBundle\Entity\Environment[]
     */
    public function listEnvironments()
    {
        return $this->environmentRepository->findAll();
    }


    /**
     * @return \Core\ProjectBundle\Entity\Environment
     */
    public function getEnvironmentByName($name)
    {
        return $this->environmentRepository->findOneBy(array('name' => $name));
    }

    /**
     * @return boolean
     */
    public function hasEnvironmentByName($name)
    {
        return ($this->getEnvironmentByName($name) ? true : false);
    }

    /**
     * @return null|\Core\ProjectBundle\Entity\Environment
     */
    public function getEnvironment()
    {
        if ($this->masterRequest->attributes->has('@environment')) {
            return $this->getEnvironmentByName($this->masterRequest->attributes->get('@environment'));
        }
        return null;
    }

    /**
     * @return boolean
     */
    public function hasEnvironment()
    {
        return $this->masterRequest->attributes->has('@environment');
    }



}