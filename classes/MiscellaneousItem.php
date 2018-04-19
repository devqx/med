<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/23/16
 * Time: 12:54 PM
 */
class MiscellaneousItem implements JsonSerializable
{
    private $id;
    private $name;
    private $code;

    /**
     * MiscellaneousItem constructor.
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
     * @return MiscellaneousItem
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
     * @return MiscellaneousItem
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
     * @return MiscellaneousItem
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}