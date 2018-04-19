<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 11:41 AM
 */
class SampleState implements JsonSerializable
{
private $id;
private $name;

	/**
	 * SampleState constructor.
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
	 * @return SampleState
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
	 * @return SampleState
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$name = quote_esc_str($this->getName());
			$sql = "INSERT INTO ivf_sample_state (`name`) VALUES ($name)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()){
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
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
		try {
			$name = quote_esc_str($this->getName());
			$sql = "UPDATE ivf_sample_state SET `name`=$name WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()){
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