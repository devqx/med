<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/18/16
 * Time: 1:50 PM
 */
class PatientProcedureNursingTask implements JsonSerializable
{
    private $id;
    private $patientProcedure;
    private $task;
    private $creator;
    private $when;
    private $serviceCentre;

    /**
     * @return mixed
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @param mixed $when
     * @return PatientProcedureNursingTask
     */
    public function setWhen($when)
    {
        $this->when = $when;
        return $this;
    }


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
     * @return PatientProcedureNursingTask
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientProcedure()
    {
        return $this->patientProcedure;
    }

    /**
     * @param mixed $patientProcedure
     * @return PatientProcedureNursingTask
     */
    public function setPatientProcedure($patientProcedure)
    {
        $this->patientProcedure = $patientProcedure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param mixed $task
     * @return PatientProcedureNursingTask
     */
    public function setTask($task)
    {
        $this->task = $task;
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
     * @return PatientProcedureNursingTask
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
        return $this;
    }
	
	/**
	 * @return mixed
	 */
	public function getServiceCentre()
	{
		return $this->serviceCentre;
	}
	
	/**
	 * @param mixed $serviceCentre
	 *
	 * @return PatientProcedureNursingTask
	 */
	public function setServiceCentre($serviceCentre)
	{
		$this->serviceCentre = $serviceCentre;
		return $this;
	}
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	
}