<?php

namespace Core\ProjectBundle\Component\Helper;


use Symfony\Bridge\Doctrine\RegistryInterface;

class ProjectHelper
{

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $container = null;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $projectRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $environmentRepository;

    public function __construct(RegistryInterface $doctrine)
    {
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
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function hasProjectByName($name)
    {
        return ($this->getProjectByName($name) ? true : false);
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
     * @return \Core\ProjectBundle\Entity\Environment
     */
    public function hasEnvironmentByName($name)
    {
        return ($this->getEnvironmentByName($name) ? true : false);
    }



}