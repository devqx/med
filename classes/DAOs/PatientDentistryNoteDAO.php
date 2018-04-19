<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/15/14
 * Time: 11:49 AM
 */

class PatientDentistryNoteDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Dentistry.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/PatientDentistryNote.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDentistryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/StaffDirectoryDAO.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function getNote($id, $pdo=NULL){
        $note = new PatientDentistryNote();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM patient_dentistry_notes WHERE id = $id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $note->setId($row['id']);
                $note->setPatientDentistry( (new PatientDentistryDAO())->get($row['patient_dentistry_id'], $pdo) );
                $note->setDateAdded($row['create_date']);
                $note->setNote($row['note']);
                $note->setArea($row['note_area']);
                $note->setCreator( (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo) );
            }
            else {
                $note = NULL;
                $stmt = NULL;
            }

        }catch (PDOException $e){
            $note = NULL;
        }
        return $note;
    }

    function all($pdo=NULL){
        $notes = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM patient_dentistry_notes";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $notes[] = $this->getNote($row['id'], $pdo);
            }
        }catch (PDOException $e){
            $notes = array();
        }
        return $notes;
    }
    function getDentistryNotes($scanId, $pdo=NULL){
        $notes = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
            $sql = "SELECT * FROM patient_dentistry_notes WHERE patient_dentistry_id = $scanId";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $note = new PatientDentistryNote();
                $note->setId($row['id']);
//                $atch->setPatientDentistry( (new PatientDentistryDAO())->getDentistry($row['patient_dentistry_id'], $pdo) );
                $note->setNote($row['note']);
                $note->setDateAdded($row['create_date']);
                $note->setCreator( (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo) );
                $note->setArea($row['note_area']);
                $notes[] = $note;
            }
        }catch (PDOException $e){
            $notes = array();
        }
        return $notes;
    }

    function add($note, $pdo=NULL){
//        $note = new PatientDentistryNote();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;

            $sql = "INSERT INTO patient_dentistry_notes (patient_dentistry_id, note, create_uid, create_date, note_area) VALUES (".$note->getPatientDentistry()->getId().", '".escape($note->getNote())."', '".$note->getCreator()->getId()."', NOW(), '".$note->getArea()."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){

                $note->setId($pdo->lastInsertId());
            } else {
                $note = NULL;
            }
            $stmt = NULL;
        }catch (PDOException $e){
            $note = NULL;
        }
        return $note;
    }

    function editNote($note, $pdo=NULL){
//        $note = new PatientDentistryNote();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;

            $sql = "UPDATE patient_dentistry_notes SET note='".escape($note->getNote())."' WHERE id=".$note->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $stmt = NULL;
            return $note;
        }catch (PDOException $e){
            return NULL;
        }
    }
} 