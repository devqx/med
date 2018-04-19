<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/31/17
 * Time: 1:22 PM
 */

class SubstitutionCode implements JsonSerializable
{
	private $id;
	private $name;
	
	/**
	 * SubstitutionCode constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
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
	 * @return SubstitutionCode
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
	 * @return SubstitutionCode
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
	
	function add($pdo=null){
		//todo
	}
}