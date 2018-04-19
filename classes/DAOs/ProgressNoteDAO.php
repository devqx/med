<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ProgressNoteDAO
 *
 * @author pauldic
 */
class ProgressNoteDAO
{

	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ProgressNote.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($pn, $pdo = null)
	{
		$ntype = '';
		$type = $pn->getNoteType();
		if ($type == 'obj') {
			$ntype = 'o';
		} else if ($type == 'subj') {
			$ntype = 's';
		} else if ($type == 'asst') {
			$ntype = 'a';
		} else if ($type == 'plan') {
			$ntype = 'p';
		} else if ($type == 'doc') {
			$ntype = 'd';
		} else if ($type == 'inv') {
			$ntype = 'i';
		} else if ($type == 'diag_note') {
			$ntype = 'g';
		} else if ($type == 'exam') {
			$ntype = 'e';
		} else if ($type == 'ref') {
			$ntype = 'r';
		} else if ($type == 'revw') {
			$ntype = 'v';
		} else if ($type == 'ph_ex') {
			$ntype = 'x';
		} else if ($type == 'pr_note') {
			$ntype = 'n';
		}
		$value = !is_null($pn->getValue()) ? $pn->getValue() : "NULL";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO progress_note (in_patient_id, `value`, note, note_type, noted_by) VALUES (" . $pn->getInPatient()->getId() . ", $value, '" . escape($pn->getNote()) . "', '$ntype', " . $pn->getNotedBy()->getId() . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$pn->setId($pdo->lastInsertId());
				return $pn;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getProgressNote($pnid, $getFull = FALSE, $pdo = null)
	{
		$pn = new ProgressNote();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM progress_note WHERE id=$pnid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pn->setId($row['id']);
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient();
					$ip->setId($row['in_patient_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['noted_by']);
				}
				$pn->setInPatient($ip);
				$pn->setValue($row['value']);
				$pn->setNote($row['note']);
				$pn->setNotedBy($staff);
				$pn->setEntryTime($row['entry_time']);
				$pn->setNoteType($row['note_type']);
			} else {
				$pn = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pn = null;
		}
		return $pn;
	}

	function getLastProgressNote($ipid, $getFull = FALSE,  $pdo = null)
	{
		$pn = new ProgressNote();
		
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM progress_note WHERE in_patient_id=$ipid ORDER BY entry_time DESC LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pn->setId($row['id']);
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient();
					$ip->setId($row['in_patient_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['noted_by']);
				}
				$pn->setInPatient($ip);
				$pn->setValue($row['value']);
				$pn->setNote($row['note']);
				$pn->setNotedBy($staff);
				$pn->setEntryTime($row['entry_time']);
				$pn->setNoteType($row['note_type']);
			} else {
				$pn = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pn = null;
		}
		return $pn;
	}

	function getProgressNotes($ipid, $getFull = FALSE,  $type_ = null,$pdo = null)
	{
		$pns = array();
		$extrafilter = "";
		if ($type_ != null){
			$extrafilter = " AND note_type=$type_";
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM progress_note WHERE in_patient_id=$ipid $extrafilter ORDER BY entry_time DESC";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pn = new ProgressNote();
				$pn->setId($row['id']);
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient();
					$ip->setId($row['in_patient_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['noted_by']);
				}
				$pn->setInPatient($ip);
				$pn->setValue($row['value']);
				$pn->setNote($row['note']);
				
				$pn->setNotedBy($staff);
				$pn->setEntryTime($row['entry_time']);
				$pn->setNoteType($row['note_type']);
				$pns[] = $pn;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pns = [];
		}
		return $pns;
	}

	function all($ipid, $getFull = FALSE, $pdo = null)
	{
		$pns = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM progress_note WHERE in_patient_id=$ipid ORDER BY entry_time DESC, FIELD(note_type, '', 'v', 'e', 'g', 'p')";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pn = new ProgressNote();
				$pn->setId($row['id']);
				if ($getFull) {
					$ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
					$staff = (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo);
				} else {
					$ip = new InPatient();
					$ip->setId($row['in_patient_id']);
					$staff = new StaffDirectory();
					$staff->setId($row['noted_by']);
				}
				$pn->setInPatient($ip);
				$pn->setValue($row['value']);
				$pn->setNote($row['note']);
				$pn->setNotedBy($staff);
				$pn->setEntryTime($row['entry_time']);
				$pn->setNoteType($row['note_type']);
				$pns[] = $pn;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$pns = [];
		}
		return $pns;
	}

	function updateTask($type, $aid, $pdo = null)
	{
//        try{
//            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
//            $sql="UPDATE progress_note SET round_count=(round_count+1), last_round_time=NOW() WHERE type='".$type."' AND in_patient_id=(SELECT id FROM clinical_task WHERE in_patient_id=".$aid." ORDER BY id DESC LIMIT 1 )";
//            error_log($sql);
//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
//            $stmt->execute();
//            errorLog($e);
//            if($stmt->rowCount()>0){
//                $status=TRUE;
//            }else{
//                $status=FALSE;
//            }
//            $stmt=NULL;
//        }  catch (PDOException $e){
//            $stmt=NULL;
//            $status=FALSE;
//        }
//        return $status;
	}

	function updateProgressNote($pNote, $pdo = null)
	{
		//$pNote=new ProgressNote();
		$status = TRUE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE progress_note SET note='" . escape($pNote->getNote()) . "' WHERE id=" . $pNote->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}

// BELOW IS FOR MOBILE APPLICATION
     function getProgressNoteMobile($ipid, $getFull = FALSE, $pdo = null){
	    // This method return inpatient diagnosis to mobile
         $pns = array();
         try {
             $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
             $sql = "SELECT * FROM progress_note WHERE in_patient_id=$ipid AND note_type='n' ORDER BY entry_time DESC";
             //error_log($sql);
             $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
             $stmt->execute();

             while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                 $pn = new ProgressNote();
                 $pn->setId($row['id']);
                 if ($getFull) {
                     $ip = (new InPatientDAO())->getInPatient($row['in_patient_id'], FALSE, $pdo);
                     $staff = (new StaffDirectoryDAO())->getStaff($row['noted_by'], FALSE, $pdo);
                 } else {
                     $ip = new InPatient();
                     $ip->setId($row['in_patient_id']);
                     $staff = new StaffDirectory();
                     $staff->setId($row['noted_by']);
                 }
                 $pn->setInPatient($ip);
                 $pn->setValue($row['value']);
                 $pn->setNote($row['note']);

                 $pn->setNotedBy($staff);
                 $pn->setEntryTime($row['entry_time']);
                 $pns[] = $pn;
             }
             $stmt = null;
         } catch (PDOException $e) {
             $stmt = null;
             $pns = [];
         }
         return $pns;
     }
}