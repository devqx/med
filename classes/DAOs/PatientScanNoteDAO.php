<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/15/14
 * Time: 11:49 AM
 */
class PatientScanNoteDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Scan.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientScanNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getNote($id, $pdo = null)
	{
		$note = new PatientScanNote();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_notes WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$note->setId($row['id']);
				$note->setPatientScan((new PatientScanDAO())->getScan($row['patient_scan_id'], $pdo));
				$note->setDateAdded($row['create_date']);
				$note->setIsComment((bool)$row['is_comment']);
				$note->setNote($row['note']);
				$note->setArea($row['note_area']);
			} else {
				$note = null;
				$stmt = null;
			}
			
		} catch (PDOException $e) {
			$note = null;
		}
		return $note;
	}
	
	function getNotes($pdo = null)
	{
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_notes";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$notes[] = $this->getNote($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			$notes = array();
		}
		return $notes;
	}
	
	function getScanNotes($scanId, $pdo = null)
	{
		$notes = [];
		$notes['comments'] = [];
		$notes['reports'] = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_scan_notes WHERE patient_scan_id = '$scanId'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$note = new PatientScanNote();
				$note->setId($row['id']);
				//$note->setPatientScan( (new PatientScanDAO())->getScan($row['patient_scan_id'], $pdo) );
				$note->setNote($row['note']);
				$note->setDateAdded($row['create_date']);
				$note->setCreator((new StaffDirectoryDAO())->getStaff($row['create_uid'], false, $pdo));
				$note->setArea($row['note_area']);
				$note->setIsComment($row['is_comment']);
				if((bool)$row['is_comment']){
					$notes['comments'][] = $note;
				} else {
					$notes['reports'][] = $note;
				}
				
			}
		} catch (PDOException $e) {
			$notes = array();
		}
		return $notes;
	}
	
	function addNote($note, $pdo = null)
	{
		//$note = new PatientScanNote();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$isComment = $note->isComment() ? var_export($note->isComment(), true) : 'false';
			
			$sql = "INSERT INTO patient_scan_notes (patient_scan_id, note, create_uid, create_date, note_area, is_comment) VALUES (" . $note->getPatientScan()->getId() . ", '" . escape($note->getNote()) . "', '" . $note->getCreator()->getId() . "', NOW(), '" . $note->getArea() . "', $isComment)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$note->setId($pdo->lastInsertId());
			} else {
				$note = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$note = null;
		}
		return $note;
	}
	
	function editNote($note, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$sql = "UPDATE patient_scan_notes SET note='" . escape($note->getNote()) . "' WHERE id=" . $note->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$stmt = null;
			return $note;
		} catch (PDOException $e) {
			return null;
		}
	}
} 