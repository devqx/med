<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientCareMemberDAO
 *
 * @author pauldic
 */
class PatientCareMemberDAO
{

	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientCareMember.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CareTeam.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addPatientCareMember($pcts, $pdo = null)
	{
		try {
			$counter = 0;
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;

			foreach ($pcts as $pct) {
				$extra = ($pct->getType() === "Member") ? "care_member_id=" . $pct->getCareMember()->getId() . ", care_team_id=NULL" : "care_member_id=NULL, care_team_id=" . $pct->getCareTeam()->getId();
				$extra2 = !is_null($pct->getPrimaryCare()) ? ", primary_care_id=" . $pct->getPrimaryCare()->getId() . ", primary_care_type='" . $pct->getPrimaryCareType() . "'" : "";
				$sql = "INSERT INTO patient_care_member SET in_patient_id = " . $pcts[0]->getInPatient()->getId() . ", " . $extra . ", created_by=" . $pct->getCreateBy()->getId() . ", type='" . $pct->getType() . "'$extra2";

				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				$counter++;
			}
			if (count($pcts) !== $counter) {
				$pcts = [];
			}
			// $stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			// $stmt = null;
			$pcts = [];
		} catch (Exception $e) {
			// $stmt = null;
			$pcts = [];
			errorLog($e);
		}

		return $pcts;
	}

	function getPatientCareMember($id, $getFull = FALSE, $pdo = null)
	{
		$pct = new PatientCareMember();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_care_member WHERE id = " . $id;
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$createdBy = (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo);
//                    $changedBy = (new StaffDirectoryDAO())->getStaff($row['changed_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient($row['in_patient_id']);
					$createdBy = new StaffDirectory($row['created_by']);
//                    $changedBy = new StaffDirectory($row['changed_by']);
				}
				$pct->setId($row["id"]);
				$pct->setInPatient($ip);
				$pct->setCareTeam($row['type'] === "Team" ? (new CareTeamDAO())->getCareTeam($row['care_team_id'], FALSE, $pdo) : null);
				$pct->setCareMember($row['type'] === "Member" ? (new StaffDirectoryDAO())->getStaff($row['care_member_id'], FALSE, $pdo) : null);
				$pct->setCreateBy($createdBy);
				$pct->setEntryTime($row["entry_time"]);
//                $pct->setChangedBy($changedBy);
//                $pct->setChangeReason($row['change_reason']);
//                $pct->setChangeTime($row['change_time']);
				$pct->setStatus($row['status']);
				$pct->setType($row['type']);
				$pct->setPrimaryCareType($row['primary_care_type']);
				$pct->setPrimaryCare($row['primary_care_type'] === "Team" ? (new CareTeamDAO())->getCareTeamsByIds($row['primary_care_id'], FALSE, $pdo) : (new StaffDirectoryDAO())->getStaffMinByIds($row['primary_care_id'], FALSE, $pdo));
			} else {
				$pct = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pct = $stmt = null;
		}
		return $pct;
	}

	function getPatientCareMembersByInPatient($ipid, $getFull = FALSE, $pdo = null)
	{
		error_log("Entered here::::::: ". $ipid);
		$pcts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_care_member WHERE in_patient_id = $ipid AND status='Active' ORDER BY entry_time";
			error_log(">>>>".$sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pct = new PatientCareMember();
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$createdBy = (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo);
//                    $changedBy = (new StaffDirectoryDAO())->getStaff($row['changed_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient($row['in_patient_id']);
					$createdBy = new StaffDirectory($row['created_by']);
//                    $changedBy = new StaffDirectory($row['changed_by']);
				}
				$pct->setId($row["id"]);
				$pct->setInPatient($ip);
				$pct->setCareTeam($row['type'] === "Team" ? (new CareTeamDAO())->getCareTeam($row['care_team_id'], FALSE, $pdo) : null);
				$pct->setCareMember($row['type'] === "Member" ? (new StaffDirectoryDAO())->getStaff($row['care_member_id'], FALSE, $pdo) : null);
				$pct->setCreateBy($createdBy);
				$pct->setEntryTime($row["entry_time"]);
//                $pct->setChangedBy($changedBy);
//                $pct->setChangeReason($row['change_reason']);
//                $pct->setChangeTime($row['change_time']);
				$pct->setStatus($row['status']);
				$pct->setType($row['type']);
				$pct->setPrimaryCareType($row['primary_care_type']);
				$pct->setPrimaryCare($row['primary_care_type'] === "Team" ? (new CareTeamDAO())->getCareTeamsByIds($row['primary_care_id'], FALSE, $pdo) : (new StaffDirectoryDAO())->getStaffMinByIds($row['primary_care_id'], FALSE, $pdo));

				$pcts[] = $pct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pcts = [];
		}
		return $pcts;
	}

	function getPatientCareMembers($getFull = FALSE, $pdo = null)
	{
		$pcts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_care_member ORDER BY entry_time";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pct = new PatientCareMember();
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$createdBy = (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo);
//                    $changedBy = (new StaffDirectoryDAO())->getStaff($row['changed_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient($row['in_patient_id']);
					$createdBy = new StaffDirectory($row['created_by']);
//                    $changedBy = new StaffDirectory($row['changed_by']);
				}
				$pct->setId($row["id"]);
				$pct->setInPatient($ip);
				$pct->setCareTeam($row['type'] === "Team" ? (new CareTeamDAO())->getCareTeam($row['care_team_id'], FALSE, $pdo) : null);
				$pct->setCareMember($row['type'] === "Member" ? (new StaffDirectoryDAO())->getStaff($row['care_member_id'], FALSE, $pdo) : null);
				$pct->setCreateBy($createdBy);
				$pct->setEntryTime($row["entry_time"]);
//                $pct->setChangedBy($changedBy);
//                $pct->setChangeReason($row['change_reason']);
//                $pct->setChangeTime($row['change_time']);
				$pct->setStatus($row['status']);
				$pct->setType($row['type']);
				$pct->setPrimaryCareType($row['primary_care_type']);
				$pct->setPrimaryCare($row['primary_care_type'] === "Team" ? (new CareTeamDAO())->getCareTeamsByIds($row['primary_care_id'], FALSE, $pdo) : (new StaffDirectoryDAO())->getStaffMinByIds($row['primary_care_id'], FALSE, $pdo));

				$pcts[] = $pct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pcts = [];
		}
		return $pcts;
	}

	function getStaffPatientCareMembers($sid, $getFull = FALSE, $pdo = null)
	{
		$pcts = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT t.*, s.staffId FROM patient_care_member t LEFT JOIN staff_care_team st ON st.team_id = t.id  AND st.staff_id = '$sid'  LEFT JOIN staff_directory s ON s.staffId = st.staff_id";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pct = new PatientCareMember();
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$createdBy = (new StaffDirectoryDAO())->getStaff($row['created_by'], FALSE, $pdo);
//                    $changedBy = (new StaffDirectoryDAO())->getStaff($row['changed_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient($row['in_patient_id']);
					$createdBy = new StaffDirectory($row['created_by']);
//                    $changedBy = new StaffDirectory($row['changed_by']);
				}
				$pct->setId($row["id"]);
				$pct->setInPatient($ip);
				$pct->setCareTeam($row['type'] === "Team" ? (new CareTeamDAO())->getCareTeam($row['care_team_id'], FALSE, $pdo) : null);
				$pct->setCareMember($row['type'] === "Member" ? (new StaffDirectoryDAO())->getStaff($row['care_member_id'], FALSE, $pdo) : null);
				$pct->setCreateBy($createdBy);
				$pct->setEntryTime($row["entry_time"]);
//                $pct->setChangedBy($changedBy);
//                $pct->setChangeReason($row['change_reason']);
//                $pct->setChangeTime($row['change_time']);
				$pct->setStatus($row['status']);
				$pct->setType($row['type']);
				$pct->setPrimaryCareType($row['primary_care_type']);
				$pct->setPrimaryCare($row['primary_care_type'] === "Team" ? (new CareTeamDAO())->getCareTeamsByIds($row['primary_care_id'], FALSE, $pdo) : (new StaffDirectoryDAO())->getStaffMinByIds($row['primary_care_id'], FALSE, $pdo));

				$pcts[] = $pct;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pcts = [];
		}
		return $pcts;
	}

	function changePatientCareMember($pcms, $pdo = null)
	{
		$status = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			try {
				$pdo->beginTransaction();
			} catch (PDOException $ex) {
				//Transaction is already started
			}
//            $sql = "UPDATE patient_care_member SET changed_by = '" . $ct->getChangedBy()->getId() . "', change_reason='" . $ct->getChangeReason() . "', change_time=NOW(), status='" . $ct->getStatus() . "' WHERE id = " . $ct->getId();
			$sql = "UPDATE patient_care_member SET status='Cancelled' WHERE in_patient_id = " . $pcms[0]->getInPatient()->getId();
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$cms = [];
			foreach ($pcms as $ms) {
				if ($ms->getId() !== null && strlen($ms->getId()) > 0) {//Update the existing once
					$sql = "UPDATE patient_care_member SET status='Active' WHERE id = " . $ms->getId();
//                    error_log($sql);
					$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$stmt->execute();
				} else {//Prepare the new once for insert
					$cms[] = $ms;
				}
			}

			if (count($cms) > 0) {
				if ($this->addPatientCareMember($cms, $pdo) === null) {
					$pdo->rollBack();
				} else {
					$pdo->commit();
				}
			} else {
				$pdo->commit();
			}
			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pdo->rollBack();
		}
		return $status;
	}
}
