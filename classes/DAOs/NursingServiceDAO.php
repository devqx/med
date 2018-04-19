<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/23/15
 * Time: 7:12 AM
 */
class NursingServiceDAO
{
    private $conn = null;

    function __construct() {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/NursingService.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/InsuranceBillableItem.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/InsuranceItemsCost.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/InsuranceScheme.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/Clinic.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceBillableItemDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/InsuranceItemsCostDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/BillSourceDAO.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] .'/functions/utils.php';
            $this->conn=new MyDBConnector();
        }catch(PDOException $e) {
            exit( 'ERROR: ' . $e->getMessage() );
        }
    }

    function get($id, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT s.*, ic.selling_price AS price FROM nursing_service s LEFT JOIN insurance_items_cost ic ON ic.item_code=s.billing_code WHERE s.id=$id #AND ic.insurance_scheme_id=1";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $service = (new NursingService())
                    ->setId($row['id'])
                    ->setCode($row['billing_code'])
                    ->setName($row['service_name'])
                    ->setBasePrice($row['price']);
                return $service;
            }
            return NULL;
        } catch(PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function add($service, $pdo=NULL){
//        $service = new NursingService();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            
            $code = "NS" . generateBillableItemCode('nursing_service', $pdo);
            $service->setCode($code);

            $sql = "INSERT INTO nursing_service SET service_name = '".escape($service->getName())."', billing_code = '".$service->getCode()."'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $service->setId($pdo->lastInsertId());

            $insureBI = new InsuranceBillableItem();
            $insureBI->setItem($service);
            $insureBI->setItemDescription(escape($service->getName()));
            $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(16, $pdo));

            $clinic = new Clinic();
            $clinic->setId(1);
            $insureBI->setClinic($clinic);

            $insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
            if ($insBI == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }

            $insureIC = new InsuranceItemsCost();
            $insureIC->setItem($service);
            $insureIC->setSellingPrice ($service->getBasePrice());
            $insureSch = new InsuranceScheme();
            $insureSch->setId(1);
            $insureIC->setInsuranceScheme($insureSch);
            $insureIC->setClinic($clinic);
            $insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
            if ($insIC == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }
            if($stmt->rowCount()>0){
                $pdo->commit();
                return $service;
            }
            $pdo->rollBack();
            return NULL;

        }catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function update($service, $pdo=NULL){
        // $service = new NursingService();
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $pdo->beginTransaction();
            $sql = "UPDATE nursing_service SET service_name = '".escape($service->getName())."' WHERE id=".$service->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $clinic = new Clinic();
            $clinic->setId(1);
            $insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($service->getCode(), TRUE, $pdo);
            $insureBI->setItemDescription($service->getName());
            $insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(16, $pdo));
            $insureBI->setClinic($clinic);
            $insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
            if ($insBI == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }
            $insureIC = new InsuranceItemsCost();
            $insureIC->setItem($service);
            $insureIC->setSellingPrice($service->getBasePrice());
            $insureSch = new InsuranceScheme();
            $insureSch->setId(1);
            $insureIC->setInsuranceScheme($insureSch);
            $insureIC->setClinic($clinic);
            $insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
            if ($insIC == NULL) {
                $pdo->rollBack();
                $stmt = null;
                return NULL;
            }
            $pdo->commit();
            return $service;
        } catch (PDOException $e){
            errorLog($e);
            return NULL;
        }
    }

    function all($pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT s.*, ic.selling_price AS price FROM nursing_service s LEFT JOIN insurance_items_cost ic ON ic.item_code=s.billing_code WHERE ic.insurance_scheme_id=1";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            $services = [];
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $services[] = $this->get($row['id'], $pdo);
            }
            return $services;
        } catch(PDOException $e){
            errorLog($e);
            return [];
        }
    }

    function getByCode($billingCode, $pdo=NULL){
        try {
            $pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM nursing_service WHERE billing_code = '$billingCode'";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                // return (object)$row;
                return $this->get($row['id'], $pdo);
            }
            return NULL;
        } catch(PDOException $e){
            errorLog($e);
            return NULL;
        }
    }
	
	function find($name, $pdo=NULL){
		try {
			$pdo=$pdo==NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM nursing_service WHERE service_name LIKE '%$name%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				return $this->get($row['id'], $pdo);
			}
			return NULL;
		} catch(PDOException $e){
			errorLog($e);
			return NULL;
		}
	}
}