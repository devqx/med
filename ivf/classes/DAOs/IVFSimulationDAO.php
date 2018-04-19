<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/24/16
 * Time: 4:07 PM
 */
class IVFSimulationDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SimulationDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		$sql = "SELECT * FROM ivf_simulation_ WHERE id=$id";

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data = (new SimulationDataDAO())->getForSimulation($row['id'], $pdo);
				$total = array('left'=>$row['totals_left'], 'right'=>$row['totals_right']);
				return (new IVFSimulation($row['id']))->setEnrolment(new IVFEnrollment($row['enrolment_id']))->setRecordDate($row['record_date'])->setRecordedBy((new StaffDirectoryDAO())->getStaff($row['recorded_by_id'], FALSE, $pdo))->setDay($row['day'])->setEndo($row['endo'])->setE2Level($row['e2'])->setGnrha($row['gnrha'])->setAnt($row['ant'])->setFsh($row['fsh'])->setHmg($row['hmg'])->setRemarks($row['remarks'])->setData($data)->setTotal($total);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function getForEnrolment($enrolmentId, $pdo=null){
		$sql = "SELECT * FROM ivf_simulation WHERE enrolment_id=$enrolmentId";
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}

	function preventDayForEnrollment($day, $instanceId, $pdo=null){
		$sql = "SELECT * FROM ivf_simulation WHERE `day`=$day AND enrolment_id=$instanceId";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return true;
			}
			return false;
		}catch (PDOException $e){
			errorLog($e);
			return false;
		}
	}
}