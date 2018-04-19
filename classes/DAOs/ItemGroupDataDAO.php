<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/25/17
 * Time: 10:19 PM
 */
class ItemGroupDataDAO
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
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}


	function get($id, $pdo = null){
		if ($id == null || is_blank($id)){
			return null;
		}
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE id= $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$grp = new ItemGroupData($row['id']);
				$grp->setGroup((new ItemGroupDAO())->getItemGroup($row['item_group']));
				$grp->setItem((new ItemDAO())->getItem($row['item']));
				return $grp;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}


	function getByGroup($gid, $pdo = null){
		if ($gid == null || is_blank($gid)){
			return null;
		}
		$data = array();
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_data WHERE `item_group`=$gid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->get($row['id']);
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}


}