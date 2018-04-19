<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/16
 * Time: 3:08 PM
 */
class ArvEnrollment implements JsonSerializable
{
    private $id;
    private $active;
    private $patient;
    private $uniqueId;
    private $careEntryPoint;
    private $dateHivConfirmed;
    private $locationOfTest;
    private $modeOfTest;
    private $priorART;
    private $enrolledOn;
    private $enrolledBy;
    private $enrolledAt;

    private $create_date;

    /**
     * STIEnrollment constructor.
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return ArvEnrollment
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     * @return ArvEnrollment
     */
    public function setActive($active)
    {
        $this->active = $active;
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
     * @return ArvEnrollment
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @param mixed $uniqueId
     * @return ArvEnrollment
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCareEntryPoint()
    {
        return $this->careEntryPoint;
    }

    /**
     * @param mixed $careEntryPoint
     * @return ArvEnrollment
     */
    public function setCareEntryPoint($careEntryPoint)
    {
        $this->careEntryPoint = $careEntryPoint;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateHivConfirmed()
    {
        return $this->dateHivConfirmed;
    }

    /**
     * @param mixed $dateHivConfirmed
     * @return ArvEnrollment
     */
    public function setDateHivConfirmed($dateHivConfirmed)
    {
        $this->dateHivConfirmed = $dateHivConfirmed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getModeOfTest()
    {
        return $this->modeOfTest;
    }

    /**
     * @param mixed $modeOfTest
     * @return ArvEnrollment
     */
    public function setModeOfTest($modeOfTest)
    {
        $this->modeOfTest = $modeOfTest;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocationOfTest()
    {
        return $this->locationOfTest;
    }

    /**
     * @param mixed $locationOfTest
     * @return ArvEnrollment
     */
    public function setLocationOfTest($locationOfTest)
    {
        $this->locationOfTest = $locationOfTest;
        return $this;
    }

    
    /**
     * @return mixed
     */
    public function getPriorART()
    {
        return $this->priorART;
    }

    /**
     * @param mixed $priorART
     * @return ArvEnrollment
     */
    public function setPriorART($priorART)
    {
        $this->priorART = $priorART;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnrolledOn()
    {
        return $this->enrolledOn;
    }

    /**
     * @param mixed $enrolledOn
     * @return ArvEnrollment
     */
    public function setEnrolledOn($enrolledOn)
    {
        $this->enrolledOn = $enrolledOn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnrolledBy()
    {
        return $this->enrolledBy;
    }

    /**
     * @param mixed $enrolledBy
     * @return ArvEnrollment
     */
    public function setEnrolledBy($enrolledBy)
    {
        $this->enrolledBy = $enrolledBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEnrolledAt()
    {
        return $this->enrolledAt;
    }

    /**
     * @param mixed $enrolledAt
     * @return ArvEnrollment
     */
    public function setEnrolledAt($enrolledAt)
    {
        $this->enrolledAt = $enrolledAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param mixed $create_date
     * @return ArvEnrollment
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }
}