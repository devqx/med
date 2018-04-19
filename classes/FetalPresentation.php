<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/16
 * Time: 5:09 PM
 */
class FetalPresentation implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * FetalPresentation constructor.
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
     * @return FetalPresentation
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return FetalPresentation
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}