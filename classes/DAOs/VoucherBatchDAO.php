<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/15
 * Time: 10:53 AM
 */
class VoucherBatchDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VoucherBatch.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM voucher_batch WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$v = new VoucherBatch($row['id']);
				$v->setQuantity($row['quantity']);
				$v->setUsed((new BillDAO())->getNumberOfVouchersUsed($row['id'], $pdo));
				$v->setAmount($row['amount']);
				$v->setType($row['type']);
				$v->setGenerator((new StaffDirectoryDAO())->getStaff($row['generator_id'], FALSE, $pdo));
				$v->setDescription($row['description']);
				$v->setDateGenerated($row['date_generated']);
				$v->setExpirationDate($row['expiration_date']);
				$v->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
				return $v;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($page, $pageSize, $pdo = null)
	{
		$sql = "SELECT vb.*, (SELECT count(*) FROM voucher v WHERE v.batch_id = vb.id AND date_used IS NOT NULL) AS used FROM voucher_batch vb HAVING used <> vb.quantity AND vb.expiration_date >= DATE(NOW())";
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
		$vs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vs[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		$data = (object)null;
		$data->data = $vs;
		$data->total = $total;
		$data->page = $page;

		return $data;
	}

	function getByType($type, $pdo = null)
	{
		$vs = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM voucher_batch WHERE type = '$type'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vs[] = $this->get($row['id'], $pdo);
			}
			return $vs;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}

	function getByTypeAndDate($type, $from, $to, $generatorId = null, $page = 0, $pageSize = 10, $pdo = null)
	{
		$generator = !is_null($generatorId) ? " AND generator_id=$generatorId" : "";
		$vs = [];
		$total = 0;
		$sql = "SELECT * FROM voucher_batch WHERE type IN ('" . implode("','", $type) . "') AND DATE(date_generated) BETWEEN '$from' AND '$to'{$generator}";
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
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vs[] = $this->get($row['id'], $pdo);
			}
			return $vs;
		} catch (PDOException $e) {
			errorLog($e);
			$vs = [];
		}
		$results = (object)null;
		$results->data = $vs;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}

	function add($v, $pdo = null)
	{
		$quantity = $v->getQuantity();
		$amount = $v->getAmount();
		$type = $v->getType();
		$date_generated = $v->getDateGenerated();
		$expiration_date = $v->getExpirationDate();
		$generator_id = $v->getGenerator()->getId();
		$description = quote_esc_str($v->getDescription());
		$service_centre = $v->getServiceCentre()->getId();

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO voucher_batch(quantity, amount, `type`, generator_id, description, date_generated, expiration_date, service_centre_id) VALUES ($quantity, $amount, '$type', $generator_id, $description, '$date_generated', '$expiration_date', $service_centre)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() == 1) {
				$v->setId($pdo->lastInsertId());
				return $v;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}