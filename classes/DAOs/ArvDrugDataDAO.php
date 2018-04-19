<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/1/16
 * Time: 12:30 PM
 */
class ArvDrugDataDAO
{
    private $conn = null;

    function __construct()
    {
        if(!isset($_SESSION)){session_start();}
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvDrugData.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ArvDrug.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ArvDrugDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($record, $pdo=NULL){
        $patient_id = $record->getPatient()->getId();
        $arv_drug_id = $record->getArvDrug()->getId();
        $type = escape($record->getType());
        $dose = escape($record->getDose());
        $prescribedBy = $_SESSION['staffID'];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO arv_drug_data (`patient_id`, arv_drug_id, `type`, dose, `state`, prescribed_by) VALUES ($patient_id, $arv_drug_id, '$type', '$dose', 'active', $prescribedBy)";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount()>0){
                return $record->setId($pdo->lastInsertId());
            }
            return NULL;
        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function get($id, $pdo=NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_drug_data WHERE id=$id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new ArvDrugData($row['id']))->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo))->setArvDrug((new ArvDrugDAO())->get($row['arv_drug_id'], $pdo))->setType($row['type'])->setDose($row['dose'])->setState($row['state'])->setPrescribedBy( (new StaffDirectoryDAO())->getStaff($row['prescribed_by'], FALSE, $pdo) )->setDatePrescribed($row['date_prescribed']);
            }
            return NULL;
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=null){
        $data = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_drug_data";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $data[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
        return $data;
    }
    function forPatient($pid, $pdo=null){
        $data = [];
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM arv_drug_data WHERE patient_id=$pid";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
                $data[] = $this->get($row['id'], $pdo);
            }
        }catch (PDOException $e){
            errorLog($e);
            return [];
        }
        return $data;
    }
}