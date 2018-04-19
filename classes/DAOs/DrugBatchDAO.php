<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/25/14
 * Time: 1:43 PM
 */
class DrugBatchDAO
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
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function add($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO drug_batch (`name`, drug_id, quantity, expiration_date, service_centre_id) VALUES ('" . $batch->getName() . "', " . $batch->getDrug()->getId() . ",'" . $batch->getQuantity() . "','" . $batch->getExpirationDate() . "', " . $batch->getServiceCentre()->getId() . ")";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$batch->setId($pdo->lastInsertId());
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function stockUp($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE drug_batch SET quantity = (quantity + " . $batch->getQuantity() . ") WHERE id = " . $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function stockAdjust($batch, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE drug_batch SET quantity = " . $batch->getQuantity() . ", name='". $batch->getName() ."', expiration_date='". $batch->getExpirationDate() ."',   service_centre_id={$batch->getServiceCentre()->getId()} WHERE id = " . $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function depleteStock($batch, $quantity, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE drug_batch SET quantity = (quantity - " . $quantity . ") WHERE id = " . $batch->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				return $batch;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getBatches($from = null, $to = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null || $from == '') ? '' : $from;
		$t = ($to == null || $to == '') ? '' : $to;
		$drug_where = ($f == '' && $t == '') ? '' : ' WHERE DATE(db.expiration_date) BETWEEN DATE("' . $f . '") AND DATE("' . $t . '")';

		$sql = "SELECT * FROM drug_batch db {$drug_where}";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;

		$batches = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_batch db {$drug_where} ORDER BY DATE(db.expiration_date) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new DrugBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setDrug((new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo));
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));

				$batches[] = $batch;
			}

		} catch (PDOException $e) {
			errorLog($e);
			$batches = [];
		}
		$results = (object)null;
		$results->data = $batches;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function getDrugBatches($drug, $pdo = null)
	{
		$batches = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_batch WHERE drug_id = " . $drug->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new DrugBatch($row['id']);
				$batch->setName($row['name']);
//                $batch->setDrug( (new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo) );
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				
				$batches[] = $batch;
			}

			return $batches;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function getBatch($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if (is_blank($id) || $id === null) return null;
			$sql = "SELECT * FROM drug_batch WHERE id =". $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$batch = new DrugBatch($row['id']);
				$batch->setName($row['name']);
				$batch->setDrug((new DrugDAO())->getDrug($row['drug_id'], FALSE, $pdo));
				$batch->setQuantity($row['quantity']);
				$batch->setExpirationDate($row['expiration_date']);
				$batch->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				return $batch;
				
			}

			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
} 