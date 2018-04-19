<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/9/16
 * Time: 5:34 PM
 */
class FetalBrainRelationship implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * FetalBrainRelationship constructor.
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
     * @return FetalBrainRelationship
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
     * @return FetalBrainRelationship
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }


}