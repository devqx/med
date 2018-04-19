<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 10:41 AM
 */
class GeneticLabDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticLab.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/GeneticTemplate.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($lab, $pdo = null)
	{
		// $lab = new GeneticLab();
		$name = escape($lab->getName());
		$template = $lab->getTemplate()->getId();
		$printLayout = $lab->getPrintLayout();
		$controlIds = implode(",", $lab->getQualityControls());
		//the comma separated values of ids. no need to convert to object here
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$billingCode = 'PGD' . generateBillableItemCode('genetic_lab', $pdo);
			$lab->setCode($billingCode);
			$sql = "INSERT INTO genetic_lab (`name`, billing_code, genetic_template_id, print_layout, quality_control_ids) VALUES ('$name', '$billingCode', $template, '$printLayout', '$controlIds')";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$lab->setId($pdo->lastInsertId());
				$insureBI = new InsuranceBillableItem();
				$insureBI->setItem($lab);
				$insureBI->setItemDescription($lab->getName());
				$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(21, $pdo));
				$insureBI->setClinic(new Clinic(1));
				$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
				if ($insBI == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}

				$insureIC = new InsuranceItemsCost();
				$insureIC->setItem($lab);
				$insureIC->setSellingPrice($lab->getBasePrice());
				$insureIC->setInsuranceScheme(new InsuranceScheme(1));
				$insureIC->setClinic(new Clinic(1));
				$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
				if ($insIC == null) {
					$pdo->rollBack();
					$stmt = null;
					return null;
				}
				$pdo->commit();
				return $lab;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new GeneticLab($row['id']))
					->setName($row['name'])
					->setQualityControls(explode(",", $row['quality_control_ids']))
					->setPrintLayout($row['print_layout'])
					->setTemplate(new GeneticTemplate($row['genetic_template_id']))
					->setCode($row['billing_code'])
					->setBasePrice((new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($row['billing_code'], $pdo));
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return $data;
		}
	}

	function find($search, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab WHERE `name` LIKE '%$search%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return $data;
		}
	}

	public function getByCode($iCode, $pdo)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM genetic_lab WHERE billing_code='$iCode'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->get($row['id'], $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}