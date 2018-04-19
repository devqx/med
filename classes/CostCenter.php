<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/3/15
 * Time: 4:20 PM
 */
class CostCenter implements JsonSerializable
{
    private $id;
    private $name;
    private $description;
    private $analytical_code;

    /**
     * CostCenter constructor.
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

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getAnalyticalCode()
    {
        return $this->analytical_code;
    }

    /**
     * @param mixed $analytical_code
     */
    public function setAnalyticalCode($analytical_code)
    {
        $this->analytical_code = $analytical_code;
    }



    function jsonSerialize()
    {
        return (object) get_object_vars($this);
    }


}