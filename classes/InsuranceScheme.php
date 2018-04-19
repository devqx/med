<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceScheme
 *
 * @author pauldic
 */
class InsuranceScheme implements JsonSerializable
{
	private $id;
	private $badge;
	private $name;
	private $insurer;
	private $payType;
	private $insuranceType;
	private $individualRegCost;
	private $companyRegCost;
	private $creditLimit;
	private $hospital;
	private $receivablesAccount;
	private $discountAccount;
	private $partner;
	private $email;
	private $phone;
	private $logoUrl;
	private $clinicalServicesRate;
	private $enroleesMax;
	private $is_reference = false;

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
	 * @return InsuranceScheme
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBadge()
	{
		return $this->badge;
	}

	/**
	 * @param mixed $badge
	 * @return InsuranceScheme
	 */
	public function setBadge($badge)
	{
		$this->badge = $badge;
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
	 * @return InsuranceScheme
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInsurer()
	{
		return $this->insurer;
	}

	/**
	 * @param mixed $insurer
	 * @return InsuranceScheme
	 */
	public function setInsurer($insurer)
	{
		$this->insurer = $insurer;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->payType;
	}

	/**
	 * @param mixed $payType
	 * @return InsuranceScheme
	 */
	public function setType($payType)
	{
		$this->payType = $payType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getInsuranceType()
	{
		return $this->insuranceType;
	}

	/**
	 * @param mixed $insuranceType
	 * @return InsuranceScheme
	 */
	public function setInsuranceType($insuranceType)
	{
		$this->insuranceType = $insuranceType;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIndividualRegCost()
	{
		return $this->individualRegCost;
	}

	/**
	 * @param mixed $individualRegCost
	 * @return InsuranceScheme
	 */
	public function setIndividualRegCost($individualRegCost)
	{
		$this->individualRegCost = $individualRegCost;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompanyRegCost()
	{
		return $this->companyRegCost;
	}

	/**
	 * @param mixed $companyRegCost
	 * @return InsuranceScheme
	 */
	public function setCompanyRegCost($companyRegCost)
	{
		$this->companyRegCost = $companyRegCost;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreditLimit()
	{
		return $this->creditLimit;
	}

	/**
	 * @param mixed $creditLimit
	 * @return InsuranceScheme
	 */
	public function setCreditLimit($creditLimit)
	{
		$this->creditLimit = $creditLimit;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getHospital()
	{
		return $this->hospital;
	}

	/**
	 * @param mixed $hospital
	 * @return InsuranceScheme
	 */
	public function setHospital($hospital)
	{
		$this->hospital = $hospital;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getReceivablesAccount()
	{
		return $this->receivablesAccount;
	}

	/**
	 * @param mixed $receivablesAccount
	 * @return InsuranceScheme
	 */
	public function setReceivablesAccount($receivablesAccount)
	{
		$this->receivablesAccount = $receivablesAccount;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDiscountAccount()
	{
		return $this->discountAccount;
	}

	/**
	 * @param mixed $discountAccount
	 * @return InsuranceScheme
	 */
	public function setDiscountAccount($discountAccount)
	{
		$this->discountAccount = $discountAccount;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getPartner()
	{
		return $this->partner;
	}

	/**
	 * @param mixed $partner
	 * @return InsuranceScheme
	 */
	public function setPartner($partner)
	{
		$this->partner = $partner;
		return $this;
	}

	function __toString()
	{
		if ($this->getBadge() != null) {
			return $this->badge->getIcon() . " " . $this->name;
		}
		return $this->name;
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
	 * @return InsuranceScheme
	 */
	public function setEmail($email)
	{
		$this->email = $email;
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
	 * @return InsuranceScheme
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLogoUrl()
	{
		return $this->logoUrl;
	}

	/**
	 * @param mixed $logoUrl
	 * @return InsuranceScheme
	 */
	public function setLogoUrl($logoUrl)
	{
		$this->logoUrl = $logoUrl;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getClinicalServicesRate()
	{
		return $this->clinicalServicesRate;
	}

	/**
	 * @param mixed $clinicalServicesRate
	 * @return InsuranceScheme
	 */
	public function setClinicalServicesRate($clinicalServicesRate)
	{
		$this->clinicalServicesRate = $clinicalServicesRate;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getEnroleesMax()
	{
		return $this->enroleesMax;
	}
	
	/**
	 * @param mixed $enroleesMax
	 *
	 * @return InsuranceScheme
	 */
	public function setEnroleesMax($enroleesMax)
	{
		$this->enroleesMax = $enroleesMax;
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isReference(): bool
	{
		return $this->is_reference;
	}
	
	/**
	 * @param bool $is_reference
	 *
	 * @return InsuranceScheme
	 */
	public function setIsReference(bool $is_reference): InsuranceScheme
	{
		$this->is_reference = $is_reference;
		return $this;
	}

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

}
