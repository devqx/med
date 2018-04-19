<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/8/16
 * Time: 5:23 PM
 */
class AllergenCategory implements JsonSerializable
{

	private $id;
	private $name;
	private $createdBy;

	/**
	 * AllergenCategory constructor.
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
	 * @return AllergenCategory
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
	 * @return AllergenCategory
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCreatedBy()
	{
		return $this->createdBy;
	}

	/**
	 * @param mixed $createdBy
	 * @return AllergenCategory
	 */
	public function setCreatedBy($createdBy)
	{
		$this->createdBy = $createdBy;
		return $this;
	}

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


}