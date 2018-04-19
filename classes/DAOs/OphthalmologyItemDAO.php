<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 11:58 AM
 */
class OphthalmologyItemDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/OphthalmologyItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function add($item, $pdo = NULL)
    {
//        $item = new OphthalmologyItem();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $bCode = "PI" . generateBillableItemCode('ophthalmology_item', $pdo);
            $item->setCode($bCode);
            $sql = "INSERT INTO ophthalmology_item (billing_code, `name`)  VALUES ('" . $item->getCode() . "', '" . escape($item->getName()) . "')";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                $item->setId($pdo->lastInsertId());
                $insureBI = new InsuranceBillableItem();
                $insureBI->setItem($item);
                $insureBI->setItemDescription($item->getName());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(18, $pdo));
                $insureBI->setClinic(new Clinic(1));
                $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }

                $insureIC = new InsuranceItemsCost();
                $insureIC->setItem($item);
                $insureIC->setSellingPrice ($item->getBasePrice());
                $insureSch = new InsuranceScheme();
                $insureSch->setId(1);
                $insureIC->setInsuranceScheme($insureSch);
                $insureIC->setClinic(new Clinic(1));
                $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
                if ($insIC == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
                return $item;
            } else {
                $pdo->rollBack();
                $item = NULL;
            }

            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = NULL;
            $item = NULL;
        }
        return $item;
    }

    function get($lid, $pdo = NULL)
    {
        $item = new OphthalmologyItem();

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_item WHERE id=" . $lid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                return (new OphthalmologyItem())
                    ->setId($row['id'])
                    ->setCode($row['billing_code'])
                    ->setName($row['name'])
                    ->setBasePrice( (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo) );
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            errorLog($e);
            $item = NULL;
        }
        return $item;
    }

    function getByCode($iCode, $pdo = NULL)
    {
        $item = new OphthalmologyItem();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_item WHERE billing_code='" . $iCode . "'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                // return (object)$row;
                return $this->get($row['id'], $pdo);
            }
        } catch (PDOException $e) {
            $item = NULL;
            errorLog($e);
        }
        return $item;
    }

    function update($item, $pdo = NULL){
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "UPDATE ophthalmology_item SET `name` = '".escape($item->getName())."' WHERE id = ". $item->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1 || $stmt->rowCount()== 0) {
                $insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($item->getCode(), TRUE, $pdo);
                $insureBI->setItemDescription($item->getName());
                $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(18, $pdo));
                $insureBI->setClinic(new Clinic(1));
                $insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);

                if ($insBI == NULL) {
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($item->getCode(), 1, FALSE, FALSE, $pdo);
                $insureIC->selling_price = ($item->getBasePrice());
                $insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);

                if ($insIC == NULL) {
                    error_log("Something is not right");
                    $pdo->rollBack();
                    $stmt = null;
                    return NULL;
                }
                $pdo->commit();
                return $item;
            } else {
                error_log("Is there problem");
                $pdo->rollBack();
                $item = NULL;
            }

            $stmt = null;
        } catch (PDOException $e) {
            errorLog($e);
            $stmt = NULL;
            $item = NULL;
        }
        return $item;
    }

    function all($pdo = NULL)
    {
        $items = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_item";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $items[] = $this->get($row['id'], $pdo);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $items = array();
            errorLog($e);
        }
        return $items;
    }

    function findItems($search, $pdo = NULL)
    {
        $array = array();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM ophthalmology_item WHERE `name` LIKE '%$search%'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $array[] = $this->get($row['id'], $pdo);
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            $array = array();
            errorLog($e);
        }
        return $array;
    }
}