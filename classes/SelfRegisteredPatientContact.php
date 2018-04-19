<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 5:11 PM
 */
class SelfRegisteredPatientContact implements JsonSerializable
{

	private $id;
	private $patient;
	private $nation;
	private $phone;
	private $type;
	private $primary;

	/**
	 * SelfRegisteredPatientContact constructor.
	 * @param $id
	 */
	public function __construct($id=null)
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
	 * @return SelfRegisteredPatientContact
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
	 * @param mixed $patient_id
	 * @return SelfRegisteredPatientContact
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getNation()
	{
		return $this->nation;
	}

	/**
	 * @param mixed $nation_id
	 * @return SelfRegisteredPatientContact
	 */
	public function setNation($nation)
	{
		$this->nation = $nation;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @param mixed $phone
	 * @return SelfRegisteredPatientContact
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
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
	 * @return SelfRegisteredPatientContact
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 * @param mixed $primary
	 * @return SelfRegisteredPatientContact
	 */
	public function setPrimary($primary)
	{
		$this->primary = $primary;
		return $this;
	}


	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
}