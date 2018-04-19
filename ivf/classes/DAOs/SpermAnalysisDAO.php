<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/11/16
 * Time: 11:10 AM
 */
class SpermAnalysisDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SpermAnalysis.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	function get($id, $pdo=null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_sperm_analysis WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wits = array_filter(explode(',',$row['witness_ids']));
				$witnesses = [];
				foreach ($wits as $wit){
					$witnesses[] = (new StaffDirectoryDAO())->getStaff($wit, FALSE, $pdo);
				}
				return (new SpermAnalysis($row['id']))->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], false, $pdo) )->setTimeEntered($row['time_entered'])->setVolume($row['volume'])->setCellNo($row['cell_no'])->setDensity($row['density'])->setMotility($row['motility'])->setProg($row['prog'])->setAbnormal($row['abnormal'])->setMar($row['mar'])->setAggl($row['aggl'])->setComment($row['comment'])->setWitnesses($witnesses);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forInstance($id, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_sperm_analysis WHERE instance_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return $data;
		}
	}
}