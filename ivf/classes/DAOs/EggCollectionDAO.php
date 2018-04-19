<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/6/16
 * Time: 1:43 PM
 */
class EggCollectionDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/EggCollection.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EggCollectionMethodDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EggCollectionFollicleDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_egg_collection WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wits = array_filter(explode(',',$row['witness_ids']));
				$witnesses = [];
				foreach ($wits as $wit){
					$witnesses[] = (new StaffDirectoryDAO())->getStaff($wit, FALSE, $pdo);
				}
				$data = (new EggCollectionFollicleDataDAO())->forInstance($row['id'], $pdo);
				return (new EggCollection($row['id']))->setTimeEntered($row['time_entered'])->setUser((new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo))->setCollectionTime($row['collection_time'])->setMethod( (new EggCollectionMethodDAO())->get($row['method_id'], $pdo) )->setDoneBy((new StaffDirectoryDAO())->getStaff($row['done_by_id'], FALSE, $pdo))->setData($data)->setTotalLeft($row['total_left'])->setTotalRight($row['total_right'])->setWitnesses($witnesses)->setComment($row['comment']);
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
			$sql = "SELECT * FROM ivf_egg_collection WHERE instance_id=$id";
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