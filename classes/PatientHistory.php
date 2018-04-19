<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:11 PM
 */
class PatientHistory implements JsonSerializable
{
	private $id;
	private $patient;
	private $history;
	private $creator;
	private $date;
	private $data;

	/**
	 * PatientHistory constructor.
	 * @param $id
	 */
	public function __construct($id = null)
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
	public function getHistory()
	{
		return $this->history;
	}

	/**
	 * @param mixed $history
	 */
	public function setHistory($history)
	{
		$this->history = $history;
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
	 */
	public function setCreator($creator)
	{
		$this->creator = $creator;
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
	 */
	public function setDate($date)
	{
		$this->date = $date;
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * @param mixed $data
	 */
	public function setData($data)
	{
		$this->data = $data;
	}


}