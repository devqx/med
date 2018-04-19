<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 1/9/17
 * Time: 1:52 PM
 */
class ItemBatch implements JsonSerializable
{

	private $id;
	private $name;
	private $item;
	private $quantity;
	private $expirationDate;
	private $serviceCenter;

	/**
	 * ItemBatch constructor.
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
	 * @return ItemBatch
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
	 * @return ItemBatch
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @param mixed $item
	 * @return ItemBatch
	 */
	public function setItem($item)
	{
		$this->item = $item;
		return $this;
	}

	

	/**
	 * @return mixed
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * @param mixed $quantity
	 * @return ItemBatch
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
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
	 * @return ItemBatch
	 */
	public function setExpirationDate($expirationDate)
	{
		$this->expirationDate = $expirationDate;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getServiceCenter()
	{
		return $this->serviceCenter;
	}

	/**
	 * @param mixed $serviceCenter
	 * @return ItemBatch
	 */
	public function setServiceCenter($serviceCenter)
	{
		$this->serviceCenter = $serviceCenter;
		return $this;
	}

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


}