<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/11/16
 * Time: 4:35 PM
 */
class InseminationDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/Insemination.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/IVFMethod.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SampleSource.php';

			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFMethodDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleSourceDAO.php';

			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_insemination WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wits = array_filter(explode(',',$row['witness_ids']));
				$witnesses = [];
				foreach ($wits as $wit){
					$witnesses[] = (new StaffDirectoryDAO())->getStaff($wit, FALSE, $pdo);
				}
				return (new Insemination($row['id']))->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], false, $pdo) )->setTimeEntered($row['time_entered'])->setMethod((new IVFMethodDAO())->get($row['method_id'], $pdo))->setSource((new SampleSourceDAO())->get( $row['source_id'], $pdo))->setTimeEntered($row['time_entered'])->setTotalEggs($row['total_eggs'])->setTotalSperm($row['total_sperm'])->setComment($row['comment'])->setWitnesses($witnesses);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function forInstance($instanceId, $pdo=null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_insemination WHERE instance_id=$instanceId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}