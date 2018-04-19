<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 3:16 PM
 */

class PatientPhysicalExam implements JsonSerializable
{
    private $id;
    private $patient;
    private $date;
    private $physicalExamination;
    private $reviewer;
    private $encounter;

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
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * @param mixed $patient
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
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
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getPhysicalExamination()
    {
        return $this->physicalExamination;
    }

    /**
     * @param mixed $physicalExamination
     */
    public function setPhysicalExamination($physicalExamination)
    {
        $this->physicalExamination = $physicalExamination;
    }

    /**
     * @return mixed
     */
    public function getReviewer()
    {
        return $this->reviewer;
    }

    /**
     * @param mixed $reviewer
     */
    public function setReviewer($reviewer)
    {
        $this->reviewer = $reviewer;
    }

    function jsonSerialize()
    {
        return (object)get_object_vars($this);
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
     * @return PatientPhysicalExam
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }
}