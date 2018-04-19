<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PatientVaccineDAO
 *
 * @author pauldic
 */
class PatientVaccineDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientVaccine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function getPatientVaccine($id, $getFull = FALSE, $pdo = NULL)
    {
        $pv = new PatientVaccine();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from patient_vaccine WHERE id=" . $id;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $pv->setId($row['id']);
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL);
                    $vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], $pdo);
                    $takenBy = (new StaffDirectoryDAO())->getStaff($row['taken_by'], FALSE, $pdo);
//                        $vacLevel=(new VaccineLevelDAO())->getVaccineLevel($row['vaccine_id'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph();
                    $pat->setId($row["patient_id"]);
                    $vac = new Vaccine();
                    $vac->setId($row['vaccine_id']);
                    $takenBy = new StaffDirectory();
                    $takenBy->setId($row['taken_by']);
                }
                $pv->setPatient($pat); //Obj
                $pv->setVaccine($vac); //Obj
                $pv->setIsBooster($row['is_booster']);
                $pv->setVaccineLevel($row['vaccine_level']);
                $pv->setDueDate($row['due_date']);
                $pv->setBilled($row['billed']);
                $pv->setEntryDate($row['entry_date']);
                $pv->setTakenBy($takenBy); //Obj
                $pv->setTakeType($row['take_type']);
                $pv->setInternal($row['internal']);
                $pv->setRealAdministerDate($row['real_administer_date']);
                $pv->setExpirationDate($row['expiration_date']);
            } else {
                $pv = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $pv = NULL;
        }
        return $pv;
    }

    function getPatientVaccineByVaccine($vid, $getFull = FALSE, $pdo = NULL){
        $pv_ = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * from patient_vaccine WHERE vaccine_id=" . $vid . " GROUP BY patient_id";//TODO: Emeka I don't think this is so right
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $pv = new PatientVaccine();
                $pv->setId($row['id']);
                if ($getFull) {
                    $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, NULL);
                    $vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], $pdo);
                    $takenBy = (new StaffDirectoryDAO())->getStaff($row['taken_by'], FALSE, $pdo);
//                        $vacLevel=(new VaccineLevelDAO())->getVaccineLevel($row['vaccine_id'], FALSE, $pdo);
                } else {
                    $pat = new PatientDemograph();
                    $pat->setId($row["patient_id"]);
                    $vac = new Vaccine();
                    $vac->setId($row['vaccine_id']);
                    $takenBy = new StaffDirectory();
                    $takenBy->setId($row['taken_by']);
                }
                $pv->setPatient($pat); //Obj
                $pv->setVaccine($vac); //Obj
                $pv->setIsBooster($row['is_booster']);
                $pv->setVaccineLevel($row['vaccine_level']);
                $pv->setDueDate($row['due_date']);
                $pv->setBilled($row['billed']);
                $pv->setEntryDate($row['entry_date']);
                $pv->setTakenBy($takenBy); //Obj
                $pv->setTakeType($row['take_type']);
                $pv->setInternal($row['internal']);
                $pv->setRealAdministerDate($row['real_administer_date']);
                $pv->setExpirationDate($row['expiration_date']);
                $pv_[] = $pv;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $pv_ = array();
        }
        return $pv_;
    }

    function updatePatientVaccineProps($pv, $pdo=NULL){
//        $pv = new PatientVaccine();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE patient_vaccine SET billed=".$pv->getBilled()." WHERE id=".$pv->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if($stmt->rowCount()==1){
                return $pv;
            }
        }catch (PDOException $e){
            return NULL;
        }
        return NULL;
    }

    function getDirectUpdateVaccines($pid, $pdo = NULL)
    {
        $pvs = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "select id from patient_vaccine WHERE due_date <= date(now()) and entry_date is null AND patient_id=$pid";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $pvs[] = $this->getPatientVaccine($row['id'], TRUE, $pdo);
            }
            $stmt = NULL;
            return $pvs;
        } catch (PDOException $e) {
            return [];
        }
    }

}
