<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/7/18
 * Time: 1:24 AM
 */

class ZygoteType implements JsonSerializable
{
	private $id;
	private $name;
	private $fertilized;
	
	/**
	 * ZygoteType constructor.
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
	 * @return ZygoteType
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
	 * @return ZygoteType
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFertilized()
	{
		return $this->fertilized;
	}
	
	/**
	 * @param mixed $fertilized
	 *
	 * @return ZygoteType
	 */
	public function setFertilized($fertilized)
	{
		$this->fertilized = $fertilized;
		return $this;
	}
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	public function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try{
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}
	
}