<?php

namespace Core\ProjectBundle\Entity;

/**
 * Project
 */
class Project
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $environments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->environments = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return Project
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
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set logo
     *
     * @param string $logo
     *
     * @return Project
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Add environment
     *
     * @param \Core\ProjectBundle\Entity\Environment $environment
     *
     * @return Project
     */
    public function addEnvironment(\Core\ProjectBundle\Entity\Environment $environment)
    {
        $this->environments[] = $environment;

        return $this;
    }

    /**
     * Remove environment
     *
     * @param \Core\ProjectBundle\Entity\Environment $environment
     */
    public function removeEnvironment(\Core\ProjectBundle\Entity\Environment $environment)
    {
        $this->environments->removeElement($environment);
    }

    /**
     * Get environments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return "{$this->getLabel()} [{$this->getName()}]";
    }
}

