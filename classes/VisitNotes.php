<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/8/14
 * Time: 11:41 AM
 */

class VisitNotes implements JsonSerializable {
    private $id;
    private $patient;
    private $dateOfEntry;
    private $notedBy;
    private $description;
    private $noteType;
    private $reason;
    private $hospital;

    private $encounter;

    /**
     * VisitNotes constructor.
     * @param $id
     */
    public function __construct($id = NULL)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDateOfEntry()
    {
        return $this->dateOfEntry;
    }

    public function setDateOfEntry($dateOfEntry)
    {
        $this->dateOfEntry = $dateOfEntry;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getHospital()
    {
        return $this->hospital;
    }

    /**
     * @param mixed $hospital
     * @return $this
     */
    public function setHospital($hospital)
    {
        $this->hospital = $hospital;
        return $this;

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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getNoteType()
    {
        return $this->noteType;
    }

    /**
     * @param mixed $noteType
     * @return $this
     */
    public function setNoteType($noteType)
    {
        $this->noteType = $noteType;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getNotedBy()
    {
        return $this->notedBy;
    }

    /**
     * @param mixed $notedBy
     * @return $this
     */
    public function setNotedBy($notedBy)
    {
        $this->notedBy = $notedBy;
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
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     * @return $this
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
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
     * @return VisitNotes
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }
    

    public function jsonSerialize()
    {
        return (object)get_object_vars($this);
    }

}