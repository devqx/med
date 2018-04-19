<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 10:05 AM
 */
class ItemGroupDAO
{
	private $conn = null;

	function __construct() {
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroup.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroupData.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	
	function getOrCreate($name, $description, $pdo){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->find($name, $pdo)[0];
			if(!$return == null){
				return $return;
			}else{
				$create =   (new ItemGroup())->setName($name)->setDescription($description)->add($pdo);
				return $create;
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	

	function get($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$Center = new ItemGroupData($row['id']);
				$Center->setGroup($this->getItemGroup($row['item_group'], $pdo));
				$Center->setGeneric((new ItemGenericDAO())->get($row['generic_id'], $pdo));
				return $Center ;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function all($pdo = null)
	{
		$centres = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$centres[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $centres;
	}

	function getItemGroups($lastItemId = null, $pdo = null){
		$data = [];
		$pageSize = 50;
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if($lastItemId !== null){
				$sql = "SELECT * FROM item_group WHERE id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";

			}else{
				$sql = "SELECT * FROM item_group ORDER BY id DESC ";
			}

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->getItemGroup($row['id'], $pdo);
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $data;
	}

	function find($filter, $pdo = null)
	{
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group WHERE `name` LIKE '%$filter%' ORDER BY `name`";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = $this->getItemGroup($row["id"], $pdo);
				$cats[] = $cat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cats = null;
		}
		return $cats;
	}

	function getGroupsByServiceCenter($scid,  $pdo = null)
	{
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_sc WHERE service_center_id=$scid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items[] = $this->getItemGroup($row['group_id']);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$items = null;
		}
		return $items;
	}

	function getItemGroup($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$it = new ItemGroup($row['id']);
				$it->setName($row['name']);
				$it->setDescription($row['description']);
				return $it;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

	function getGroupByGeneric($id, $pdo = null)
	{
		if ($id === null || is_blank($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE generic_id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$Center = new ItemGroupData($row['id']);
				$Center->setGroup($this->getItemGroup($row['item_group'], $pdo));
				return $Center;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}


	function getGenericByGroup($gid, $pdo = null){
		if ($gid == null || is_blank($gid)){
			return null;
		}
		$data = array();
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE item_group=$gid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			//error_log($sql);
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$datum = $this->get($row['id'], $pdo);
				$data[] = $datum;
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}

	function findGroupByGeneric($gen, $ig, $pdo)
	{
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql  = "SELECT * FROM item_group_data WHERE generic_id=$gen AND item_group=$ig";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$Center = new ItemGroupData($row['id']);
				$Center->setGroup($this->getItemGroup($row['item_group'], $pdo));
				$Center->setGeneric((new ItemGenericDAO())->get($row['generic_id'], $pdo));
				return $Center ;
			}
			return false;
		}catch (PDOException $e){
			error_log($e);
			return false;
		}
	}

}