<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/15
 * Time: 4:21 PM
 */
class PatientProcedureRegimen implements JsonSerializable
{
    private $id;
    private $patient_procedure;
    private $drug;
    private $drug_generic;
    private $note;
    private $batch;
    private $unit;
    private $quantity;
    private $requestTime;
    private $requestingUser;
    private $pharmacy;
    private $price;
    private $status;
    private $billLine;

    /**
     * PatientProcedureRegimen constructor.
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
    public function getDrug()
    {
        return $this->drug;
    }

    /**
     * @param mixed $drug
     */
    public function setDrug($drug)
    {
        $this->drug = $drug;
    }

    /**
     * @return mixed
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * @param mixed $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getDrugGeneric()
    {
        return $this->drug_generic;
    }

    /**
     * @param mixed $drug_generic
     */
    public function setDrugGeneric($drug_generic)
    {
        $this->drug_generic = $drug_generic;
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
    public function getBatch()
    {
        return $this->batch;
    }

    /**
     * @param mixed $batch
     */
    public function setBatch($batch)
    {
        $this->batch = $batch;
    }

    /**
     * @return mixed
     */
    public function getPharmacy()
    {
        return $this->pharmacy;
    }

    /**
     * @param mixed $pharmacy
     */
    public function setPharmacy($pharmacy)
    {
        $this->pharmacy = $pharmacy;
    }


    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param mixed $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }

    /**
     * @param mixed $requestTime
     */
    public function setRequestTime($requestTime)
    {
        $this->requestTime = $requestTime;
    }

    /**
     * @return mixed
     */
    public function getRequestingUser()
    {
        return $this->requestingUser;
    }

    /**
     * @param mixed $requestingUser
     */
    public function setRequestingUser($requestingUser)
    {
        $this->requestingUser = $requestingUser;
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
	public function getBillLine()
	{
		return $this->billLine;
	}
	
	/**
	 * @param mixed $billLine
	 *
	 * @return PatientProcedureRegimen
	 */
	public function setBillLine($billLine)
	{
		$this->billLine = $billLine;
		return $this;
	}

    
    
  

}