<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/20/16
 * Time: 1:29 PM
 */
class ArvConsulting implements JsonSerializable
{
    private $id;
    private $patient;
    private $comment;
    private $createUser;
    private $createTime;
    private $nextAppointment;

    private $data;

    /**
     * ArvConsulting constructor.
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
     * @return ArvConsulting
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     * @return ArvConsulting
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     * @return ArvConsulting
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateUser()
    {
        return $this->createUser;
    }

    /**
     * @param mixed $createUser
     * @return ArvConsulting
     */
    public function setCreateUser($createUser)
    {
        $this->createUser = $createUser;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param mixed $createTime
     * @return ArvConsulting
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNextAppointment()
    {
        return $this->nextAppointment;
    }

    /**
     * @param mixed $nextAppointment
     * @return ArvConsulting
     */
    public function setNextAppointment($nextAppointment)
    {
        $this->nextAppointment = $nextAppointment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     * @return ArvConsulting
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
    
}