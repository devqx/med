<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 3:20 PM
 */
class CareEntryPoint implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * CarePointEntry constructor.
     * @param $id
     */
    public function __construct($id = NULL)
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
     * @return CareEntryPoint
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
     * @return CareEntryPoint
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    function jsonSerialize()
    {
        // Implement jsonSerialize() method.
        return (object)get_object_vars($this);
    }



}