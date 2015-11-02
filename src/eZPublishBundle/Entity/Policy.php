<?php

namespace eZPublishBundle\Entity;

class Policy
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $function;

    /**
     * @var array
     */
    private $class;

    /**
     * @var array
     */
    private $path;

    /**
     * @var array
     */
    private $language;


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
     * Set module
     *
     * @param string $module
     *
     * @return Policy
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set function
     *
     * @param string $function
     *
     * @return Policy
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Set class
     *
     * @param array $class
     *
     * @return Policy
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return array
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set path
     *
     * @param array $path
     *
     * @return Policy
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return array
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set language
     *
     * @param array $language
     *
     * @return Policy
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return array
     */
    public function getLanguage()
    {
        return $this->language;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->language = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add language
     *
     * @param \eZPublishBundle\Entity\Language $language
     *
     * @return Policy
     */
    public function addLanguage(\eZPublishBundle\Entity\Language $language)
    {
        $this->language[] = $language;

        return $this;
    }

    /**
     * Remove language
     *
     * @param \eZPublishBundle\Entity\Language $language
     */
    public function removeLanguage(\eZPublishBundle\Entity\Language $language)
    {
        $this->language->removeElement($language);
    }
}
