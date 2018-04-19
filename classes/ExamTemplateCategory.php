<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/2/14
 * Time: 1:22 PM
 */

class ExamTemplateCategory implements JsonSerializable {
    private $id;
    private $name;

    function __construct($id=NULL)
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