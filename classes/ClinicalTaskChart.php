<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 10:59 PM
 */
class ClinicalTaskChart implements JsonSerializable
{
	private $id;
	private $InPatient;
	private $patient;
	private $clinicalTaskData;
	private $nursingService;
	private $value;
	private $collectedBy;
	private $collectedDate;
	private $comment;

	public function __construct($id = NULL)
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
	 * @return ClinicalTaskChart
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInPatient()
	{
		return $this->InPatient;
	}

	/**
	 * @param mixed $InPatient
	 * @return ClinicalTaskChart
	 */
	public function setInPatient($InPatient)
	{
		$this->InPatient = $InPatient;
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
	 * @return ClinicalTaskChart
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
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
	 * @return ClinicalTaskChart
	 */
	public function setClinicalTaskData($clinicalTaskData)
	{
		$this->clinicalTaskData = $clinicalTaskData;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNursingService()
	{
		return $this->nursingService;
	}

	/**
	 * @param mixed $nursingService
	 * @return ClinicalTaskChart
	 */
	public function setNursingService($nursingService)
	{
		$this->nursingService = $nursingService;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return ClinicalTaskChart
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCollectedBy()
	{
		return $this->collectedBy;
	}

	/**
	 * @param mixed $collectedBy
	 * @return ClinicalTaskChart
	 */
	public function setCollectedBy($collectedBy)
	{
		$this->collectedBy = $collectedBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCollectedDate()
	{
		return $this->collectedDate;
	}

	/**
	 * @param mixed $collectedDate
	 * @return ClinicalTaskChart
	 */
	public function setCollectedDate($collectedDate)
	{
		$this->collectedDate = $collectedDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param mixed $comment
	 * @return ClinicalTaskChart
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
		return $this;
	}


	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}
}