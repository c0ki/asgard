<?php

namespace Core\ProjectBundle\Component\Helper;


use Core\ProjectBundle\Entity\Link;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LinkHelper
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

    public function __construct(RequestStack $request, RegistryInterface $doctrine)
    {
        $this->masterRequest = $request->getMasterRequest();
        $this->doctrine = $doctrine;
        $this->repository = $this->doctrine->getRepository('CoreProjectBundle:Link');
    }

    /**
     * @return \Core\ProjectBundle\Entity\Link[]
     */
    public function listLinks()
    {
        if (empty($this->links)) {
            $this->links = $this->repository->findAll();
        }

        return $this->links;
    }

    /**
     * @var \Core\ProjectBundle\Entity\Link[]
     */
    private $links = array();

    /**
     * @param array $criteria
     * @param bool  $sort
     * @return \Core\ProjectBundle\Entity\Link[]
     */
    public function findLinks(array $criteria, $sort)
    {
        $links = $this->repository->findBy($criteria);
        if ($sort) {
            $links = $this->sortLinks($links);
        }

        return $links;
    }

    /**
     * @param $id
     * @return \Core\ProjectBundle\Entity\Link
     */
    public function getLinkById($id)
    {
        if (!array_key_exists($id, $this->linkById)) {
            $this->linkById[$id] = $this->repository->findOneBy(array('id' => $id));
        }

        return $this->linkById[$id];
    }

    /**
     * @var \Core\ProjectBundle\Entity\Link[]
     */
    private $linkById = array();

    /**
     * @param Link[] $links
     * @return array
     */
    protected function sortLinks(array $links)
    {
        $linksSorted = [];

        foreach ($links as $link) {
            $linksSorted[$link->getProject()->getName()][$link->getDomain()->getName()][] = $link;
        }

        return $linksSorted;
    }
}