<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/12/16
 * Time: 4:58 PM
 */
class FertilizationDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/Fertilization.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFMethodDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	function get($id, $pdo=null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_fertilization WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wits = array_filter(explode(',',$row['witness_ids']));
				$witnesses = [];
				foreach ($wits as $wit){
					$witnesses[] = (new StaffDirectoryDAO())->getStaff($wit, FALSE, $pdo);
				}
				return (new Fertilization($row['id']))->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], false, $pdo) )->setTimeEntered($row['time_entered'])->setMethod((new IVFMethodDAO())->get($row['method_id'], $pdo))->setZygoteType($row['zygote_type'])->setCellNo($row['cell_no'])->setWitnesses($witnesses)->setComment($row['comment']);
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
			$sql = "SELECT * FROM ivf_fertilization WHERE instance_id=$id";
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