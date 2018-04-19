<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/16
 * Time: 11:37 AM
 */
class Company implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * Company constructor.
	 * @param $id
	 */
	public function __construct($id = NULL) { $this->id = $id; }

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return Company
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
	 * @return Company
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

}