<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/24/16
 * Time: 5:05 PM
 */
class SimulationDataDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFSimulation.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SimulationSize.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SimulationData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFSimulationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SimulationSizeDAO.php';
			$this->conn = new MyDBConnector();

		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		$sql = "SELECT * FROM ivf_simulation_data WHERE id=$id";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new SimulationData($row['id']))->setSimulation(new IVFSimulation($row['ivf_simulation_id']))->setRightSide($row['right_side'])->setLeftSide($row['left_side'])->setSize( new SimulationSize($row['size_index_id']) );
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function getForSimulation($simulationId, $pdo=null){
		$sql = "SELECT * FROM ivf_simulation_data WHERE ivf_simulation_id=$simulationId";
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new SimulationData($row['id']))->setSimulation(new IVFSimulation($row['ivf_simulation_id']))->setRightSide($row['right_side'])->setLeftSide($row['left_side'])->setSize( new SimulationSize($row['size_index_id']) );
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}