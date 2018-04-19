<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/3/15
 * Time: 4:25 PM
 */
class ServiceCenter implements JsonSerializable
{
	private $id;
	private $erpLocation;
	private $name;
	private $department;
	private $costCentre;
	private $type;

	/**
	 * ServiceCenter constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
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
	 * @return ServiceCenter
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getErpLocation()
	{
		return $this->erpLocation;
	}

	/**
	 * @param mixed $erpLocation
	 * @return ServiceCenter
	 */
	public function setErpLocation($erpLocation)
	{
		$this->erpLocation = $erpLocation;
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
	 * @return ServiceCenter
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDepartment()
	{
		return $this->department;
	}

	/**
	 * @param mixed $department
	 * @return ServiceCenter
	 */
	public function setDepartment($department)
	{
		$this->department = $department;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCostCentre()
	{
		return $this->costCentre;
	}

	/**
	 * @param mixed $costCentre
	 * @return ServiceCenter
	 */
	public function setCostCentre($costCentre)
	{
		$this->costCentre = $costCentre;
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
	 * @return ServiceCenter
	 */
	public function setType($type)
	{
		$this->type = $type;
		return $this;
	}


	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null? (new MyDBConnector())->getPDO() : $pdo;
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : "NULL";
			$department = !is_blank($this->getDepartment()) ? $this->getDepartment()->getId() : "NULL";
			$costCentre = !is_blank($this->getCostCentre()) ? $this->getCostCentre()->getId() : "NULL";
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : "NULL";
			$sql = "INSERT INTO service_centre SET `name` = $name, department_id=$department, cost_centre_id=$costCentre, type=$type";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}

	}

	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo==null? (new MyDBConnector())->getPDO() : $pdo;
			$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : "NULL";
			$department = !is_blank($this->getDepartment()) ? $this->getDepartment()->getId() : "NULL";
			$costCentre = !is_blank($this->getCostCentre()) ? $this->getCostCentre()->getId() : "NULL";
			$type = !is_blank($this->getType()) ? quote_esc_str($this->getType()) : "NULL";
			$sql = "UPDATE service_centre SET `name` = $name, department_id=$department, cost_centre_id=$costCentre, type=$type WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() == 1){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}

	}


}