<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/15/15
 * Time: 11:26 AM
 */
class LabourEnrollmentDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabourEnrollment.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	
	function isEnrolled($patient, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT id FROM enrollments_labour WHERE patient_id = $patient AND active IS TRUE";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return TRUE;
			}
			return FALSE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}

	function get($instanceId, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_labour WHERE id = $instanceId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$in = (new LabourEnrollment($row['id']))->setEnrolledOn($row['enrolled_on'])->setActive($row['active'])->setEnrolledAt((new ClinicDAO())->getClinic($row['enrolled_at'], FALSE, $pdo))->setEnrolledBy((new StaffDirectoryDAO())->getStaff($row['enrolled_by'], FALSE, $pdo))->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))->setLmpDate($row['lmpDate'])->setBabyFatherName($row['baby_father_name'])->setBabyFatherPhone($row['baby_father_phone'])->setBabyFatherBloodGroup($row['baby_father_blood_group'])->setGravida($row['gravida'])->setPara($row['para'])->setAlive($row['alive'])->setAbortions($row['abortions'])->setCurrentPregnancy($row['current_pregnancy'])->setDateClosed($row['date_closed']);
				return $in;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function allActive($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM enrollments_labour WHERE active IS TRUE";
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