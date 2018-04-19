<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugDAO
 *
 * @author pauldic
 */
class DrugDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Drug.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugManufacturer.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DispensedDrugs.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DispensedDrugsDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addDrug($d, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = false;
			if (!$pdo->inTransaction()) {
				$canCommit = true;
				$pdo->beginTransaction();
			}
			$code = "DR" . generateBillableItemCode('drugs', $pdo);
			$d->setCode($code);
			$erp_product_id = $d->getErpProduct() ? quote_esc_str($d->getErpProduct()) : "NULL";
			$sql = "INSERT INTO drugs SET `name` = '" . escape($d->getName()) . "', billing_code = '" . $d->getCode() . "', drug_generic_id = " . $d->getGeneric()->getId() . ", manufacturer_id=" . $d->getManufacturer()->getId() . ", erp_product_id = " . $erp_product_id . ", stock_uom='" . $d->getStockUOM() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$d->setId($pdo->lastInsertId());
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($d);
			$insureBI->setItemDescription(escape($d->getName()));
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(2, $pdo));
			
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI->setClinic($clinic);
			
			$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				if ($canCommit) {
					$pdo->rollBack();
				}
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($d);
			$insureIC->setSellingPrice($d->getBasePrice());
			$insureSch = new InsuranceScheme();
			$insureSch->setId(1);
			$insureIC->setInsuranceScheme($insureSch);
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
			if ($insIC == null) {
				if ($canCommit) {
					$pdo->rollBack();
				}
				$stmt = null;
				return null;
			}
			if ($stmt->rowCount() > 0) {
				if ($canCommit) {
					$pdo->commit();
				}
			} else {
				$d = null;
				if ($canCommit) {
					$pdo->rollBack();
				}
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$d = null;
		} catch (Exception $e) {
			$d = null;
			errorLog($e);
		}
		
		return $d;
	}
	
	//FIXME: check usages and apply correctly
	//    function updateStock($d, $pdo = NULL) {
	//        try {
	//            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
	//            $sql = "UPDATE drugs SET stock_quantity=(stock_quantity+" . $d->getStockQuantity() . ") WHERE id = " . $d->getId();
	//            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	//            $stmt->execute();
	//            $status=true;
	//
	//            $stmt = NULL;
	//        } catch (Exception $e) {
	//            $status=false;
	//        }
	//        return $status;
	//    }
	
	function updateDrug($d, $id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE drugs SET `name` = '" . escape($d->getName()) . "',erp_product_id=". quote_esc_str($d->getErpProduct()) .", drug_generic_id = " . $d->getGeneric()->getId() . ",  manufacturer_id=" . $d->getManufacturer()->getId() . ", stock_uom = '" . $d->getStockUOM() . "' WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($d->getCode(), true, $pdo);
			$insureBI->setItemDescription($d->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(2, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($d);
			$insureIC->setSellingPrice($d->getBasePrice());
			$insureSch = new InsuranceScheme();
			$insureSch->setId(1);
			$insureIC->setInsuranceScheme($insureSch);
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$pdo->commit();
			$stmt = null;
		} catch (Exception $e) {
			$d = null;
		}
		return $d;
	}
	
	function getPrice($did, $pid, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN drugs d ON d.billing_code = c.item_code WHERE d.id = $did AND c.insurance_scheme_id = (SELECT insurance_scheme FROM insurance WHERE patient_id = '$pid')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			} else {
				$price = $this->getDefaultPrice($did, $pdo);
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
		}
		return $price;
	}
	
	function getDefaultPrice($did, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN drugs d ON d.billing_code = c.item_code WHERE d.id = $did AND c.insurance_scheme_id = 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
		}
		return $price;
	}
	
	function getDrug($id, $getFull = false, $pdo = null)
	{
		if (is_blank($id)) {
			return null;
		}
		$drug = new Drug();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS price FROM drugs d LEFT JOIN insurance_items_cost ic ON ic.item_code=d.billing_code WHERE d.id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$drug->setId($row["id"]);
				$drug->setName($row["name"]);
				$drug->setCode($row['billing_code']);
				$drug->setErpProduct($row['erp_product_id']);
				if ($getFull) {
					$gen = (new DrugGenericDAO())->getGeneric($row["drug_generic_id"], true, $pdo);
					$man = (new DrugManufacturerDAO())->getManufacturer($row["manufacturer_id"], $pdo);
				} else {
					$gen = new DrugGeneric($row["drug_generic_id"]);
					$man = new DrugManufacturer($row["manufacturer_id"]);
				}
				$drug->setGeneric($gen);
				$drug->setManufacturer($man);
				
				$batches = (new DrugBatchDAO())->getDrugBatches($drug, $pdo);
				$drug->setBatches($batches);
				
				$quantity = 0;
				foreach ($batches as $batch) {
					//todo: batches that have not expired
					$quantity += $batch->getQuantity();
				}
				
				$drug->setStockQuantity($quantity);
				$drug->setBasePrice($row['price']);
				$drug->setStockUOM($row['stock_uom']);
			} else {
				$drug = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$drug = null;
		}
		return $drug;
	}
	
	function getDrugs($activeGenericsOnly = true, $pdo = null)
	{
		$drugs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT drugs.* FROM drugs";
			if ($activeGenericsOnly) {
				$sql .= " LEFT JOIN drug_generics dg ON drugs.drug_generic_id=dg.id WHERE dg.active IS TRUE";
			} else {
				$sql .= " LEFT JOIN drug_generics dg ON drugs.drug_generic_id=dg.id WHERE dg.active IS FALSE";
			}
			$sql .= " ORDER BY drugs.`name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$drug = $this->getDrug($row['id'], true, $pdo);
				//if ($activeGenericsOnly && $drug->getGeneric()->getActive()) {
				//	$drugs[] = $drug;
				//} else if (!$activeGenericsOnly && !$drug->getGeneric()->getActive()) {
				$drugs[] = $drug;
				//}
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$drugs = [];
		}
		return $drugs;
	}
	
	function findDrugs($search, $activeGenericsOnly = true, $pdo = null)
	{
		$drugs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT drugs.* FROM drugs";
			if ($activeGenericsOnly) {
				$sql .= " LEFT JOIN drug_generics dg ON drugs.drug_generic_id=dg.id WHERE dg.active IS TRUE";
			} else {
				$sql .= " LEFT JOIN drug_generics dg ON drugs.drug_generic_id=dg.id WHERE dg.active IS FALSE";
			}
			$sql .= " AND drugs.name LIKE '%$search%' ORDER BY drugs.`name`";
			error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$drug = $this->getDrug($row['id'], true, $pdo);
				//if ($activeGenericsOnly && $drug->getGeneric()->getActive()) {
				//	$drugs[] = $drug;
				//} else if (!$activeGenericsOnly && !$drug->getGeneric()->getActive()) {
				$drugs[] = $drug;
				//}
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$drugs = [];
		}
		return $drugs;
	}
	
	function getDrugsByGeneric($gid, $activeGenericsOnly = true, $pdo = null)
	{
		$drugs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.* FROM drugs d LEFT JOIN drug_generics g ON g.id=d.drug_generic_id WHERE d.drug_generic_id = $gid " . ($activeGenericsOnly ? " AND g.active IS TRUE " : "") . " ORDER BY d.name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$drugs[] = $this->getDrug($row['id'], true, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$drugs = null;
		}
		return $drugs;
	}
	
	function getDrugsByCategory($cId, $pdo = null)
	{
		$drugs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.* FROM drugs d LEFT JOIN drug_generics g ON g.id=d.drug_generic_id WHERE g.category_ids LIKE '$cId' OR g.category_ids LIKE '$cId,%' OR g.category_ids LIKE '%,$cId,%' OR g.category_ids LIKE '%,$cId'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$drug = $this->getDrug($row['id'], true, $pdo);
				$drugs[] = $drug;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$drugs = null;
		}
		return $drugs;
	}
	
	function getDrugByCode($iCode, $getFull = false, $pdo = null)
	{
		$drug = new Drug();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS price FROM drugs d LEFT JOIN insurance_items_cost ic ON ic.item_code=d.billing_code WHERE d.billing_code='$iCode'";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				$drug->setId($row["id"]);
				$drug->setName($row["name"]);
				$drug->setCode($row['billing_code']);
				//FIXME: check if this will break things
				//if($getFull){
				$gen = (new DrugGenericDAO())->getGeneric($row["drug_generic_id"], $getFull, $pdo);
				//    $man=(new DrugManufacturerDAO())->getManufacturer($row["manufacturer_id"], $pdo);
				//                }else{
				//                    $gen=new DrugGeneric($row["drug_generic_id"]);
				//                    $man=new DrugManufacturer($row["manufacturer_id"]);
				//                }
				$drug->setGeneric($gen);
				//$drug->setManufacturer($man);
				
				$batches = (new DrugBatchDAO())->getDrugBatches($drug, $pdo);
				//$drug->setBatches( $batches );
				
				//$quantity = 0;
				//foreach ($batches as $batch) {
				//  $quantity += $batch->getQuantity();
				//}
				//
				//$drug->setStockQuantity($quantity);
				$drug->setBasePrice($row['price']);
				$drug->setStockUOM($row['stock_uom']);
			} else {
				$drug = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$drug = null;
		}
		return $drug;
	}
	
	function getDrugAvailableBatch($drug, $qty, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$drug = $this->getDrug($drug->getId(), false, $pdo);
			foreach ($drug->getBatches() as $batch) {
				//$batch = new DrugBatch();
				if ($batch->getQuantity() > $qty && strtotime($batch->getExpirationDate()) > time())
					return $batch;
			}
			return null;
		} catch (PDOException $e) {
			return null;
		}
	}
	
	function dispenseDrug($drug, $qty, $batch, $pat, $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//get the patient again, so that we go deeper
			$pat = (new PatientDemographDAO())->getPatient($pat->getId(), false, $pdo, null);
			$batch = (new DrugBatchDAO())->getBatch($batch->getId(), $pdo);
			if ($qty > $batch->getQuantity()) {
				$overflow = $batch->getQuantity() - $qty;
				$quantity = $batch->getQuantity();
			} else {
				$overflow = 0;
				$quantity = $qty;
			}
			$disp = (new DispensedDrugs())->setDrug($drug)->setPatient($pat)->setQuantity($quantity)->setBatch($batch)->setQuantityOverflow($overflow)//make sure this batch is not expired during usage
			->setBilledTo($pat->getScheme())->setPharmacist((new StaffDirectoryDAO())->getStaff($_SESSION["staffID"], false, $pdo))->setType('fill');
			
			$item = (new DispensedDrugsDAO())->add($disp, $pdo);
			$stmt = null;
			if ($item !== null) {
				$status = true;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
		}
		return $status;
	}
	
	function getDrugStockUOMs($pdo = null)
	{
		//TODO: we can implement a db fetch here in the future
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
		$MainConfig = new MainConfig();
		return $MainConfig::$drug_stock_uom;
	}
	
	function findDrugByProps($name, $generic, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$genId = $generic->getId();
			
			$sql = "SELECT * FROM drugs WHERE drug_generic_id=$genId AND `name`=".quote_esc_str($name);
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->getDrug($row['id'], FALSE, $pdo);
			}
			return null;
		} catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
}