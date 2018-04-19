<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/29/16
 * Time: 12:14 PM
 */
class FormPatientQuestionDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/FormPatientQuestion.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormPatientQuestionAnswerDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/FormQuestionDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($patientQuestion, $pdo=NULL){
//        $patientQuestion = new FormPatientQuestion();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
            $canCommit = !$pdo->inTransaction();
            try {
                $pdo->beginTransaction();
            } catch (PDOException $ef){
                //
            }

            $sql = "INSERT INTO form_patient_question (patient_id, form_id, form_question_id, create_uid, create_date) VALUES (".$patientQuestion->getPatient()->getId().", ".$patientQuestion->getForm()->getId().", ".$patientQuestion->getQuestion()->getId().",".$patientQuestion->getCreator()->getId().", NOW())";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $_data= [];

            if($stmt->rowCount()>0){
                $patientQuestion->setId($pdo->lastInsertId());
                foreach ($patientQuestion->getAnswers() as $d) {
//                     $d = new FormPatientQuestionAnswer();
                    $d->setPatientQuestion($patientQuestion);
                    $_data[] = (new FormPatientQuestionAnswerDAO())->add($d, $pdo);
                }

                if(count($_data) == count($patientQuestion->getAnswers())){
                    if($canCommit){
                        $pdo->commit();
                    }
                    $patientQuestion->setId($pdo->lastInsertId());
                    return $patientQuestion;
                } else {
                    error_log("error: Data inconsistency");
                }
            }
            $pdo->rollBack();
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM form_patient_question WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $history = new FormPatientQuestion($row['id']);
                $history->setCreator( (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo) );
                $history->setQuestion( (new FormQuestionDAO())->get($row['form_question_id'], $pdo) );
                $history->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo) );
                $history->setDate( $row['create_date'] );
                $history->setAnswers( (new FormPatientQuestionAnswerDAO())->forQuestion($row['id'], $pdo) );

                return $history;
            }
            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function forPatient($pid, $formId=NULL, $grouped=FALSE, $pdo=NULL){
        $form = !is_null($formId) ? " AND form_id=".$formId:"";
        $array = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM form_patient_question WHERE patient_id=$pid$form";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $history = $this->get($row['id'], $pdo);
                $array[] = $history;
            }
            if($grouped){
                $return = (object)null;
                $return->data = $array;
                return $return;
            } else {
                return $array;
            }

        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}