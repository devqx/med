<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/18/16
 * Time: 9:52 AM
 */
class DrugSuperGenericData implements JsonSerializable
{

	private $id;
	private $superGeneric;
	private $drugGeneric;

	/**
	 * DrugSuperGenericData constructor.
	 * @param $id
	 */
	public function __construct($id = null)
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
	 * @return DrugSuperGenericData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSuperGeneric()
	{
		return $this->superGeneric;
	}

	/**
	 * @param mixed $superGeneric
	 * @return DrugSuperGenericData
	 */
	public function setSuperGeneric($superGeneric)
	{
		$this->superGeneric = $superGeneric;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getDrugGeneric()
	{
		return $this->drugGeneric;
	}

	/**
	 * @param mixed $drugGeneric
	 * @return DrugSuperGenericData
	 */
	public function setDrugGeneric($drugGeneric)
	{
		$this->drugGeneric = $drugGeneric;
		return $this;
	}


	

	function jsonSerialize()
	{
		// TODO: Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}


	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;

		$super_generic = ($this->getSuperGeneric()) ? $this->getSuperGeneric()->getId() : 'null';
		$generic = $this->getDrugGeneric() ? $this->getDrugGeneric()->getId() : 'null';

		$sql = "INSERT IGNORE INTO drug_super_generic_data(super_generic_id, drug_generic_id) VALUES ($super_generic, $generic)";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}

	}
	

	function delete($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;

		$sql = "DELETE FROM drug_super_generic_data WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return true;
			}
			return false;
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}
	}
	
}