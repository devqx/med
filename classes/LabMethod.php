<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/18/17
 * Time: 11:36 AM
 */

class LabMethod implements JsonSerializable
{
	private $id;
	private $name;
	private $type;
	
	/**
	 * LabMethod constructor.
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
	 * @return LabMethod
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
	 * @return LabMethod
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * @param mixed $type
	 *
	 * @return LabMethod
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}
	
	
	public function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$type = quote_esc_str($this->getType());
			$sql = "INSERT INTO lab_method (`name`, `type`) VALUES ($name, $type)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$type = quote_esc_str($this->getType());
			$sql = "UPDATE lab_method SET `name`=$name, `type`=$type WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}