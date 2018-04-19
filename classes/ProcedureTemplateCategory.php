<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/15
 * Time: 12:01 PM
 */
class ProcedureTemplateCategory implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * ProcedureTemplateCategory constructor.
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