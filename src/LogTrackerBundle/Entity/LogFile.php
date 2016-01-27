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
    private $path;

    /**
     * @var string
     */
    private $mask;

    /**
     * @var \Core\ProjectBundle\Entity\Link
     */
    private $link;


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
     * Set link
     *
     * @param \Core\ProjectBundle\Entity\Link $link
     *
     * @return LogFile
     */
    public function setLink(\Core\ProjectBundle\Entity\Link $link = null)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return \Core\ProjectBundle\Entity\Link
     */
    public function getLink()
    {
        return $this->link;
    }
}

