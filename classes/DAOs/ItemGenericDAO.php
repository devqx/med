<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/28/17
 * Time: 4:49 PM
 */
class ItemGenericDAO
{

	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGeneric.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';

			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit("ERROR: " . $e->getMessage());
		}

	}

	function get($id, $pdo = null)
	{
		if(is_null($id)){
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_generic WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$generic = new ItemGeneric();
				$generic->setId($row['id']);
				$generic->setName($row['name']);
				$generic->setCategory((new ItemCategoryDAO())->get($row['category_id'], $pdo));
				$generic->setDescription($row['description']);
				return $generic;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getGenericByServiceCenter($center_id, $pdo = null)
	{
		$generic = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.*, g.* FROM item_group_sc c LEFT JOIN item_group_data g ON c.group_id=g.item_group WHERE c.service_center_id=$center_id AND g.generic_id IS NOT NULL GROUP BY g.generic_id";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen =  $this->get($row['generic_id'], $pdo);
				if($gen !== null){
					$generic[] = $gen;
				}
			}
		} catch (PDOException $e) {
			errorLog($e);
			$generic = [];
		}
		return $generic;
	}
	
	function getByGroup($g, $pdo = null)
	{
		$gens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE item_group=$g";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen =  $this->get($row['generic_id'], $pdo);
				if($gen !== null){
					$gens[] = $gen;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			error_log($e);
			$gens = [];
		}
		return $gens;
	}
	
	function list_($lastItemId = null, $pdo = null)
	{
		$pageSize = 50;
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($lastItemId !== null) {
				$sql = "SELECT * FROM item_generic WHERE id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";
			} else {
				$sql = "SELECT * FROM item_generic ORDER BY `name`";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen =  $this->get($row['id'], $pdo);
				if($gen !== null){
					$data[] = $gen;
				}
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}
		return $data;
	}
	
	function find($filter, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_generic WHERE `name` LIKE '%$filter%' ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$data = [];
		}
		return $data;
	}
	
	
	
	function get_items_by_generic($gen, $pdo = null)
	{
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item WHERE generic_id=$gen";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item = new Item($row['id']);
				$item->setName($row['name']);
				$items[] = $item;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$items = [];
		}
		return $items;
	}

}