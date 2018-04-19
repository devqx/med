<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/21/16
 * Time: 9:44 AM
 */
class AptClinic implements JsonSerializable
{
	private $id;
	private $name;
	private $a_limit;
	private $queue_type;

	/**
	 * AptClinic constructor.
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
	 * @return AptClinic
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
	 * @return AptClinic
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getALimit()
	{
		return $this->a_limit;
	}

	/**
	 * @param mixed $a_limit
	 * @return AptClinic
	 */
	public function setALimit($a_limit)
	{
		$this->a_limit = $a_limit;
		return $this;
	}

	function jsonSerialize()
	{
		// Implement jsonSerialize() method.
		return (object)get_object_vars($this);
	}
	
	/**
	 * @return mixed
	 */
	public function getQueueType()
	{
		return $this->queue_type;
	}
	
	/**
	 * @param mixed $queue_type
	 *
	 * @return AptClinic
	 */
	public function setQueueType($queue_type)
	{
		$this->queue_type = $queue_type;
		return $this;
	}

	function add($pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$aLimit = $this->getALimit() ? $this->getALimit() : 0;
			$queueType = quote_esc_str($this->getQueueType());
			$sql = "INSERT INTO appointment_clinic (`name`, a_limit, queue_type) VALUES ($name, $aLimit, $queueType)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
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
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$name = quote_esc_str($this->getName());
			$aLimit = $this->getALimit() ? $this->getALimit() : 0;
			$queueType = quote_esc_str($this->getQueueType());
			$sql = "UPDATE appointment_clinic SET `name`=$name, a_limit=$aLimit, queue_type=$queueType WHERE id={$this->getId()}";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$this->setId($pdo->lastInsertId());
				return $this;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}