<?php

namespace DisplayBundle\Entity;


class DisplayUrl
{
    protected $project_id;
    protected $url;
    protected $group;
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
