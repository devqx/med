<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/14/17
 * Time: 9:52 AM
 */

class CurrencyDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Currency.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function all($pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM currency";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM currency WHERE id=$id";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new Currency($row['id']))->setTitle($row['title'])->setCode($row['code'])->setSymbolLeft($row['symbol_left'])->setSymbolRight($row['symbol_right'])->setDecimalPlace($row['decimal_place'])->setValue($row['value'])->setActive((bool)$row['active'])->setDefault((bool)$row['default'])->setDateModified($row['date_modified']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getDefault($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM currency WHERE `default` IS TRUE";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new Currency($row['id']))->setTitle($row['title'])->setCode($row['code'])->setSymbolLeft($row['symbol_left'])->setSymbolRight($row['symbol_right'])->setDecimalPlace($row['decimal_place'])->setValue($row['value'])->setActive((bool)$row['active'])->setDefault((bool)$row['default'])->setDateModified($row['date_modified']);
			}
			error_log("...........");
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}