<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Insurance
 *
 * @author pauldic
 */
class Insurance implements JsonSerializable
{
	private $id;
	private $active;
	private $patient;
	private $scheme;
	private $expirationDate;
	private $policyNumber;
	private $enrolleeId;
	private $parentEnrolleeId;
	private $coverageType;
	private $company;

	private $dependent;
	private $external;


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
	 * @return Insurance
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * @param mixed $active
	 * @return Insurance
	 */
	public function setActive($active)
	{
		$this->active = $active;
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
	 * @return Insurance
	 */
	public function setPatient($patient)
	{
		$this->patient = $patient;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * @param mixed $scheme
	 * @return Insurance
	 */
	public function setScheme($scheme)
	{
		$this->scheme = $scheme;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExpirationDate()
	{
		return $this->expirationDate;
	}

	/**
	 * @param mixed $expirationDate
	 * @return Insurance
	 */
	public function setExpirationDate($expirationDate)
	{
		$this->expirationDate = $expirationDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPolicyNumber()
	{
		return $this->policyNumber;
	}

	/**
	 * @param mixed $policyNumber
	 * @return Insurance
	 */
	public function setPolicyNumber($policyNumber)
	{
		$this->policyNumber = $policyNumber;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getEnrolleeId()
	{
		return $this->enrolleeId;
	}

	/**
	 * @param mixed $enrolleeId
	 * @return Insurance
	 */
	public function setEnrolleeId($enrolleeId)
	{
		$this->enrolleeId = $enrolleeId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCoverageType()
	{
		return $this->coverageType;
	}

	/**
	 * @param mixed $coverageType
	 * @return Insurance
	 */
	public function setCoverageType($coverageType)
	{
		$this->coverageType = $coverageType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompany()
	{
		return $this->company;
	}

	/**
	 * @param mixed $company
	 * @return Insurance
	 */
	public function setCompany($company)
	{
		$this->company = $company;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDependent()
	{
		return $this->dependent;
	}

	/**
	 * @param mixed $dependent
	 * @return Insurance
	 */
	public function setDependent($dependent)
	{
		$this->dependent = $dependent;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getParentEnrolleeId()
	{
		return $this->parentEnrolleeId;
	}

	/**
	 * @param mixed $parentEnrolleeId
	 * @return Insurance
	 */
	public function setParentEnrolleeId($parentEnrolleeId)
	{
		$this->parentEnrolleeId = $parentEnrolleeId;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getExternal()
	{
		return $this->external;
	}

	/**
	 * @param mixed $external
	 * @return Insurance
	 */
	public function setExternal($external)
	{
		$this->external = $external;
		return $this;
	}


	public function getPolicyDetails()
	{
		$ret = array();
		if ($this->getPolicyNumber() !== null && $this->getPolicyNumber() !== "") {
			$ret[] = $this->getPolicyNumber();
		}
		if ($this->getEnrolleeId() !== null && $this->getEnrolleeId() !== "") {
			$ret[] = $this->getEnrolleeId();
		}

		return implode("/", $ret);
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

}
