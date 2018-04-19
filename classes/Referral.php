<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/15
 * Time: 1:40 PM
 */
class Referral implements JsonSerializable
{
	private $id;
	private $company;
	private $name;
	private $phone;
	private $email;
	private $specialization;
	private $bankName;
	private $accountNumber;
	
	/**
	 * Referral constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
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
	 *
	 * @return Referral
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 *
	 * @return Referral
	 */
	public function setCompany($company)
	{
		$this->company = $company;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @param mixed $name
	 *
	 * @return Referral
	 */
	public function setName($name)
	{
		$this->name = $name;
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
	 *
	 * @return Referral
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return $this->email;
	}
	
	/**
	 * @param mixed $email
	 *
	 * @return Referral
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getSpecialization()
	{
		return $this->specialization;
	}
	
	/**
	 * @param mixed $specialization
	 *
	 * @return Referral
	 */
	public function setSpecialization($specialization)
	{
		$this->specialization = $specialization;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getBankName()
	{
		return $this->bankName;
	}
	
	/**
	 * @param mixed $bankName
	 *
	 * @return Referral
	 */
	public function setBankName($bankName)
	{
		$this->bankName = $bankName;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAccountNumber()
	{
		return $this->accountNumber;
	}
	
	/**
	 * @param mixed $accountNumber
	 *
	 * @return Referral
	 */
	public function setAccountNumber($accountNumber)
	{
		$this->accountNumber = $accountNumber;
		return $this;
	}
	
}