<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 3:22 PM
 */
class PriorART implements JsonSerializable
{
    private $id;
    private $code;
    private $name;

    /**
     * PriorART constructor.
     * @param $id
     */
    public function __construct($id = NULL)
    {
        $this->id = $id;
    }

    function jsonSerialize()
    {
        // Implement jsonSerialize() method.
        return (object)get_object_vars($this);
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
     * @return PriorART
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
     * @return PriorART
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return PriorART
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }



}