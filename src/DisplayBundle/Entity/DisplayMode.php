<?php

namespace DisplayBundle\Entity;


class DisplayMode
{
    protected $project_id;
    protected $mode_id;
    protected $width;
    protected $height;

    /**
     * @var integer
     */
    private $id;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


}
