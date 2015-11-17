<?php

namespace ViewerBundle\Entity;

/**
 * ServerUrl
 */
class ServerUrl
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $label;

    /**
     * @var \Core\ProjectBundle\Entity\Project
     */
    private $project;

    /**
     * @var \Core\ProjectBundle\Entity\Domain
     */
    private $domain;


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
     * Set url
     *
     * @param string $url
     *
     * @return ServerUrl
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return ServerUrl
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set project
     *
     * @param \Core\ProjectBundle\Entity\Project $project
     *
     * @return ServerUrl
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
     * @return ServerUrl
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
}

