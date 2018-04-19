<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsurerDAO
 *
 * @author pauldic
 */
class InsurerDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Insurer.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function getInsurer($iid, $getFull = FALSE, $pdo = NULL)
    {
        $ins = new Insurer();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM insurance_owners WHERE id=" . $iid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ins->setId($row['id']);
                $ins->setName($row['company_name']);
                $ins->setAddress($row['address']);
                $ins->setPhone($row['contact_phone']);
                $ins->setEmail($row['contact_email']);
                $ins->setErpProduct($row['partner_id']);

                if ($getFull) {
                    $schemes = (new InsuranceSchemeDAO())->getInsuranceSchemesByOwner($row['id'], FALSE, $pdo);
                    $hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                } else {
                    $schemes = [];
                    $hosp = new Clinic($row['hospid']);
                }
                $ins->setHospital($hosp);
                $ins->setSchemes($schemes);
            } else {
                $ins = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $stmt = $pdo = NULL;
            $ins = NULL;
        }
        return $ins;
    }

    function getInsurers($getFull = FALSE, $pdo = NULL)
    {
        $insers = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM insurance_owners";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ins = new Insurer();
                $ins->setId($row['id']);
                $ins->setName($row['company_name']);
                $ins->setAddress($row['address']);
                $ins->setPhone($row['contact_phone']);
                $ins->setEmail($row['contact_email']);
                if ($getFull) {
                    $schemes = (new InsuranceSchemeDAO())->getInsuranceSchemesByOwner($row['id'], FALSE, $pdo);
                    $hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                } else {
                    $schemes = [];
                    $hosp = new Clinic();
                    $hosp->setId($row['hospid']);
                }
                $ins->setHospital($hosp);
                $ins->setSchemes($schemes);
                $insers[] = $ins;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $stmt = $pdo = NULL;
            $insers = [];
        }
        return $insers;
    }

    function getFilteredInsurers($exceptIds = [], $getFull = FALSE, $pdo = NULL)
    {
        $insers = array();
        $filter = ($exceptIds == NULL || sizeof($exceptIds) < 1) ? "" : " WHERE id NOT IN (" . implode(",", $exceptIds) . ")";
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM insurance_owners " . $filter;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $ins = new Insurer();
                $ins->setId($row['id']);
                $ins->setName($row['company_name']);
                $ins->setAddress($row['address']);
                $ins->setPhone($row['contact_phone']);
                $ins->setEmail($row['contact_email']);
                $ins->setErpProduct($row['partner_id']);
                if ($getFull) {
                    $schemes = (new InsuranceSchemeDAO())->getInsuranceSchemesByOwner($row['id'], FALSE, $pdo);
                    $hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
                } else {
                    $schemes = [];
                    $hosp = new Clinic();
                    $hosp->setId($row['hospid']);
                }
                $ins->setHospital($hosp);
                $ins->setSchemes($schemes);
                $insers[] = $ins;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $stmt = $pdo = NULL;
            $insers = [];
        }
        return $insers;
    }

    function update($insurer, $pdo = NULL)
    {
//        $insurer = new Insurer();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "UPDATE insurance_owners SET company_name='" . $insurer->getName() . "', address='" . $insurer->getAddress() . "', contact_phone='" . $insurer->getPhone() . "', partner_id='". $insurer->getErpProduct() ."', contact_email='" . $insurer->getEmail() . "' WHERE id=" . $insurer->getId() . "# AND hospid=" . $insurer->getHospital()->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                return $insurer;
            }
            return NULL;
        } catch (PDOException $e) {
            return NULL;
        }
    }

    function add($insurer, $pdo = NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "INSERT INTO insurance_owners SET company_name='" . $insurer->getName() . "', address='" . $insurer->getAddress() . "', contact_phone='" . $insurer->getPhone() . "', contact_email='" . $insurer->getEmail() . "', partner_id='". $insurer->getErpProduct() ."' ";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if($stmt->rowCount() == 1){
                $insurer->setId($pdo->lastInsertId());
            } else {
                $insurer = NULL;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $insurer = NULL;
        }
        return $insurer;
    }
}
