<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/9/18
 * Time: 12:56 PM
 */

class EmbrayoStage implements JsonSerializable
{
	
	private $id;
	private $name;
	
	/**
	 * EmbrayoStage constructor.
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
	 * @return EmbrayoStage
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
	 * @return EmbrayoStage
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