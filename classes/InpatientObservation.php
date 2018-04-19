<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/16
 * Time: 12:53 PM
 */
class InpatientObservation implements JsonSerializable
{
	private $id;
	private $inPatient;
	private $dateEntered;
	private $user;
	private $note;

	/**
	 * InpatientObservation constructor.
	 * @param $id
	 */
	public function __construct($id = NULL) { $this->id = $id; }

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
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
	 * @return InpatientObservation
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
		return $this->inPatient;
	}

	/**
	 * @param mixed $inPatient
	 * @return InpatientObservation
	 */
	public function setInPatient($inPatient)
	{
		$this->inPatient = $inPatient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDateEntered()
	{
		return $this->dateEntered;
	}

	/**
	 * @param mixed $dateEntered
	 * @return InpatientObservation
	 */
	public function setDateEntered($dateEntered)
	{
		$this->dateEntered = $dateEntered;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 * @return InpatientObservation
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	 * @return InpatientObservation
	 */
	public function setNote($note)
	{
		$this->note = $note;
		return $this;
	}
}