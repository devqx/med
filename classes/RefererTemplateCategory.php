<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 12/21/16
 * Time: 9:50 AM
 */
class RefererTemplateCategory implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * RefererTemplateCategory constructor.
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
	 * @return RefererTemplateCategory
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
	 * @return RefererTemplateCategory
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);

	}


}