<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaffSpecializationDAO
 *
 * @author pauldic
 */
class StaffSpecializationDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			if (!isset($_SESSION)) {
				@session_start();
			}
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addSpecialization($spe, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();

			$bCode = "CO" . generateBillableItemCode('staff_specialization', $pdo);
			$spe->setCode($bCode);
			$sql = "INSERT INTO staff_specialization (billing_code, staff_type ) VALUES ('" . $spe->getCode() . "','" . $spe->getName() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$spe->setId($pdo->lastInsertId());

			if ($stmt->rowCount() != 1) {
				$pdo->rollBack();
				return null;
			}
			//add the billable item
			$item = new InsuranceBillableItem();
			$item->setItem($spe);
			$item->setItemDescription($spe->getName());
			$item->setItemGroupCategory((new BillSourceDAO())->findSourceById(3, $pdo));
			$clinic = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo)->getClinic();
			$item->setClinic($clinic);

			$item_ = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($item, $pdo);

			if ($item_ == null) {
				$pdo->rollBack();
				return null;
			}
			$item_cost = new InsuranceItemsCost();
			$item_cost->setClinic($clinic);
			$item_cost->setItem($spe);
			$item_cost->setSellingPrice(0);

			$scheme = new InsuranceScheme();
			$scheme->setId(1);
			$item_cost->setInsuranceScheme($scheme);

			$item_cost_ = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($item_cost, $pdo);

			if ($item_cost_ == null) {
				$pdo->rollBack();
				return null;
			}
			$pdo->commit();
			$stmt = null;
		} catch (PDOException $e) {
			$spe = null;
		}
		return $spe;
	}

	function get($sid, $pdo = null)
	{
		$spe = new StaffSpecialization();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization WHERE id=$sid ORDER BY staff_type";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spe->setId($row['id']);
				$spe->setCode($row['billing_code']);
				$spe->setName($row['staff_type']);
				$spe->setInpatient((bool)$row['inpatient']);
				$spe->setOutpatient((bool)$row['outpatient']);
				//$spe->setHospital( (new ClinicDAO())->getClinic($_SESSION['staffID'], FALSE, $pdo)  );
				$spe->setHospital((new ClinicDAO())->getClinic(1, FALSE, $pdo));
				//for now, there's only one hospital. so the id might be 1
			} else {
				$spe = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$spe = $stmt = null;
		}
		return $spe;
	}

	function getSpecializationByCode($sCode, $pdo = null)
	{
		$spe = new StaffSpecialization();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization WHERE billing_code='" . $sCode . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spe->setId($row['id']);
				$spe->setCode($row['billing_code']);
				$spe->setName($row['staff_type']);
				$spe->setInpatient((bool)$row['inpatient']);
				$spe->setOutpatient((bool)$row['outpatient']);
			} else {
				$spe = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$spe = $stmt = null;
		}
		return $spe;
	}

	function getSpecializationByTitle($sTitle, $pdo = null)
	{
		$spe = new StaffSpecialization();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization WHERE staff_type='" . $sTitle . "'";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spe->setId($row['id']);
				$spe->setCode($row['billing_code']);
				$spe->setName($row['staff_type']);
				$spe->setInpatient((bool)$row['inpatient']);
				$spe->setOutpatient((bool)$row['outpatient']);
			} else {
				$spe = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$spe = $stmt = null;
		}
		return $spe;
	}

	function getSpecializations($pdo = null)
	{
		$spes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization  ORDER BY id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spe = new StaffSpecialization();
				$spe->setId($row['id']);
				$spe->setCode($row['billing_code']);
				$spe->setName($row['staff_type']);
				$spe->setInpatient((bool)$row['inpatient']);
				$spe->setOutpatient((bool)$row['outpatient']);
				$spes[] = $spe;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$spes = [];
		}
		return $spes;
	}

	function getIpSpecializations($pdo = null)
	{
		$spes = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization WHERE inpatient IS TRUE ORDER BY id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$spe = new StaffSpecialization();
				$spe->setId($row['id']);
				$spe->setCode($row['billing_code']);
				$spe->setName($row['staff_type']);
				$spe->setInpatient((bool)$row['inpatient']);
				$spe->setOutpatient((bool)$row['outpatient']);
				$spes[] = $spe;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$spes = [];
		}
		return $spes;
	}

	function updateSpecialization($spe, $price, $followUpPrice, $pdo = null)
	{
		$status = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			//$spe = new StaffSpecialization();

			$pdo->beginTransaction();//todo try if this pdo is already in transaction
			$sql = "UPDATE staff_specialization SET billing_code='" . $spe->getCode() . "', staff_type='" . escape($spe->getName()) . "', inpatient=".var_export($spe->getInpatient(), true).", outpatient=".var_export($spe->getOutpatient(), true)." WHERE id=" . $spe->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1 || $stmt->rowCount() == 0) {

				$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($spe->getCode(), TRUE, $pdo);
				$insureBI->setItemDescription($spe->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(3, $pdo));
				$insureBI->setClinic($spe->getHospital());
				$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					//we shouldn't roll back here because they might have not changed the name
					//$pdo->rollBack();
				}

				$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($spe->getCode(), 1, FALSE, TRUE, $pdo);;
				$insureIC->selling_price = $price;
				$insureIC->followUpPrice = $followUpPrice;

				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);

				if ($insIC == null) {
					//maybe the price didn't change?
					$pdo->rollBack();
					$stmt = null;
					return FALSE;
				}
				$stmt = null;
				$status = TRUE;
				$pdo->commit();
			} else {
				$pdo->rollBack();
				$stmt = null;
				$status = FALSE;
			}
		} catch (PDOException $e) {
			$stmt = null;
			$status = FALSE;
		}
		return $status;
	}
	
	
	
	function uploadConsultation($spe, $selling_price=null, $followup_price=null, $pdo = null)
	{
		
		try {
			 $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			 $pdo->beginTransaction();
			
			$bCode = "CO" . generateBillableItemCode('staff_specialization', $pdo);
			$spe->setCode($bCode);
			$sql = "INSERT INTO staff_specialization (billing_code, staff_type ) VALUES ('" . $spe->getCode() . "','" . $spe->getName() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$spe->setId($pdo->lastInsertId());
			
			//add the billable item
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($spe);
			$insureBI->setItemDescription(escape($spe->getName()));
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(3, $pdo));
			
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
			$insureIC->setItem($spe);
			$insureIC->setSellingPrice($selling_price);
			$insureIC->setFollowUpPrice($followup_price);
			
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
				return $spe;
			}
			$pdo->rollBack();
			return NULL;
			
		} catch (PDOException $e) {
			error_log("messaging: ". $e->getMessage());
			$spe = null;
		}
		return $spe;
	}
	
	
	function find($name, $pdo=NULL){
		try {
			$pdo=$pdo==NULL? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_specialization WHERE staff_type LIKE '%$name%'";
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
	
	
	
	
	function searchStaffSpecialization($term, $limit = 1000,  $pdo = null)
	{
		$specs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT id, staff_type from staff_specialization WHERE staff_type LIKE '%$term%' ORDER BY id";
			$sql .= " LIMIT " . (isset($limit) ? $limit : 1000);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
				while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$spec = array();
					$spec['id'] = $row["id"];
					$spec['staff_type'] = $row["staff_type"];
					$specs = $spec;
				}
		
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			errorLog($e);
			$specs = [];
		}
		return $specs;
	}
	
	function searchStaffSpecializationById($id,   $pdo = null)
	{
		$specs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * from staff_specialization WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$specs['id'] = $row["id"];
				$specs['staff_type'] = $row["staff_type"];
				
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			errorLog($e);
			$specs = [];
		}
		return $specs;
	}
	
	
}