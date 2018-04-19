<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/1/16
 * Time: 12:28 PM
 */
class ArvDrugData implements JsonSerializable
{
    private $id;
    private $patient;
    private $arvDrug;
    private $type;
    private $dose;
    private $state;
    private $prescribedBy;
    private $datePrescribed;

    /**
     * ArvDrugData constructor.
     * @param $id
     */
    public function __construct($id = NULL)
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
     * @return ArvDrugData
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
     * @return ArvDrugData
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArvDrug()
    {
        return $this->arvDrug;
    }

    /**
     * @param mixed $arvDrug
     * @return ArvDrugData
     */
    public function setArvDrug($arvDrug)
    {
        $this->arvDrug = $arvDrug;
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
     * @return ArvDrugData
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDose()
    {
        return $this->dose;
    }

    /**
     * @param mixed $dose
     * @return ArvDrugData
     */
    public function setDose($dose)
    {
        $this->dose = $dose;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     * @return ArvDrugData
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrescribedBy()
    {
        return $this->prescribedBy;
    }

    /**
     * @param mixed $prescribedBy
     * @return ArvDrugData
     */
    public function setPrescribedBy($prescribedBy)
    {
        $this->prescribedBy = $prescribedBy;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDatePrescribed()
    {
        return $this->datePrescribed;
    }

    /**
     * @param mixed $datePrescribed
     * @return ArvDrugData
     */
    public function setDatePrescribed($datePrescribed)
    {
        $this->datePrescribed = $datePrescribed;
        return $this;
    }

    
    

    function jsonSerialize()
    {
        // Implement jsonSerialize() method.
        return (object)get_object_vars($this);
    }


}