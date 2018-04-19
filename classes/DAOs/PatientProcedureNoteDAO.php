<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 2:41 PM
 */
class PatientProcedureNoteDAO
{
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedureNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureSpecialtyDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getProcedureNotes($p_procedure, $pdo = null)
	{
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_note WHERE patient_procedure_id = " . $p_procedure->getId() . " ORDER BY note_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$note = new PatientProcedureNote($row['id']);
				//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
				$note->setStaff((new StaffDirectoryDAO())->getStaff($row['staff_id'], false, $pdo));
				$note->setNote($row['note']);
				$note->setSpecialty((new ProcedureSpecialtyDAO())->get($row['specialization_id'], $pdo));
				$note->setNoteTime($row['note_time']);
				$note->setType($row['note_type']);
				
				$notes[] = $note;
			}
		} catch (PDOException $e) {
		
		}
		return $notes;
	}
	
	function getNotes($pdo = null)
	{
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_note ORDER BY patient_procedure_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$note = new PatientProcedureNote($row['id']);
				$note->setPatientProcedure((new PatientProcedureDAO())->get($row['patient_procedure_id'], $pdo));
				$note->setStaff((new StaffDirectoryDAO())->getStaff($row['staff_id'], false, $pdo));
				$note->setNote($row['note']);
				$note->setSpecialty((new ProcedureSpecialtyDAO())->get($row['specialization_id'], $pdo));
				$note->setNoteTime($row['note_time']);
				$note->setType($row['note_type']);
				
				$notes[] = $note;
			}
		} catch (PDOException $e) {
		
		}
		return $notes;
	}
	
	function addPatientProcedureNote($procedure_note, $pdo = null)
	{
		//$procedure_note = new PatientProcedureNote();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$specialty = $procedure_note->getSpecialty() ? $procedure_note->getSpecialty()->getId() : 'NULL';
			$sql = "INSERT INTO patient_procedure_note (patient_procedure_id, note, note_type, staff_id, specialization_id, note_time) VALUES (" . $procedure_note->getPatientProcedure()->getId() . ", '" . escape($procedure_note->getNote()) . "', '" . $procedure_note->getType() . "', '" . $procedure_note->getStaff()->getId() . "', $specialty, NOW())";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$procedure_note->setId($pdo->lastInsertId());
				return $procedure_note;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}