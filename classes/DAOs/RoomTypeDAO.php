<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RoomTypeDAO
 *
 * @author pauldic
 */
class RoomTypeDAO
{
    private $conn = null;

    function __construct()
    {
        if (!isset($_SESSION)) {
            @session_start();
        }
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/RoomType.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }
    
    function addRoomType($bt, $pdo=NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $bt->setCode("RT" . generateBillableItemCode('room_type', $pdo));
            $sql = "INSERT INTO room_type SET billing_code='".$bt->getCode()."', label = '".$bt->getName()."', hospital_id = '".$bt->getHospital()->getId()."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $bt->setId($pdo->lastInsertId());

                $ibi = new InsuranceBillableItem();
                $ibi->setItem($bt);
                $ibi->setItemDescription($bt->getName() . " (Room Category)");
                $ibi->setItemGroupCategory("admissions");
                $ibi->setItemGroupCategory((new BillSourceDAO())->findSourceById(5, $pdo));
                $ibi->setClinic($bt->getHospital());
                $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($ibi, $pdo);
                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                $iic = new InsuranceItemsCost();
                $iic->setItem($bt);
                $iic->setSellingPrice($bt->getDefaultPrice());
                $scheme = new InsuranceScheme();
                $scheme->setId(1);
                $iic->setInsuranceScheme($scheme);
                $iic->setClinic($bt->getHospital());
                $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($iic, $pdo);

                if ($insIC == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
            } else {
                $bt = NULL;
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            $bt = $stmt = NULL;
        } catch (Exception $e) {
            $bt = $stmt = NULL;
        }

        return $bt;
    }


    function getDefaultPrice($bid, $pdo = NULL)
    {
        $price = NULL;
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN room_type t ON t.billing_code = c.item_code WHERE t.id = $bid AND c.insurance_scheme_id = 1";
            // error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $price = $row['selling_price'];
            }

            $stmt = NULL;
        } catch (PDOException $e) {
            $price = $stmt = NULL;
        }
        return $price;
    }

    function getRoomType($id, $getFull = FALSE, $pdo = NULL)
    {
        $roomType = new RoomType();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room_type WHERE id = " . $id;
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $roomType->setId($row["id"]);
                $roomType->setCode($row["billing_code"]);
                $roomType->setName($row["label"]);
                if ($getFull) {
                    $hosp = (new ClinicDAO())->getClinic($row['hospital_id'], FALSE, $pdo);
                } else {
                    $hosp = new Clinic();
                    $hosp->setId($row['hospital_id']);
                }
                $roomType->setHospital($hosp);
                $roomType->setDefaultPrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row["billing_code"], $pdo));
            } else {
                $roomType = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $roomType = $stmt = NULL;
        }
        return $roomType;
    }

    function getRoomTypes($getFull = FALSE, $pdo = NULL)
    {
        $roomTypes = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room_type ORDER BY label";
//            error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $roomType = new RoomType();
                $roomType->setId($row["id"]);
                $roomType->setCode($row["billing_code"]);
                $roomType->setName($row["label"]);
                if ($getFull) {
                    $hosp = (new ClinicDAO())->getClinic($row['hospital_id'], FALSE, $pdo);
                } else {
                    $hosp = new Clinic();
                    $hosp->setId($row['hospital_id']);
                }
                $roomType->setHospital($hosp);
                $roomType->setDefaultPrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row["billing_code"], $pdo));
                $roomTypes[] = $roomType;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $roomTypes = [];
        }
        return $roomTypes;
    }

    function getRoomTypeByCode($code, $getFull = FALSE, $pdo = NULL)
    {
        $roomType = new RoomType();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM room_type WHERE billing_code ='" . $code . "'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                // return (object)$row;
                $roomType->setId($row["id"]);
                $roomType->setCode($row["billing_code"]);
                $roomType->setName($row["label"]);
                if ($getFull) {
                    $hosp = (new ClinicDAO())->getClinic($row['hospital_id'], FALSE, $pdo);
                } else {
                    $hosp = new Clinic();
                    $hosp->setId($row['hospital_id']);
                }
                $roomType->setHospital($hosp);
                $roomType->setDefaultPrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row["billing_code"], $pdo));
            } else {
                $roomType = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $stmt = NULL;
            $roomType = NULL;
        }
        return $roomType;
    }

    function updateRoomType($bt, $pdo = NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;

            $pdo->beginTransaction();
            $sql = "UPDATE room_type SET label = '" . $bt->getName() . "' WHERE id = " . $bt->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($stmt->rowCount() == 1 || $stmt->rowCount() == 0) {
                $insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($bt->getCode(), 1, FALSE, TRUE, $pdo);
                $insureIC->selling_price = ($bt->getDefaultPrice());
                $insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
                if ($insIC == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return FALSE;
                }
                $pdo->commit();
                $stmt = NULL;
                return TRUE;
            }

            $pdo->rollBack();
            return FALSE;
        } catch (PDOException $e) {
            return FALSE;
        }
    }

}
