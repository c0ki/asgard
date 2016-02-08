<?php

namespace Core\ProjectBundle\Entity;

/**
 * Link
 */
class Link
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $server;

    /**
     * @var \Core\ProjectBundle\Entity\Project
     */
    private $project;

    /**
     * @var \Core\ProjectBundle\Entity\Domain
     */
    private $domain;

    /**
     * @var \Core\ProjectBundle\Entity\Daemon
     */
    private $daemon;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set server
     *
     * @param string $server
     *
     * @return Link
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get server
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Set project
     *
     * @param \Core\ProjectBundle\Entity\Project $project
     *
     * @return Link
     */
    public function setProject(\Core\ProjectBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Core\ProjectBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set domain
     *
     * @param \Core\ProjectBundle\Entity\Domain $domain
     *
     * @return Link
     */
    public function setDomain(\Core\ProjectBundle\Entity\Domain $domain = null)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return \Core\ProjectBundle\Entity\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set daemon
     *
     * @param \Core\ProjectBundle\Entity\Daemon $daemon
     *
     * @return Link
     */
    public function setDaemon(\Core\ProjectBundle\Entity\Daemon $daemon = null)
    {
        $this->daemon = $daemon;

        return $this;
    }

    /**
     * Get daemon
     *
     * @return \Core\ProjectBundle\Entity\Daemon
     */
    public function getDaemon()
    {
        return $this->daemon;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return "{$this->getProject()} / {$this->getDomain()} / {$this->getDaemon()} [{$this->getServer()}]";
    }
}
