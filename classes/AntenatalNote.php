<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/26/15
 * Time: 3:51 PM
 */
class AntenatalNote implements JsonSerializable
{
	private $id;
	private $antenatalInstance;
	private $patient;
	private $note;
	private $type;
	private $enteredOn;
	private $enteredBy;
	private $assessment;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
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
	 * @return AntenatalNote
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return AntenatalNote
	 */
	public function setAntenatalInstance($antenatalInstance)
	{
		$this->antenatalInstance = $antenatalInstance;
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
	 * @return AntenatalNote
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
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
	 * @return AntenatalNote
	 */
	public function setNote($note)
	{
		$this->note = $note;
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
	 * @return AntenatalNote
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnteredOn()
	{
		return $this->enteredOn;
	}

	/**
	 * @param mixed $enteredOn
	 * @return AntenatalNote
	 */
	public function setEnteredOn($enteredOn)
	{
		$this->enteredOn = $enteredOn;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnteredBy()
	{
		return $this->enteredBy;
	}

	/**
	 * @param mixed $enteredBy
	 * @return AntenatalNote
	 */
	public function setEnteredBy($enteredBy)
	{
		$this->enteredBy = $enteredBy;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getAssessment()
	{
		return $this->assessment;
	}

	/**
	 * @param mixed $assessment
	 * @return AntenatalNote
	 */
	public function setAssessment($assessment)
	{
		$this->assessment = $assessment;
		return $this;
	}

    
}