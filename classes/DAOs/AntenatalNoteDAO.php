<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/26/15
 * Time: 3:55 PM
 */

class AntenatalNoteDAO {
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/AntenatalNote.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($note, $pdo=NULL){
        //$note = new AntenatalNote();
        $patient_id = $note->getPatient()->getId();
        $antenatalInstance = $note->getAntenatalInstance()->getId();
        $Text = escape($note->getNote());
        $notedBy = $note->getEnteredBy()->getId();
        $type = $note->getType();
        $assessment_id = ($note->getAssessment()? $note->getAssessment()->getId():"NULL");

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO antenatal_notes (patient_id, antenatal_enrollment_id, note, entered_on, entered_by, type, antenatal_assesment_id) VALUES ('$patient_id','$antenatalInstance','$Text',NOW(),'$notedBy', '$type', $assessment_id)";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $note->setId($pdo->lastInsertId());
            } else {
                $note = NULL;
            }
        }catch (PDOException $e){
            errorLog($e);
            $note = NULL;
        }

        return $note;
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM antenatal_notes WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $note = new AntenatalNote($row['id']);
                $note->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo) );
                $note->setNote($row['note']);
                $note->setEnteredOn($row['entered_on']);
                $note->setEnteredBy( (new StaffDirectoryDAO())->getStaff($row['entered_by'], FALSE, $pdo));
                $note->setType($row['type']);
//                $note->setAssessment()
//                $note->setAntenatalInstance()
                //this would cause too many recursive redirects
            } else {
                $note = NULL;
            }
        }catch (PDOException $e){
            errorLog($e);
            $note = NULL;
        }

        return $note;
    }

    function getInstanceNotes($instanceId, $pdo=NULL){
        $notes = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM antenatal_notes WHERE antenatal_enrollment_id=$instanceId";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $note = $this->get($row['id'], $pdo);

                $notes[] = $note;
            }
        }catch (PDOException $e){
            errorLog($e);
            $notes = [];
        }

        return $notes;
    }

    function getAssessmentNotes($assessmentId, $pdo=NULL){
        $notes = [];
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM antenatal_notes WHERE antenatal_assesment_id = ". $assessmentId;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $notes[] = $this->get($row['id'], $pdo);
            }

        } catch (PDOException $e){
            errorLog($e);
            $notes = [];
        }

        return $notes;
    }
}