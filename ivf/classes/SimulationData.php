<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/24/16
 * Time: 1:35 PM
 */
class SimulationData implements JsonSerializable
{
	private $id;
	private $simulation;
	private $rightSide;
	private $leftSide;
	private $size;

	/**
	 * SimulationData constructor.
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
	 * @return SimulationData
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSimulation()
	{
		return $this->simulation;
	}

	/**
	 * @param mixed $simulation
	 * @return SimulationData
	 */
	public function setSimulation($simulation)
	{
		$this->simulation = $simulation;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getRightSide()
	{
		return $this->rightSide;
	}

	/**
	 * @param mixed $rightSide
	 * @return SimulationData
	 */
	public function setRightSide($rightSide)
	{
		$this->rightSide = $rightSide;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getLeftSide()
	{
		return $this->leftSide;
	}

	/**
	 * @param mixed $leftSide
	 * @return SimulationData
	 */
	public function setLeftSide($leftSide)
	{
		$this->leftSide = $leftSide;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @param mixed $size
	 * @return SimulationData
	 */
	public function setSize($size)
	{
		$this->size = $size;
		return $this;
	}

	/**
	 * @param null $pdo
	 * @return SimulationData
	 */
	function add($pdo=null){
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		try {
			$pdo = $pdo===null ? (new MyDBConnector())->getPDO() : $pdo;
			$ivf_simulation_id = $this->getSimulation() ? $this->getSimulation()->getId() : 'NULL';
			$right_side  = !is_blank($this->getRightSide()) ? quote_esc_str($this->getRightSide()) : 'NULL';
			$left_side  = !is_blank($this->getLeftSide()) ? quote_esc_str($this->getLeftSide()) : 'NULL';
			$size_index_id = $this->getSize() ? $this->getSize()->getId() : 'NULL';
			$sql = "INSERT INTO ivf_simulation_data (ivf_simulation_id, right_side, left_side, size_index_id) VALUES ($ivf_simulation_id, $right_side, $left_side, $size_index_id)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
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