<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/8/16
 * Time: 10:02 PM
 */
class SuperGeneric implements JsonSerializable
{
	private $id;
	private $name;
    private $data;
	private $createdBy;
	private $lastModifiedBy;

	/**
	 * SuperGeneric constructor.
	 * @param $id
	 */
	public function __construct($id=null)
	{
		$this->id = $id;
	}


	/**
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 * @return SuperGeneric
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
	 * @return SuperGeneric
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
	 * @return SuperGeneric
	 */
	public function setData($data)
	{
		$this->data = $data;
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

		$inTransaction = $pdo->inTransaction();
		if(!$inTransaction){
			$pdo->beginTransaction();
		}

		$name = !is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'null';
		$sql = "INSERT INTO drug_super_generic (`name`) VALUES ($name)";
		//error_log($sql);
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()==1){
				$this->setId($pdo->lastInsertId());

				foreach ($this->getData() as $data){
					//$data = new FormularyData();
					$data->setSuperGeneric($this)->add($pdo);
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
		$sql = "UPDATE drug_super_generic SET `name` = ($name) WHERE id={$this->getId()}";
		try {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()>=0){
				foreach ($this->getData() as $data){
					$data->setSuperGeneric($this)->add($pdo);
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