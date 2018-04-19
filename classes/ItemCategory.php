<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/3/17
 * Time: 10:57 AM
 */
class ItemCategory implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * ItemCategory constructor.
	 * @param $id
	 */
	 function __construct($id = Null)
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
	 * @return ItemCategory
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
	 * @return ItemCategory
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	
	function jsonSerialize()
	{
		return (object)get_object_vars($this);
		// TODO: Implement jsonSerialize() method.
	}



}