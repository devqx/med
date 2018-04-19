<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/31/14
 * Time: 3:53 PM
 */
class ProcedureDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Procedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getProcedureByCode($iCode, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT p.*, c.selling_price, c.theatrePrice, c.anaesthesiaPrice, c.surgeonPrice FROM `procedure` p LEFT JOIN insurance_items_cost c ON p.billing_code = c.item_code WHERE p.billing_code = '$iCode'# AND c.insurance_scheme_id=1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				return (new Procedure($row['id']))->setName($row['name'])->setIcdCode($row['icd_code'])->setCode($row['billing_code'])->setDescription($row['description'])->setBasePrice($row['selling_price'])->setCategory((new ProcedureCategoryDAO())->get($row['category_id'], $pdo))->setPriceTheatre($row['theatrePrice'])->setPriceAnaesthesia($row['anaesthesiaPrice'])->setPriceSurgeon($row['surgeonPrice']);
			}
			return null;

		} catch (PDOException $e) {
			return null;
		}

	}

	function getProcedures($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT p.*, c.selling_price, c.theatrePrice, c.anaesthesiaPrice, c.surgeonPrice FROM `procedure` p LEFT JOIN insurance_items_cost c ON p.billing_code = c.item_code WHERE c.insurance_scheme_id = 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$procs = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$proc = new Procedure();
				//$proc->setId($row['id']);
				//$proc->setName($row['name']);
				//$proc->setCode($row['billing_code']);
				//$proc->setIcdCode($row['icd_code']);
				//$proc->setDescription($row['description']);
				//$proc->setBasePrice($row['selling_price']);
				//$proc->setCategory((new ProcedureCategoryDAO())->get($row['category_id'], $pdo));
				//$proc->setPriceTheatre($row['theatrePrice']);
				//$proc->setPriceAnaesthesia($row['anaesthesiaPrice']);
				//$proc->setPriceSurgeon($row['surgeonPrice']);

				$procs[] = $this->getProcedure($row['id'], $pdo);
			}
			return $procs;
		} catch (PDOException $e) {
			return [];
		}
	}

	function findProcedures($search, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT p.id FROM `procedure` p LEFT JOIN procedure_category c ON p.category_id=c.id WHERE p.name LIKE '%$search%' OR p.billing_code LIKE '$search%' OR c.name LIKE '%$search%' ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$procs = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procs[] = $this->getProcedure($row['id'], $pdo);
			}
			return $procs;
		} catch (PDOException $e) {
			return [];
		}
	}

	function getByIds($ids, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM `procedure` p WHERE id IN ($ids)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$procs = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$procs[] = $this->getProcedure($row['id'], $pdo);
			}
			return $procs;
		} catch (PDOException $e) {
			return [];
		}
	}

	function getProcedure($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT p.*, c.selling_price, c.theatrePrice, c.anaesthesiaPrice, c.surgeonPrice FROM `procedure` p LEFT JOIN insurance_items_cost c ON p.billing_code = c.item_code WHERE p.id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				//$proc = new Procedure($row['id']);
				//$proc->setName($row['name']);
				//$proc->setIcdCode($row['icd_code']);
				//$proc->setDescription($row['description']);
				//$proc->setCode($row['billing_code']);
				//$proc->setBasePrice($row['selling_price']);
				//
				//$proc->setCategory((new ProcedureCategoryDAO())->get($row['category_id'], $pdo));
				//
				//$proc->setPriceTheatre($row['theatrePrice']);
				//$proc->setPriceAnaesthesia($row['anaesthesiaPrice']);
				//$proc->setPriceSurgeon($row['surgeonPrice']);

				return (new Procedure($row['id']))->setName($row['name'])->setIcdCode($row['icd_code'])->setCode($row['billing_code'])->setDescription($row['description'])->setBasePrice($row['selling_price'])->setCategory((new ProcedureCategoryDAO())->get($row['category_id'], $pdo))->setPriceTheatre($row['theatrePrice'])->setPriceAnaesthesia($row['anaesthesiaPrice'])->setPriceSurgeon($row['surgeonPrice']);
			}
			return null;

		} catch (PDOException $e) {
			return null;
		}
	}

	function addProcedure($proc, $pdo = null)
	{
    //$proc = new Procedure();
		try {
			
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = false;
			if (!$pdo->inTransaction()) {
				$canCommit = true;
				$pdo->beginTransaction();
			}
			$code = "PR" . generateBillableItemCode('procedure', $pdo);
			$proc->setCode($code);
			
			$procedureName = quote_esc_str($proc->getName());
			$procedureDescription = quote_esc_str($proc->getDescription());
			$icd10 = quote_esc_str($proc->getIcdCode());
			
			$sql = "INSERT INTO `procedure` SET `name` = $procedureName, billing_code = '" . $proc->getCode() . "', icd_code=$icd10, description=$procedureDescription, category_id = " . $proc->getCategory()->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($proc);
			$insureBI->setItemDescription($proc->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(8, $pdo));

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
			$insureIC->setItem($proc);
			$insureIC->setSellingPrice($proc->getBasePrice());
			$insureIC->setTheatrePrice($proc->getPriceTheatre());
			$insureIC->setSurgeonPrice($proc->getPriceSurgeon());
			$insureIC->setAnesthesiaPrice($proc->getPriceAnaesthesia());

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
				if($canCommit) {
					$proc->setId($pdo->lastInsertId());
					$pdo->commit();
				}
			} else {
				$proc = null;
				$pdo->rollBack();
			}

			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$proc = null;
		} catch (Exception $e) {
			errorLog($e);
			$proc = null;
		}

		return $proc;
	}

	function updateProcedure($procedure, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE `procedure` SET name = '" . escape($procedure->getName()) . "', icd_code='" . $procedure->getIcdCode() . "', description='" . escape($procedure->getDescription()) . "', category_id=" . $procedure->getCategory()->getId() . " WHERE id = " . $procedure->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$clinic = new Clinic();
			$clinic->setId(1);
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($procedure->getCode(), TRUE, $pdo);
			$insureBI->setItemDescription($procedure->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(8, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}

			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($procedure);
			$insureIC->setSellingPrice($procedure->getBasePrice());
			$insureIC->setTheatrePrice($procedure->getPriceTheatre());
			$insureIC->setAnesthesiaPrice($procedure->getPriceAnaesthesia());
			$insureIC->setSurgeonPrice($procedure->getPriceSurgeon());

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
			errorLog($e);
			$procedure = null;
		}
		return $procedure;
	}
	
	
	function getOrCreate($procedure, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->findProcedures($procedure->getName(), $pdo)[0];
			if ($return != null) {
				return $return;
			} else {
				return $this->addProcedure($procedure, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
} 