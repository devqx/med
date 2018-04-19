<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 10:46 AM
 */
class FluidRoute implements JsonSerializable
{
	private $id;
	private $type;
	private $name;

	/**
	 * FluidRoute constructor.
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
	 * @return FluidRoute
	 */
	public function setId($id)
	{
		$this->id = $id;
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
	 * @return FluidRoute
	 */
	public function setType($type)
	{
		$this->type = $type;
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
	 * @return FluidRoute
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function jsonSerialize()
	{
		return (object)get_object_vars($this);
	}

	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$type = quote_esc_str($this->getType());
			$sql = "INSERT INTO fluid_route (`name`, type) VALUES ($name, $type)";
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
	function update($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = ($pdo == null ? (new MyDBConnector())->getPDO() : $pdo);
			$name = quote_esc_str($this->getName());
			$type = quote_esc_str($this->getType());
			$sql = "UPDATE fluid_route SET `name`=$name, type=$type WHERE id={$this->getId()}";
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
}