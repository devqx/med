<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/15
 * Time: 1:19 PM
 */
class KinRelation implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * KinRelation constructor.
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

    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}