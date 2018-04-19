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
class ClinicalTaskData implements JsonSerializable
{
	private $id;
	private $clinicalTask;
	private $dose;
	private $drug;
	private $generic;
	private $frequency;
	private $entryTime;
	private $lastRoundTime;
	private $endRoundTime;
	private $roundCount;
	private $taskCount;
	private $status;
	private $type;
	private $cancelReason;
	private $cancelledBy;
	private $cancelTime;
	private $createdBy;
	private $description;

	private $nextRoundTime;
	private $lastReading;
	private $readings;
	private $startTime;

	private $billed;

	private $private;

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
	 * @return ClinicalTaskData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinicalTask()
	{
		return $this->clinicalTask;
	}

	/**
	 * @param mixed $clinicalTask
	 * @return ClinicalTaskData
	 */
	public function setClinicalTask($clinicalTask)
	{
		$this->clinicalTask = $clinicalTask;
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
	 * @return ClinicalTaskData
	 */
	public function setDose($dose)
	{
		$this->dose = $dose;
		return $this;
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
	 * @return ClinicalTaskData
	 */
	public function setDrug($drug)
	{
		$this->drug = $drug;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getGeneric()
	{
		return $this->generic;
	}

	/**
	 * @param mixed $generic
	 * @return ClinicalTaskData
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getFrequency()
	{
		return $this->frequency;
	}

	/**
	 * @param mixed $frequency
	 * @return ClinicalTaskData
	 */
	public function setFrequency($frequency)
	{
		$this->frequency = $frequency;
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
	 * @return ClinicalTaskData
	 */
	public function setEntryTime($entryTime)
	{
		$this->entryTime = $entryTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastRoundTime()
	{
		return $this->lastRoundTime;
	}

	/**
	 * @param mixed $lastRoundTime
	 * @return ClinicalTaskData
	 */
	public function setLastRoundTime($lastRoundTime)
	{
		$this->lastRoundTime = $lastRoundTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEndRoundTime()
	{
		return $this->endRoundTime;
	}

	/**
	 * @param mixed $endRoundTime
	 * @return ClinicalTaskData
	 */
	public function setEndRoundTime($endRoundTime)
	{
		$this->endRoundTime = $endRoundTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRoundCount()
	{
		return $this->roundCount;
	}

	/**
	 * @param mixed $roundCount
	 * @return ClinicalTaskData
	 */
	public function setRoundCount($roundCount)
	{
		$this->roundCount = $roundCount;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getTaskCount()
	{
		return $this->taskCount;
	}

	/**
	 * @param mixed $taskCount
	 * @return ClinicalTaskData
	 */
	public function setTaskCount($taskCount)
	{
		$this->taskCount = $taskCount;
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
	 * @return ClinicalTaskData
	 */
	public function setStatus($status)
	{
		$this->status = $status;
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
	 * @return ClinicalTaskData
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelReason()
	{
		return $this->cancelReason;
	}

	/**
	 * @param mixed $cancelReason
	 * @return ClinicalTaskData
	 */
	public function setCancelReason($cancelReason)
	{
		$this->cancelReason = $cancelReason;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelledBy()
	{
		return $this->cancelledBy;
	}

	/**
	 * @param mixed $cancelledBy
	 * @return ClinicalTaskData
	 */
	public function setCancelledBy($cancelledBy)
	{
		$this->cancelledBy = $cancelledBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCancelTime()
	{
		return $this->cancelTime;
	}

	/**
	 * @param mixed $cancelTime
	 * @return ClinicalTaskData
	 */
	public function setCancelTime($cancelTime)
	{
		$this->cancelTime = $cancelTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @param mixed $createdBy
	 * @return ClinicalTaskData
	 */
	public function setCreatedBy($createdBy)
	{
		$this->createdBy = $createdBy;
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
	 * @return ClinicalTaskData
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNextRoundTime()
	{
		return $this->nextRoundTime;
	}

	/**
	 * @param mixed $nextRoundTime
	 * @return ClinicalTaskData
	 */
	public function setNextRoundTime($nextRoundTime)
	{
		$this->nextRoundTime = $nextRoundTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLastReading()
	{
		return $this->lastReading;
	}

	/**
	 * @param mixed $lastReading
	 * @return ClinicalTaskData
	 */
	public function setLastReading($lastReading)
	{
		$this->lastReading = $lastReading;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReadings()
	{
		return $this->readings;
	}

	/**
	 * @param mixed $readings
	 * @return ClinicalTaskData
	 */
	public function setReadings($readings)
	{
		$this->readings = $readings;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getStartTime()
	{
		return $this->startTime;
	}

	/**
	 * @param mixed $startTime
	 * @return ClinicalTaskData
	 */
	public function setStartTime($startTime)
	{
		$this->startTime = $startTime;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBilled()
	{
		return $this->billed;
	}

	/**
	 * @param mixed $billed
	 * @return ClinicalTaskData
	 */
	public function setBilled($billed)
	{
		$this->billed = $billed;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPrivate()
	{
		return $this->private;
	}

	/**
	 * @param mixed $private
	 * @return ClinicalTaskData
	 */
	public function setPrivate($private)
	{
		$this->private = $private;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function isBilled()
	{
		return (bool)$this->billed;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

}
//