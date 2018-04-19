<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/10/16
 * Time: 6:16 PM
 */
class Industry implements JsonSerializable
{
	private $id;
	private $name;
	
	/**
	 * Industry constructor.
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
	 * @return Industry
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
	 * @return Industry
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	
}