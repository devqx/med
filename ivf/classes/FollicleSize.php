<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/10/16
 * Time: 11:20 AM
 */
class FollicleSize implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * FollicleSize constructor.
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
	 * @return FollicleSize
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
	 * @return FollicleSize
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$name = is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
		try {
			$sql = "INSERT INTO ivf_follicle_size (`name`) VALUES ($name)";
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
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
		$name = is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
		try {
			$sql = "UPDATE ivf_follicle_size SET `name` = $name WHERE id= {$this->getId()}";
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($stmt->rowCount()){
				return $this;
			}
			return null;

		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
}