<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VaccineDAO
 *
 * @author pauldic
 */
class VaccineDAO {
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineLevel.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineLevelDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineBoosterDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            errorLog($e);
            return NULL;

        }
    }

    function addVaccine($v, $price, $pdo=NULL){
        try {
            $pdo = $pdo==NULL?$this->conn->getPDO():$pdo;

            $pdo->beginTransaction();
            $bCode = "VC" . generateBillableItemCode('vaccines', $pdo);
            $v->setCode($bCode);
            $sql = "INSERT INTO vaccines (billing_code, label, description) VALUES ('" . $v->getCode() . "', '" . escape($v->getName()) . "', '" . escape($v->getDescription()) . "')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $v->setId($pdo->lastInsertId());

                $clinic = new Clinic();
                $clinic->setId(1);
                $vv = new Vaccine();
                $vv->setId($v->getId());

                $vls = (new VaccineLevelDAO())->addVaccineLevels($v->getId(), $v->getLevels(), $pdo);
                if (sizeof($v->getLevels()) > sizeof($vls)) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                if ($v->getHasBooster()) {
                    $v->getBooster()->setVaccine($vv);
                    $vb = (new VaccineBoosterDAO())->addVaccineBooster($v->getBooster(), $pdo);
                    if ($vb == NULL) {
                        $pdo->rollBack();
                        $stmt = null;
                        return NULL;
                    }
                }

                $insureBI = new InsuranceBillableItem();
                $insureBI->setItem($v);
                $insureBI->setItemDescription($v->getDescription());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(6, $pdo));
                $insureBI->setClinic($clinic);
                $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                $insureIC = new InsuranceItemsCost();
                $insureIC->setItem($v);
                $insureIC->setSellingPrice ($price);
                $insureSch = new InsuranceScheme();
                $insureSch->setId(1);
                $insureIC->setInsuranceScheme($insureSch);
                $insureIC->setClinic($clinic);
                $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
                if ($insIC == NULL) {
                    error_log("Failed to add item cost........");
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
            } else {
                $pdo->rollBack();
                $stmt = NULL;
                $v = NULL;
            }

            $pdo->commit();
            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $pdo->rollBack();
            $stmt = NULL;
            $v = NULL;
        }
        return $v;
    }

    function getVaccine($vid, $pdo = NULL) {
        $vaccine = new Vaccine();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM vaccines WHERE id=" . $vid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vaccine->setId($row['id']);
                $vaccine->setCode($row['billing_code']);
                $vaccine->setName($row['label']);
                $vaccine->setDescription($row['description']);
                $vaccine->setPrice(  (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo) );
                $vaccine->setActive($row['active']);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $vaccine = NULL;
        }
        return $vaccine;
    }

    function updateVaccine($v, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "UPDATE vaccines SET label = '".escape($v->getName())."', description='".escape($v->getDescription())."', active='".escape($v->getActive())."' WHERE id = ". $v->getId();
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1 || $stmt->rowCount()== 0) {
                $insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($v->getCode(), TRUE, $pdo);
                $insureBI->setItemDescription($v->getDescription());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(6, $pdo));
                $insureBI->setClinic( (new ClinicDAO())->getClinic(1, FALSE, $pdo) );
                $insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                $insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($v->getCode(), 1, FALSE, FALSE, $pdo);
                $insureIC->selling_price = ($v->getPrice());
                $insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
                if ($insIC == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
                return $v;
            } else {
                $pdo->rollBack();
                $v = NULL;
            }

            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = NULL;
            $v = NULL;
        }
        return $v;
    }

    function getVaccineByCode($iCode, $pdo = NULL){
        $vaccine = new Vaccine();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM vaccines WHERE billing_code='" . $iCode . "'";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                // return (object)$row;
                $vaccine->setId($row['id']);
                $vaccine->setCode($row['billing_code']);
                $vaccine->setName($row['label']);
                $vaccine->setDescription($row['description']);
                $vaccine->setActive($row['active']);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $vaccine = NULL;
        }
        return $vaccine;
    }

    //TODO make the function return the vaccine details as it relates to this Patient
    function getPatientVaccineDetails($vid, $patId, $getFull = FALSE, $pdo = NULL)
    {
        $vaccine = new Vaccine();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM vaccines WHERE id=" . $vid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vaccine->setId($row['id']);
                $vaccine->setCode($row['billing_code']);
                $vaccine->setName($row['label']);
                $vaccine->setDescription($row['description']);
                $vaccine->setActive($row['active']);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $vaccine = NULL;
        }
        return $vaccine;
    }

    function getVaccines($pdo = NULL)
    {
        $vaccines = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM vaccines";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vaccine = new Vaccine();
                $vaccine->setId($row['id']);
                $vaccine->setCode($row['billing_code']);
                $vaccine->setName($row['label']);
                $vaccine->setDescription($row['description']);
                $vaccine->setActive($row['active']);
                $vaccines[] = $vaccine;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $vaccines = array();
        }
        return $vaccines;
    }

    function getNextBillCode($pdo = NULL)
    {
        $billingCode = $bCode = 0;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT billing_code FROM vaccines order by id DESC";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $billingCode = $row ['billing_code'];

                if ($billingCode != "0" && $billingCode != "") {
                    $billingCode = str_replace("VC", "", $bCode);
                }
                $billingCode = (int)$billingCode;
                $billingCode = "VC" . str_pad($billingCode, 6, '0', STR_PAD_LEFT);
            } else {

            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $billingCode = 0;
        }
        return $billingCode;
    }

    //Move this function to PatientVaccine DAO
    function getDueVaccinesList($pdo = NULL)
    {
        $vaccines = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT vaccine_id, count(*) as sum_ FROM patient_vaccine WHERE (date(now()) between due_date and expiration_date) and entry_date is null group by vaccine_id";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vaccine = new Vaccine();
                $vaccine->setId($row['id']);
                $vaccine->setCode($row['billing_code']);
                $vaccine->setName($row['label']);
                $vaccine->setDescription($row['description']);
                $vaccine->setActive($row['active']);
                $vaccines[] = $vaccine;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $vaccines = array();
        }
        return $vaccines;
    }
}
