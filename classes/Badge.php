<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 2:59 PM
 */
class Badge implements JsonSerializable
{
    private $id;
    private $name;
    private $icon;

    /**
     * Badge constructor.
     * @param $id
     */
    public function __construct($id=NULL)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param mixed $icon
     */
    //This is going to be a valid html that would represent the badge: img, div, span, etc
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }

    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}