<?php

namespace Core\ProjectBundle\Component\Helper;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectHelper
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container = null;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $repository;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->repository = $this->container->get('doctrine')->getRepository('CoreProjectBundle:Project');
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->repository->findAll();
    }

    /**
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function getByName($name)
    {
        return $this->repository->findOneBy(array('name' => $name));
    }

    /**
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function hasByName($name)
    {
        return ($this->getByName($name) ? true : false);
    }

}