<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/18/16
 * Time: 2:28 PM
 */
class InsuranceType implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * InsuranceType constructor.
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
     * @return InsuranceType
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
     * @return InsuranceType
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }



}