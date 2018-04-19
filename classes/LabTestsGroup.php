<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/5/14
 * Time: 10:43 AM
 */
class LabTestsGroup implements JsonSerializable
{
    private $id;
    private $name;
    private $contained_tests;

    /**
     * @param mixed $contained_tests
     */
    public function setContainedTests($contained_tests)
    {
        $this->contained_tests = $contained_tests;
    }

    /**
     * @return mixed
     */
    public function getContainedTests()
    {
        return $this->contained_tests;
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
    public function getId()
    {
        return $this->id;
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
    public function getName()
    {
        return $this->name;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


} 