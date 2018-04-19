<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/19/15
 * Time: 12:14 PM
 */
class ArvPatientHistoryDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvPatientHistory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvPatientHistoryDataDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($patientHistory, $pdo=NULL){
//        $patientHistory = new ArvPatientHistory();

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO(): $pdo;
            $canCommit = !$pdo->inTransaction();
            try {
                $pdo->beginTransaction();
            } catch (PDOException $ef){
                //
            }

            $sql = "INSERT INTO arv_patient_history (patient_id, arv_history_id, create_uid, create_date) VALUES (".$patientHistory->getPatient()->getId().",".$patientHistory->getHistory()->getId().",".$patientHistory->getCreator()->getId().",NOW())";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $_data= [];

            if($stmt->rowCount()>0){
                $patientHistory->setId($pdo->lastInsertId());
                foreach ($patientHistory->getData() as $d) {
                    // $d = new ArvPatientHistoryData();
                    $d->setPatientHistory($patientHistory);
                    $_data[] = (new ArvPatientHistoryDataDAO())->add($d, $pdo);
                }

                if(count($_data) == count($patientHistory->getData())){
                    if($canCommit){
                        $pdo->commit();
                    }
                    $patientHistory->setId($pdo->lastInsertId());
                    return $patientHistory;
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
            $sql = "SELECT * FROM arv_patient_history WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $history = new ArvPatientHistory($row['id']);
                $history->setCreator( (new StaffDirectoryDAO())->getStaff($row['create_uid'], FALSE, $pdo) );
                $history->setHistory( (new ArvHistoryDAO())->get($row['history_id'], $pdo) );
                $history->setPatient( (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo) );
                $history->setDate( $row['create_date'] );
                $history->setData( (new ArvPatientHistoryDataDAO())->forHistory($row['id'], $pdo) );

                return $history;
            }
            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function forPatient($pid, $pdo=NULL){
        $array = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_patient_history WHERE patient_id=$pid";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $history = $this->get($row['id'], $pdo);
                $array[] = $history;
            }
            return $array;
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
    }
}