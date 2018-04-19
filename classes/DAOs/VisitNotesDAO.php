<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/8/14
 * Time: 11:47 AM
 */
class VisitNotesDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR:=> ' . $e->getMessage());
		}
	}

	function addNote($note, $pdo = null)
	{
		// $note = new VisitNotes();
		$patientId = $note->getPatient()->getId();
		$notedBy = $note->getNotedBy()->getId();
		$description = escape($note->getDescription());
		$encounter = $note->getEncounter() ? $note->getEncounter()->getId() : "NULL";
		$type = escape($note->getNoteType());
		$ntype = '';
		if ($type == 'obj') {
			$ntype = 'o';
		} else if ($type == 'subj') {
			$ntype = 's'; //
		} else if ($type == 'asst') {
			$ntype = 'a'; //diagnoses
		} else if ($type == 'plan') {
			$ntype = 'p';
		} else if ($type == 'm_plan') {
			$ntype = 'm';
		} else if ($type == 'doc') {
			$ntype = 'd';
		} else if ($type == 'inv') {
			$ntype = 'i';
		} else if ($type == 'diag_note') {
			$ntype = 'g'; //diagnoses comments
		} else if ($type == 'diag_hist') {
			$ntype = 't'; //history diagnoses
		} else if ($type == 'exam') {
			$ntype = 'e';
		} else if ($type == 'ref') {
			$ntype = 'r';
		} else if ($type == 'revw') {
			$ntype = 'v';
		} else if ($type == 'ph_ex') {
			$ntype = 'x';
		} else if ($type == 'arv') {
			$ntype = 'h';
		} else if ($type == 'fm_hx') {
			$ntype = 'hx';
		}

		$sql = "INSERT INTO patient_visit_notes (patient_ID, noted_by, description, `note_type`, hospitalvisited, encounter_id) VALUES ($patientId, $notedBy, '$description', '$ntype', 1, $encounter)";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$note->setId($pdo->lastInsertId());
				return $note;
			}
			error_log("something broke");
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getNote($id, $getFull = FALSE, $pdo = null)
	{
		$note = new VisitNotes();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_visit_notes WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$note->setId($row['id']);
				$note->setPatient((new PatientDemographDAO())->getPatient($row['patient_ID'], FALSE, $pdo));
				$note->setDateOfEntry($row['date_of_entry']);
				$note->setNotedBy((new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo));
				$note->setDescription($row['description']);
				$note->setNoteType($row['note_type']);
				$note->setReason($row['reason']);
				$note->setHospital((new ClinicDAO())->getClinic($row['hospitalvisited'], FALSE, $pdo));
//                $note->setSourceApp();
//                $note->setModule();
			} else {
				return null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		return $note;
	}

	function getPatientNotes($pid, $page = 0, $pageSize = 10, $getFULL = FALSE, $type = null, $pdo = null)
	{

		$sql = "SELECT pvn.*, sd.username FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE patient_ID = $pid";
		if ($type != null) {
			$sql .= " AND note_type='$type'";
		}
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY date_of_entry DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$notes[] = (object)$row;
//                $notes[]=$this->getNote($row['id'], $getFULL, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
		$results = (object)null;
		$results->data = $notes;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	//not yet used
	function getNotes($getFULL = FALSE, $pdo = null)
	{
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_visit_notes";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$notes[] = $this->getNote($row['id'], $getFULL, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $notes;
	}

	function updateNote($note, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_visit_notes SET description = '" . escape($note->getDescription()) . "' WHERE id = " . $note->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = TRUE;
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = FALSE;
			$stmt = null;
		}
		return $status;
	}

	public function getEncounterNotes($id, $type = null, $pdo=null)
	{
		$notes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT pvn.*, sd.username, CONCAT_WS(' ', sd.firstname, sd.lastname) AS doctorName FROM patient_visit_notes pvn LEFT JOIN staff_directory sd ON sd.staffId = pvn.noted_by WHERE pvn.encounter_id = $id";
			
			if ($type != null && !is_array($type)) {
				$sql .= " AND pvn.note_type='$type'";
			}if ($type != null && is_array($type)) {
				$sql .= " AND pvn.note_type IN ('".implode("','", $type)."')";
			}
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// $notes[]=$this->getNote($row['id'], FALSE, $pdo);
				$notes[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $notes;
	}
} 