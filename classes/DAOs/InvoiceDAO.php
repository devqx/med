<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/10/15
 * Time: 10:45 AM
 */
class InvoiceDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Invoice.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceLineDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function create($invoice, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT IGNORE INTO invoice (cashier_id, `time`, patient_id, scheme_id) VALUES ('" . $invoice->getCashier()->getId() . "', NOW(), " . ($invoice->getPatient() !== null ? "'" . $invoice->getPatient()->getId() . "'" : "NULL") . ", " . ($invoice->getScheme() !== null ? "'" . $invoice->getScheme()->getId() . "'" : "NULL") . ")";

			$pdo->beginTransaction();

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$invoice->setId($pdo->lastInsertId());
				foreach ($invoice->getLines() as $l) {
					$l->setInvoice($invoice);
					if ((new BillDAO())->invoiceBill($l->getBill(), $pdo) === null || (new InvoiceLineDAO())->add($l, $pdo) === null) {
						$pdo->rollBack();
						return null;
					}
				}
				$pdo->commit();
				return $this->get($invoice->getId(), $pdo);
			}
			$pdo->rollBack();
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
			$sql = "SELECT * FROM invoice WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invoice = new Invoice($row['id']);
				$invoice->setCashier((new StaffDirectoryDAO())->getStaff($row['cashier_id'], TRUE, $pdo));
				$invoice->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo));
				$invoice->setScheme((new InsuranceSchemeDAO())->get($row['scheme_id'], TRUE, $pdo));
				$invoice->setLines((new InvoiceLineDAO())->getLines($invoice, $pdo));
				$invoice->setTime($row['time']);

				return $invoice;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	public function getForPatient($pid, $pdo = null)
	{
		$invoices = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM invoice WHERE patient_id = $pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invoices[] = $this->get($row['id'], $pdo);
			}
			return $invoices;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	public function getForScheme($sid, $pdo = null)
	{
		$invoices = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM invoice WHERE scheme_id = $sid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$invoices[] = $this->get($row['id'], $pdo);
			}
			return $invoices;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

}