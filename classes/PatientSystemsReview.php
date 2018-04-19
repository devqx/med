<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/15
 * Time: 3:16 PM
 */

class PatientSystemsReview implements JsonSerializable
{
    private $id;
    private $patient;
    private $date;
    private $systemsReview;
    private $reviewer;

    private $assessmentInstance;
    private $antenatalInstance;
    private $type;

    private $encounter;

    /**
     * PatientSystemsReview constructor.
     * @param $id
     */
    public function __construct($id=NULL)
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
     * @return PatientSystemsReview
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
     * @return PatientSystemsReview
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
     * @return PatientSystemsReview
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSystemsReview()
    {
        return $this->systemsReview;
    }

    /**
     * @param mixed $systemsReview
     * @return PatientSystemsReview
     */
    public function setSystemsReview($systemsReview)
    {
        $this->systemsReview = $systemsReview;
        return $this;
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
     * @return PatientSystemsReview
     */
    public function setReviewer($reviewer)
    {
        $this->reviewer = $reviewer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssessmentInstance()
    {
        return $this->assessmentInstance;
    }

    /**
     * @param mixed $assessmentInstance
     * @return PatientSystemsReview
     */
    public function setAssessmentInstance($assessmentInstance)
    {
        $this->assessmentInstance = $assessmentInstance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAntenatalInstance()
    {
        return $this->antenatalInstance;
    }

    /**
     * @param mixed $antenatalInstance
     * @return PatientSystemsReview
     */
    public function setAntenatalInstance($antenatalInstance)
    {
        $this->antenatalInstance = $antenatalInstance;
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
     * @return PatientSystemsReview
     */
    public function setType($type)
    {
        $this->type = $type;
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

    /**
     * @return mixed
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * @param mixed $encounter
     * @return PatientSystemsReview
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }


}