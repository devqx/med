<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 4/16/17
 * Time: 3:47 PM
 */
class EstimatedBills implements JsonSerializable{
    private $id;
    private $es_code;
    private  $patient;
    private $procedure;
    private $inpatient;
    private $total_estimate;
    private $date_created;
    private $last_modified;
    private $scheme;
    private $narration;
    private $created_by;
    private $status;
    private $valid_till;
    private $estimate_bill_lines;

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
    public function getEsCode()
    {
        return $this->es_code;
    }

    /**
     * @param mixed $es_code
     */
    public function setEsCode($es_code)
    {
        $this->es_code = $es_code;
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
    public function getInpatient()
    {
        return $this->inpatient;
    }

    /**
     * @param mixed $inpatient
     */
    public function setInpatient($inpatient)
    {
        $this->inpatient = $inpatient;
    }


    /**
     * @return mixed
     */
    public function getTotalEstimate()
    {
        return $this->total_estimate;
    }

    /**
     * @param mixed $total_estimate
     */
    public function setTotalEstimate($total_estimate)
    {
        $this->total_estimate = $total_estimate;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param mixed $date_created
     */
    public function setDateCreated($date_created)
    {
        $this->date_created = $date_created;
    }

    /**
     * @return mixed
     */
    public function getLastModified()
    {
        return $this->last_modified;
    }

    /**
     * @param mixed $last_modified
     */
    public function setLastModified($last_modified)
    {
        $this->last_modified = $last_modified;
    }

    /**
     * @return mixed
     */
    public function getEstimateBillLines()
    {
        return $this->estimate_bill_lines;
    }

    /**
     * @param mixed $estimate_bill_lines
     */
    public function setEstimateBillLines($estimate_bill_lines)
    {
        $this->estimate_bill_lines = $estimate_bill_lines;
    }

    /**
     * @return mixed
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @param mixed $scheme
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    /**
     * @return mixed
     */
    public function getNarration()
    {
        return $this->narration;
    }

    /**
     * @param mixed $narration
     */
    public function setNarration($narration)
    {
        $this->narration = $narration;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * @param mixed $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getValidTill()
    {
        return $this->valid_till;
    }

    /**
     * @param mixed $valid_till
     */
    public function setValidTill($valid_till)
    {
        $this->valid_till = $valid_till;
    }



    public function jsonSerialize() {
        return (object) get_object_vars($this);
    }

}