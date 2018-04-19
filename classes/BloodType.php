<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/30/17
 * Time: 10:36 AM
 */

class BloodType
{
	private $id;
	private $name;
	
	/**
	 * BloodGroup constructor.
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
	 * @return BloodType
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
	 * @return BloodType
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function add(){
		//todo
	}
}