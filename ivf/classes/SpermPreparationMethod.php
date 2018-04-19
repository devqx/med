<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/18
 * Time: 9:42 AM
 */

class SpermPreparationMethod implements JsonSerializable
{
	private $id;
	private $name;
	
	/**
	 * SpermPreparationMethod constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return SpermPreparationMethod
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
	 * @return SpermPreparationMethod
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
}