<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExamRoomDAO
 *
 * @author pauldic
 */
class ExamRoomDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ExamRoom.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
			@session_start();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getExamRoom($rid, $pdo = null)
	{
		$sd = new ExamRoom();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM exam_rooms WHERE room_id='" . $rid . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sd->setId($row['room_id']);
				$sd->setName($row['room_name']);
				$sd->setAvailable(($row['available']));
				$sd->setSpecialization((new StaffSpecializationDAO())->get($row['specialization_id']));
				$sd->setConsultant((new StaffDirectoryDAO())->getStaff($row['consultant_id']));
			}
			$stmt = null;
		} catch (PDOException $e) {
			$sd = null;
		}
		return $sd;
	}
	
	function getExamRooms($pdo = null)
	{
		$sds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM exam_rooms";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sds[] = $this->getExamRoom($row['room_id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$sds = [];
		}
		return $sds;
	}
	
	function getAvailableExamRooms($pdo = null)
	{
		$sds = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM exam_rooms WHERE available IS TRUE";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$sds[] = $this->getExamRoom($row['room_id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$sds = [];
		}
		return $sds;
	}
	
	function setAvailable($rid, $available, $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE exam_rooms SET available=" . $available . " WHERE room_id=" . $rid . "";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			$status = false;
		}
		return $status;
	}
	
	
	function deleteExamRoom($rid, $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM exam_rooms WHERE room_id=" . $rid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			$status = false;
		}
		return $status;
	}
	
}
