<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:13 PM
 */
class AntenatalPackages implements JsonSerializable
{
	private $id;
	private $name;
	private $amount;
	private $items;
	private $code;
	
	public function __construct($id = null)
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
	 *
	 * @return AntenatalPackages
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
	 * @return AntenatalPackages
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * @param mixed $amount
	 *
	 * @return AntenatalPackages
	 */
	public function setAmount($amount)
	{
		$this->amount = $amount;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getCode()
	{
		return $this->code;
	}
	
	/**
	 * @param mixed $code
	 *
	 * @return AntenatalPackages
	 */
	public function setCode($code)
	{
		$this->code = $code;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getItems()
	{
		return $this->items;
	}
	
	/**
	 * @param mixed $items
	 *
	 * @return AntenatalPackages
	 */
	public function setItems($items)
	{
		$this->items = $items;
		return $this;
	}
}