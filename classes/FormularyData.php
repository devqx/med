<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 12:59 PM
 */
class FormularyData implements JsonSerializable
{
	private $id;
	private $formulary;
	private $generic;
	
	/**
	 * FormularyData constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	function jsonSerialize()
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
	 * @return FormularyData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getFormulary()
	{
		return $this->formulary;
	}
	
	/**
	 * @param mixed $formulary
	 *
	 * @return FormularyData
	 */
	public function setFormulary($formulary)
	{
		$this->formulary = $formulary;
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
	 * @return FormularyData
	 */
	public function setGeneric($generic)
	{
		$this->generic = $generic;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		$formulary = ($this->getFormulary()) ? $this->getFormulary()->getId() : 'null';
		$generic = $this->getGeneric() ? $this->getGeneric()->getId() : 'null';
		
		$sql = "INSERT IGNORE INTO drug_formulary_data (drug_formulary_id, generic_id) VALUES ($formulary, $generic)";
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
	
	function clear($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		$formulary = ($this->getFormulary()) ? $this->getFormulary()->getId() : 'null';
		
		$sql = "DELETE FROM drug_formulary_data WHERE drug_formulary_id = " .$formulary;
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount() >= 0){
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}
	
}