<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/11/17
 * Time: 2:04 PM
 */
class ProcedureSpecialty implements JsonSerializable
{
	private $id;
	private $name;
	
	/**
	 * ProcedureSpecialty constructor.
	 *
	 * @param $id
	 */
	public function __construct($id = null) { $this->id = $id; }
	
	
	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * @param mixed $id
	 *
	 * @return ProcedureSpecialty
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
	 * @return ProcedureSpecialty
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
	
	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$name = quote_esc_str($this->getName());
			$sql = "INSERT INTO procedure_specialty (`name`) VALUES ($name)";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function update($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$name = quote_esc_str($this->getName());
			$sql = "UPDATE procedure_specialty SET `name`=$name WHERE id={$this->getId()}";
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}