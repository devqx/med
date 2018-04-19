<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NotificationOptions
 *
 * @author pauldic
 */
class ClinicalTask implements JsonSerializable
{
	private $id;
	private $patient;
	private $inPatient;
	private $objective;
	private $entryTime;
	private $status;
	private $source;
	private $sourceInstance;

	private $clinicalTaskData;

	function __construct($id = null)
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
	 * @return ClinicalTask
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
	 * @return ClinicalTask
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return ClinicalTask
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getObjective()
	{
		return $this->objective;
	}

	/**
	 * @param mixed $objective
	 * @return ClinicalTask
	 */
	public function setObjective($objective)
	{
		$this->objective = $objective;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEntryTime()
	{
		return $this->entryTime;
	}

	/**
	 * @param mixed $entryTime
	 * @return ClinicalTask
	 */
	public function setEntryTime($entryTime)
	{
		$this->entryTime = $entryTime;
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
	 * @return ClinicalTask
	 */
	public function setStatus($status)
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinicalTaskData()
	{
		return $this->clinicalTaskData;
	}

	/**
	 * @param mixed $clinicalTaskData
	 * @return ClinicalTask
	 */
	public function setClinicalTaskData($clinicalTaskData)
	{
		$this->clinicalTaskData = $clinicalTaskData;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @param mixed $source
	 * @return ClinicalTask
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSourceInstance()
	{
		return $this->sourceInstance;
	}

	/**
	 * @param mixed $sourceInstance
	 * @return ClinicalTask
	 */
	public function setSourceInstance($sourceInstance)
	{
		$this->sourceInstance = $sourceInstance;
		return $this;
	}



	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

}
//