<?php

namespace LogTrackerBundle\Entity;

/**
 * LogFile
 */
class LogFile
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
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $mask;

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
     * @return LogFile
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
     * Set path
     *
     * @param string $path
     *
     * @return LogFile
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set mask
     *
     * @param string $mask
     *
     * @return LogFile
     */
    public function setMask($mask)
    {
        $this->mask = $mask;

        return $this;
    }

    /**
     * Get mask
     *
     * @return string
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * Set project
     *
     * @param \Core\ProjectBundle\Entity\Project $project
     *
     * @return LogFile
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
     * @return LogFile
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
     * @return LogFile
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
}

