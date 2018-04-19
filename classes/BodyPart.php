<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/19/16
 * Time: 10:28 PM
 */
class BodyPart implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * BodyPart constructor.
	 * @param $id
	 */
	public function __construct($id=null)
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
	 * @return BodyPart
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
	 * @return BodyPart
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	function add($pdo=null)
	{
		
	}

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


}