<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 5:47 PM
 */
class Nation implements JsonSerializable
{
	private $id;
	private $iso_alpha2_code;
	private $iso_alpha3_code;
	private $dailing_code;
	private $iso_numberic;
	private $country_name;

	/**
	 * Nation constructor.
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
	 * @return Nation
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIsoAlpha2Code()
	{
		return $this->iso_alpha2_code;
	}

	/**
	 * @param mixed $iso_alpha2_code
	 * @return Nation
	 */
	public function setIsoAlpha2Code($iso_alpha2_code)
	{
		$this->iso_alpha2_code = $iso_alpha2_code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIsoAlpha3Code()
	{
		return $this->iso_alpha3_code;
	}

	/**
	 * @param mixed $iso_alpha3_code
	 * @return Nation
	 */
	public function setIsoAlpha3Code($iso_alpha3_code)
	{
		$this->iso_alpha3_code = $iso_alpha3_code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDailingCode()
	{
		return $this->dailing_code;
	}

	/**
	 * @param mixed $dailing_code
	 * @return Nation
	 */
	public function setDailingCode($dailing_code)
	{
		$this->dailing_code = $dailing_code;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIsoNumberic()
	{
		return $this->iso_numberic;
	}

	/**
	 * @param mixed $iso_numberic
	 * @return Nation
	 */
	public function setIsoNumberic($iso_numberic)
	{
		$this->iso_numberic = $iso_numberic;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCountryName()
	{
		return $this->country_name;
	}

	/**
	 * @param mixed $country_name
	 * @return Nation
	 */
	public function setCountryName($country_name)
	{
		$this->country_name = $country_name;
		return $this;
	}

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


}