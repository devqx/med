<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/22/16
 * Time: 11:50 AM
 */
class SimulationSize implements JsonSerializable
{
	private $id;
	private $name;

	/**
	 * SimulationSize constructor.
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
	 * @return SimulationSize
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
	 * @return SimulationSize
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	function add($pdo=NULL) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$name = is_blank($this->getName()) ? quote_esc_str($this->getName()) : 'NULL';
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "INSERT INTO simulation_size (`name`) VALUES ($name)";
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