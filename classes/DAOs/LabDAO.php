<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Lab
 *
 * @author pauldic
 */
class LabDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Lab.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabTemplateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addLab($lab, $price, $pdo = null)
	{

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$bCode = "LA" . generateBillableItemCode('labtests_config', $pdo);
			$lab->setCode($bCode);
			$sql = "INSERT INTO labtests_config (billing_code, name, category_id, lab_template_id, testUnit_Symbol, reference, hospid) VALUES " . "('" . $lab->getCode() . "', '" . escape($lab->getName()) . "', '" . $lab->getCategory()->getId() . "', '" . $lab->getLabTemplate()->getId() . "', '" . escape($lab->getTestUnitSymbol()) . "', '" . escape($lab->getReference()) . "', " . $lab->getHospital()->getId() . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$lab->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($lab);
				$insureBI->setItemDescription($lab->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(1, $pdo));
				$insureBI->setClinic($lab->getHospital());
				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}

				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($lab);
				$insureIC->setSellingPrice($price);
				$insureSch = new InsuranceScheme();
				$insureSch->setId(1);
				$insureIC->setInsuranceScheme($insureSch);
				$insureIC->setClinic($lab->getHospital());
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				$pdo->commit();
				return $lab;
			} else {
				$pdo->rollBack();
				$lab = null;
			}

			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$lab = null;
		}
		return $lab;
	}

	function getLab($lid, $getFull = FALSE, $pdo = null)
	{
		$lab = new Lab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config WHERE id=" . $lid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$lab->setId($row['id']);
				$lab->setCode($row['billing_code']);
				$lab->setName($row['name']);
				$cfg = (new LabCategoryDAO())->getLabCategory($row['category_id'], $pdo);
				
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
					$temp = (new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], $pdo);
				} else {
					//$cfg = new LabCategory();
					//$cfg->setId($row['category_id']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
					$temp = new LabTemplate($row['lab_template_id']);
				}
				$lab->setCategory($cfg);
				$lab->setLabTemplate($temp);
				$lab->setTestUnitSymbol($row['testUnit_Symbol']);
				$lab->setReference($row['reference']);
				$lab->setDescription((new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($row['billing_code'], FALSE, $pdo)->getItemDescription());
				$lab->setHospital($clinic);
				$lab->setBasePrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo));

			}
			$stmt = null;
		} catch (PDOException $e) {
			$lab = null;
		}
		return $lab;
	}

	function getLabsById($ids, $getFull = FALSE, $pdo = null)
	{
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config WHERE id IN ($ids)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->getLab($row['id'], $getFull, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}


	function getLabByCode($iCode, $getFull = FALSE, $pdo = null)
	{
		$lab = new Lab();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config WHERE billing_code='$iCode'";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				// return (object)$row;
				$lab->setId($row['id']);
				$lab->setCode($row['billing_code']);
				$lab->setName($row['name']);
				/*if ($getFull) {
						$cfg = (new LabCategoryDAO())->getLabCategory($row['category_id'], $pdo);
						$clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE);
						$temp = (new LabTemplateDAO())->getLabTemplate($row['lab_template_id'], FALSE);
				} else {
						$cfg = new LabCategory();
						$cfg->setId($row['category_id']);
						$clinic = new Clinic();
						$clinic->setId($row['hospid']);
						$temp=new LabTemplate($row['lab_template_id']);
				}
				$lab->setCategory($cfg);
				$lab->setLabTemplate($temp);*/
				$lab->setTestUnitSymbol($row['testUnit_Symbol']);
				$lab->setReference($row['reference']);
				//purposely left out the description set- call. it is recursive if it's included
				//since getItem in utils.php, calls this function again
//                $lab->setDescription(  );
				/*$lab->setHospital($clinic);*/
			}
			$stmt = null;
		} catch (PDOException $e) {
			$lab = null;
		}
		return $lab;
	}

	function updateLab($lab, $price, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "UPDATE labtests_config SET `name` = '" . escape($lab->getName()) . "', category_id=" . $lab->getCategory()->getId() . ", lab_template_id=" . $lab->getLabTemplate()->getId() . ", testUnit_Symbol='" . escape($lab->getTestUnitSymbol()) . "', reference='" . escape($lab->getReference()) . "' WHERE id = " . $lab->getId();

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1 || $stmt->rowCount() == 0) {
				$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($lab->getCode(), TRUE, $pdo);
				$insureBI->setItemDescription($lab->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(1, $pdo));
				$insureBI->setClinic($lab->getHospital());
				$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);

				if ($insBI == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				$insureIC = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($lab->getCode(), 1, FALSE, FALSE, $pdo);
				$insureIC->selling_price = ($price);
				$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);

				if ($insIC == null) {
					error_log("Something is not right");
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				// error_log("Came to commit");
				$pdo->commit();
				return $lab;
			} else {
				// error_log("Is there problem");
				$pdo->rollBack();
				$lab = null;
			}

			$stmt = null;
		} catch (PDOException $e) {
			error_log($e->getMessage());
			$stmt = null;
			$lab = null;
		}
		return $lab;
	}

	function getLabs($getFull = FALSE, $pdo = null)
	{
		$labs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM labtests_config";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labs[] = $this->getLab($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$labs = array();
		}
		return $labs;
	}

	function findLabs($search, $getFull = FALSE, $pdo = null)
	{
		$labs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.* FROM labtests_config c LEFT JOIN labtests_config_category l ON c.category_id=l.id WHERE c.name LIKE '%$search%' OR l.name LIKE '%$search%'";
//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$labs[] = $this->getLab($row['id'], $getFull, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$labs = array();
		}
		return $labs;
	}
}
