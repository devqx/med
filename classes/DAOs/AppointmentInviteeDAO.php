<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppointmentInviteeDAO
 *
 * @author pauldic
 */
class AppointmentInviteeDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentInvitee.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($ais, $pdo)
	{
		try {
			foreach ($ais as $i => $ai) {
				$sql = "INSERT INTO appointment_invitee (group_id, staff_id) VALUES (" . $ais[0]->getGroup()->getId() . ", '" . $ai->getStaff()->getId() . "')";
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
			}
			if ($stmt->rowCount() === 0) {
				$ais = [];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ais = [];
		}
		return $ais;
	}

	function getAppointmentInvitee($aiid, $getFull = FALSE, $pdo = null)
	{
		$invitee = new AppointmentInvitee();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_invitee WHERE id=" . $aiid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invitee->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['staff_id'], FALSE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['staff_id']);
				}
				$invitee->setGroup($group);
				$invitee->setStaff($staff);
			} else {
				$invitee = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = $invitee = null;
		}
		return $invitee;
	}

	function getAppointmentInvitees($getFull = FALSE, $pdo = null)
	{
		$invitees = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_invitee";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invitee = new AppointmentInvitee();
				$invitee->setId($row['id']);
				if ($getFull) {
					$group = (new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['staff_id'], FALSE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['staff_id']);
				}
				$invitee->setGroup($group);
				$invitee->setStaff($staff);
				$invitees[] = $invitee;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$invitees = array();
		}
		return $invitees;
	}

	function getAppointmentInviteesByGroup($gid, $getFull = FALSE, $pdo = null)
	{
		$invitees = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
//            $sql = "SELECT GROUP_CONCAT(id) as ids, group_id, GROUP_CONCAT(staff_id) as staffs FROM appointment_invitee WHERE group_id=" . $gid;
			$sql = "SELECT * FROM appointment_invitee WHERE group_id=" . $gid;
//            error_log("-----------: ".$sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invitee = new AppointmentInvitee();
				$invitee->setId($row['id']);
				if ($getFull) {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
//                        $group=(new AppointmentGroupDAO())->getAppointmentGroup($row['group_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['staff_id'], FALSE, $pdo);
				} else {
					$group = new AppointmentGroup();
					$group->setId($row['group_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['staff_id']);
				}
				$invitee->setGroup($group);
				$invitee->setStaff($staff);
				if ($staff !== null) {
					$invitees[] = $invitee;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = $pdo = null;
			$invitees = array();
		}
		return $invitees;
	}

}
