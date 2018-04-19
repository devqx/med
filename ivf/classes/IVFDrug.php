<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/27/17
 * Time: 5:30 PM
 */

class IVFDrug implements JsonSerializable
{
	private $id;
	private $name;
	private $generic;
	
	/**
	 * IVFDrug constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
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
	 *
	 * @return IVFDrug
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
	 * @return IVFDrug
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getGeneric()
	{
		return $this->generic;
	}
	
	/**
	 * @param mixed $generic
	 *
	 * @return IVFDrug
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}
	
	
	
	function add($pdo=null){
		
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		
			$sql = "INSERT INTO ivf_drug (name, generic_id) VALUES ('". $this->getName() ."', '". $this->getGeneric() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}
	
	
	function update($pdo=null){
		@session_start();
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			
			$sql = "UPDATE ivf_drug set name='". $this->getName() ."', generic_id= '". $this->getGeneric() ."' WHERE id= '". $this->getId() ."'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	
}