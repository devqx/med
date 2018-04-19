<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/12/14
 * Time: 10:35 AM
 */
class LabSpecimen implements JsonSerializable
{
    private $id;
    private $name;

    function __construct($id=NULL) {
        $this->id = $id;
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
    public function getId()
    {
        return $this->id;
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
    public function getName()
    {
        return $this->name;
    }


    public function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


} 