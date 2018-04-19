<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/16/14
 * Time: 5:06 PM
 */
class PatientDiagnosis implements JsonSerializable
{
    private $id;
    private $patient;
    private $date;
    private $diagnosedBy;
    private $note;
    private $type;
    private $diagnosis;
    private $status;
    private $clinic;
    private $severity;
    private $encounter;
    private $inPatient;
    private $bodyPart;

    /**
     * @return mixed
     */
    public function getBodyPart()
    {
        return $this->bodyPart;
    }

    /**
     * @param mixed $bodyPart
     * @return PatientDiagnosis
     */
    public function setBodyPart($bodyPart)
    {
        $this->bodyPart = $bodyPart;
        return $this;
    }

    function __construct($id = NULL)
    {
        $this->id = $id;
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
     * @return PatientDiagnosis
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
     * @return PatientDiagnosis
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
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
     * @return PatientDiagnosis
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiagnosedBy()
    {
        return $this->diagnosedBy;
    }

    /**
     * @param mixed $diagnosedBy
     * @return PatientDiagnosis
     */
    public function setDiagnosedBy($diagnosedBy)
    {
        $this->diagnosedBy = $diagnosedBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     * @return PatientDiagnosis
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return PatientDiagnosis
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiagnosis()
    {
        return $this->diagnosis;
    }

    /**
     * @param mixed $diagnosis
     * @return PatientDiagnosis
     */
    public function setDiagnosis($diagnosis)
    {
        $this->diagnosis = $diagnosis;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return PatientDiagnosis
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClinic()
    {
        return $this->clinic;
    }

    /**
     * @param mixed $clinic
     * @return PatientDiagnosis
     */
    public function setClinic($clinic)
    {
        $this->clinic = $clinic;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounter
     * @return PatientDiagnosis
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param mixed $severity
     * @return PatientDiagnosis
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
        return $this;
    }

	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->inPatient;
	}

	/**
	 * @param mixed $inPatient
	 * @return PatientDiagnosis
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	
    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }


}