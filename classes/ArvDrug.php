<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/1/16
 * Time: 12:32 PM
 */
class ArvDrug implements JsonSerializable
{
    private $id;
    private $name;

    /**
     * ArvDrug constructor.
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
     * @return ArvDrug
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
     * @return ArvDrug
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