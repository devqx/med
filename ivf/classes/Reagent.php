<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:23 AM
 */
class Reagent implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * Reagent constructor.
	 * @param $id
	 */
	public function __construct($id = null)
	{
		$this->id = $id;
	}

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
	 * @return Reagent
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
	 * @return Reagent
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function update($pdo = null)
	{
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = escape($this->getName());
			$sql = "UPDATE genetic_reagent  SET `name` = '{$name}' WHERE id = {$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}