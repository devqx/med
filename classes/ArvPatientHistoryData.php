<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:12 PM
 */
class ArvPatientHistoryData implements JsonSerializable
{
    private $id;
    private $patientHistory;
    private $historyTemplateData;
    private $value;

    /**
     * PatientHistoryData constructor.
     * @param $id
     */
    public function __construct($id=NULL)
    {
        $this->id = $id;
    }

    function jsonSerialize()
    {
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
     * @return ArvPatientHistoryData
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientHistory()
    {
        return $this->patientHistory;
    }

    /**
     * @param mixed $patientHistory
     * @return ArvPatientHistoryData
     */
    public function setPatientHistory($patientHistory)
    {
        $this->patientHistory = $patientHistory;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHistoryTemplateData()
    {
        return $this->historyTemplateData;
    }

    /**
     * @param mixed $historyTemplateData
     * @return ArvPatientHistoryData
     */
    public function setHistoryTemplateData($historyTemplateData)
    {
        $this->historyTemplateData = $historyTemplateData;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return ArvPatientHistoryData
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }
    
}