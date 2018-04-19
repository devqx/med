<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Clinic
 *
 * @author pauldic
 */
class Clinic implements JsonSerializable
{
	private $id;
	private $name;
	private $address;
	private $lga;
	private $code;
	private $folioPrefix;
	private $locationLat;
	private $locationLong;
	private $klass;
	private $phoneNo;
	private $logoFile;
	private $header;
	private $logoLebel;

	//false for danferd
	public static $useHeader = true;
	//true for limi
	//false for FMC
	public static $editStyleByAdd = true;

	function __construct($id = null)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function getLga()
	{
		return $this->lga;
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getLocationLat()
	{
		return $this->locationLat;
	}

	public function getLocationLong()
	{
		return $this->locationLong;
	}

	public function getKlass()
	{
		return $this->klass;
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setAddress($address)
	{
		$this->address = $address;
	}

	public function setLga($lga)
	{
		$this->lga = $lga;
	}

	public function setCode($hospcode)
	{
		$this->code = $hospcode;
	}

	public function setLocationLat($locationLat)
	{
		$this->locationLat = $locationLat;
	}

	public function setLocationLong($locationLong)
	{
		$this->locationLong = $locationLong;
	}

	public function setKlass($klass)
	{
		$this->klass = $klass;
	}

	public function getPhoneNo()
	{
		return $this->phoneNo;
	}

	public function setPhoneNo($phoneNo)
	{
		$this->phoneNo = $phoneNo;
	}

	public function getLogoFile()
	{
		return "/img/logo/logo.jpg";
	}

	/**
	 * @return mixed
	 */
	public function getHeader()
	{
		return '<div class="container" style="margin: 5px auto;"><img align="left" src="' . $this->getLogoFile() . '?rand=' . rand(0, 10) . '" style="height: 100px; margin-right:50px;max-width:250px"><h2>' . $this->getName() . '</h2><h4>' . $this->getAddress() . '</h4><h4>' . $this->getPhoneNo() . '</h4></div><hr>';
	}

	/**
	 * @param mixed $header
	 * @return Clinic
	 */
	public function setHeader($header)
	{
		$this->header = $header;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFolioPrefix()
	{
		return $this->folioPrefix;
	}
	
	/**
	 * @param mixed $folioPrefix
	 *
	 * @return Clinic
	 */
	public function setFolioPrefix($folioPrefix)
	{
		$this->folioPrefix = $folioPrefix;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getLogoLebel()
	{
		return '<div><img style="width: 100px; height: auto;" align="left" src="' . $this->getLogoFile() . '?rand=' . rand(0, 10) . '" ><label>' . $this->getName() . '</label><label>' . $this->getAddress() . '</label><label>' . $this->getPhoneNo() . '</label></div><hr>';
		
	}
	
	/**
	 * @param mixed $logoLebel
	 *
	 * @return Clinic
	 */
	public function setLogoLebel($logoLebel)
	{
		$this->logoLebel = $logoLebel;
		return $this;
	}
	
	
	

	public function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}


}
