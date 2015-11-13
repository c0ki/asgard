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
        if (empty($this->projects)) {
            $this->projects = $this->projectRepository->findAll();
        }

        return $this->projects;
    }

    /**
     * @var \Core\ProjectBundle\Entity\Project[]
     */
    private $projects = array();

    /**
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function getProjectByName($name)
    {
        if (!array_key_exists($name, $this->projectsByName)) {
            $this->projectsByName[$name] = $this->projectRepository->findOneBy(array('name' => $name));
        }

        return $this->projectsByName[$name];
    }

    /**
     * @var \Core\ProjectBundle\Entity\Project[]
     */
    private $projectsByName = array();

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
        if (empty($this->domains)) {
            $this->domains = $this->domainRepository->findAll();
        }

        return $this->domains;
    }

    /**
     * @var \Core\ProjectBundle\Entity\Domain[]
     */
    private $domains = array();


    /**
     * @return \Core\ProjectBundle\Entity\Domain
     */
    public function getDomainByName($name)
    {
        if (!array_key_exists($name, $this->domainsByName)) {
            $this->domainsByName[$name] = $this->domainRepository->findOneBy(array('name' => $name));
        }

        return $this->domainsByName[$name];
    }

    /**
     * @var \Core\ProjectBundle\Entity\Domain[]
     */
    private $domainsByName = array();

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