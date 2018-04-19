<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/1/16
 * Time: 9:05 AM
 */
class EyeReview implements JsonSerializable
{
  private $id;
	private $name;
  private $category;

	/**
	 * @return null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param null $id
	 * @return EyeReview
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
	 * @return EyeReview
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCategory()
	{
		return $this->category;
	}

	/**
	 * @param mixed $category
	 * @return EyeReview
;	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}


	function __construct($id=NULL)
	{
		$this->id = $id;
	}


	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}

}