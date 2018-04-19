<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 12:27 PM
 */
class Formulary implements JsonSerializable
{
	private $id;
	private $name;
	private $data;
	
	/**
	 * Formulary constructor.
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
	 * @return Formulary
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
	 * @return Formulary
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}
	
	/**
	 * @param mixed $data
	 *
	 * @return Formulary
	 */
	public function setData($data)
	{
		$this->data = $data;
		return $this;
	}
	
	function add($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		$inTransaction = $pdo->inTransaction();
		if(!$inTransaction){
			$pdo->beginTransaction();
		}
		
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		$sql = "INSERT INTO drug_formulary (`name`) VALUES ($name)";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$this->setId($pdo->lastInsertId());
				
				foreach ($this->getData() as $data){
					//$data = new FormularyData();
					$data->setFormulary($this)->add($pdo);
				}
				if(!$inTransaction){
					$pdo->commit();
				}
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}
	function update($pdo=null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$inTransaction = $pdo->inTransaction();
		if(!$inTransaction){
			$pdo->beginTransaction();
		}
		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		$sql = "UPDATE drug_formulary SET `name` = ($name) WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=0){
				foreach ($this->getData() as $data){
					$data->setFormulary($this)->add($pdo);
					//fixme: when old items are removed nko?
				}
				if(!$inTransaction){
					$pdo->commit();
				}
				return $this;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
	}
	
	
}