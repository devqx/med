<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/5/15
 * Time: 4:47 PM
 */

class VaccineBoosterHistoryDAO {
    private $conn = null;

    function __construct(){
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineBoosterHistory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineBoosterDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;

        }
    }

    function getHistory($vbid, $pdo=NULL){
        $h = array();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from patient_vaccine_booster_history WHERE patientvaccinebooster_id=".$vbid." ORDER BY date_taken ASC";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vbh = new VaccineBoosterHistory();
                $vbh->setId($row['id']);
                $vbh->setPatientVaccineBooster((new PatientVaccineBoosterDAO())->getPatientVaccineBooster($row['patientvaccinebooster_id'], TRUE, $pdo));
                $vbh->setDateTaken($row['date_taken']);
                $vbh->setTakenBy((new StaffDirectoryDAO())->getStaff($row['taken_by'], FALSE, $pdo));
                $h[] = $vbh;
            }
            $stmt = null;
        }catch(PDOException $e) {
            errorLog($e);
            $h=array();
        }
        return $h;
    }

    function addDateTaken($pvb, $pdo = NULL){
        $ret = FALSE;
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO():$pdo;
            $sql = "INSERT INTO patient_vaccine_booster_history (patientvaccinebooster_id, date_taken, taken_by) VALUES ('".$pvb->getPatientVaccineBooster()."', '".$pvb->getDateTaken()."', '".$pvb->getTakenBy()."')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $pvb->setId($pdo->lastInsertId());
                $ret = TRUE;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = NULL;
            $pvb=NULL;
        }
        return $ret;
    }
}