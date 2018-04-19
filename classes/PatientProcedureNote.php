<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:42 PM
 */

class PatientProcedureNote implements JsonSerializable {

    private $id;
    private $patient_procedure;
    private $specialty;
    private $note;
    private $type;
    private $staff;
    private $note_time;

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
    public function getSpecialty()
    {
        return $this->specialty;
    }

    /**
     * @param mixed $specialty
     */
    public function setSpecialty($specialty)
    {
        $this->specialty = $specialty;
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
     */
    public function setType($type)
    {
        $this->type = $type;
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
     */
    public function setNote($note)
    {
        $this->note = $note;
    }


    /**
     * @return mixed
     */
    public function getNoteTime()
    {
        return $this->note_time;
    }

    /**
     * @param mixed $note_time
     */
    public function setNoteTime($note_time)
    {
        $this->note_time = $note_time;
    }

    /**
     * @return mixed
     */
    public function getPatientProcedure()
    {
        return $this->patient_procedure;
    }

    /**
     * @param mixed $patient_procedure
     */
    public function setPatientProcedure($patient_procedure)
    {
        $this->patient_procedure = $patient_procedure;
    }

    /**
     * @return mixed
     */
    public function getStaff()
    {
        return $this->staff;
    }

    /**
     * @param mixed $staff
     */
    public function setStaff($staff)
    {
        $this->staff = $staff;
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