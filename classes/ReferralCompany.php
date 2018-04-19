<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/15
 * Time: 1:37 PM
 */
class ReferralCompany implements JsonSerializable
{
	private $id;
	private $name;
	private $address;
	private $contactPhone;
	private $email;
	private $bankName;
	private $accountNumber;
	
	/**
	 * ReferralCompany constructor.
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
	 * @return ReferralCompany
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return ReferralCompany
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAddress()
	{
		return $this->address;
	}
	
	/**
	 * @param mixed $address
	 *
	 * @return ReferralCompany
	 */
	public function setAddress($address)
	{
		$this->address = $address;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getContactPhone()
	{
		return $this->contactPhone;
	}
	
	/**
	 * @param mixed $contactPhone
	 *
	 * @return ReferralCompany
	 */
	public function setContactPhone($contactPhone)
	{
		$this->contactPhone = $contactPhone;
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
	 * @return ReferralCompany
	 */
	public function setEmail($email)
	{
		$this->email = $email;
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
	 * @return ReferralCompany
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
	 * @return ReferralCompany
	 */
	public function setAccountNumber($accountNumber)
	{
		$this->accountNumber = $accountNumber;
		return $this;
	}
}