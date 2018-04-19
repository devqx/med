<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 3:21 PM
 */
class ModeOfTest implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * ModeOfTest constructor.
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
     * @return ModeOfTest
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
     * @return ModeOfTest
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }



}