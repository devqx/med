<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/3/16
 * Time: 9:46 AM
 */
class Form implements JsonSerializable
{
    private $id;
    private $name;
    private $components;

    /**
     * Form constructor.
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
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     * @return Form
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
     * @return Form
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @param mixed $components
     * @return Form
     */
    public function setComponents($components)
    {
        $this->components = $components;
        return $this;
    }

}