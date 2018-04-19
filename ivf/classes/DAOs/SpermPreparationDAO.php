<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/10/16
 * Time: 4:03 PM
 */
class SpermPreparationDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/SpermPreparation.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SampleStateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SpermProcedureDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_sperm_collection WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$wits = array_filter(explode(',',$row['witness_ids']));
				$witnesses = [];
				foreach ($wits as $wit){
					$witnesses[] = (new StaffDirectoryDAO())->getStaff($wit, FALSE, $pdo);
				}
				return (new SpermPreparation($row['id']))->setTimeEntered($row['time_entered'])->setUser( (new StaffDirectoryDAO())->getStaff($row['user_id'], FALSE, $pdo))->setSource( (new SampleSourceDAO())->get($row['source_id'], $pdo) )->setState( (new SampleStateDAO())->get($row['state_id'], $pdo))->setDonorCode($row['donor_code'])->setProcedure((new SpermProcedureDAO())->get($row['procedure_id'], $pdo))->setAbstinenceDays($row['abstinence_days'])->setCollectionDate($row['collection_date'])->setWitnesses($witnesses)->setPostAnalysisReport($row['analysis_post_report'])->setPreAnalysisReport($row['analysis_pre_report'])->setProductionTime($row['production_time'])->setAnalysisTime($row['analysis_time'])->setPreparationMethod($row['preparation_method']);
			}
			return null;
		} catch (PDOException $exception){
			errorLog($exception);
			return null;
		}
	}

	function forInstance($instanceId, $pdo=null){
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_sperm_collection WHERE instance_id=$instanceId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $exception){
			errorLog($exception);
			return [];
		}
	}
}