<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/4/15
 * Time: 11:55 AM
 */
class BillSourceDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/BillSource.php';
			if (!isset($_SESSION)) session_start();
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getBillSource($sid, $pdo = null)
	{
		$billsource = new BillSource();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($sid === null) {
				return null;
			}
			$sql = "SELECT * FROM bills_source WHERE id=" . $sid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$billsource->setId($row['id']);
				$billsource->setName($row['name']);
			} else {
				$billsource = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$billsource = null;
		}
		return $billsource;
	}


	function getBillSources($pdo = null)
	{
		$bill_sources = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills_source ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$billsource = new BillSource();
				$billsource->setId($row['id']);
				$billsource->setName($row['name']);

				$bill_sources[] = $billsource;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$bill_sources = array();
		}
		return $bill_sources;
	}

	function findSourceById($id, $pdo = null)
	{
		$bs = new BillSource();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM bills_source WHERE `id` = $id LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$bs->setId($row['id']);
				$bs->setName($row['name']);
			} else {
				$bs = null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$bs = null;
		}
		return $bs;
	}

	function addBillSource($bs, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO bills_source (`name`) VALUES ('" . $bs->getName() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($stmt->rowCount() > 0) {
				$bs->setId($pdo->lastInsertId());
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$bs = null;
		}
		return $bs;
	}

	function updateBillSource($source, $pdo = null)
	{
		$r = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE bills_source SET `name`='" . $source->getName() . "' WHERE id=" . $source->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$r = TRUE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
		}
		return $r;
	}

	function delBillSource($source, $pdo = null)
	{
		$r = FALSE;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM bills_source WHERE id=" . $source->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$r = TRUE;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
		}
		return $r;
	}
}