<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:11 PM
 */
class ArvPatientHistory implements JsonSerializable
{
    private $id;
    private $patient;
    private $history;
    private $creator;
    private $date;
    private $data;

    /**
     * PatientHistory constructor.
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
     * @return ArvPatientHistory
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
     * @return ArvPatientHistory
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHistory()
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     * @return ArvPatientHistory
     */
    public function setHistory($history)
    {
        $this->history = $history;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param mixed $creator
     * @return ArvPatientHistory
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return ArvPatientHistory
     */
    public function setDate($date)
    {
        $this->date = $date;
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
     * @return ArvPatientHistory
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    


}