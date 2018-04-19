<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/21/17
 * Time: 2:05 PM
 */


class ItemGrpScDAO
{


	private $conn = null;

	function __construct() {
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';


			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}


	function findCenterByGroup($cent, $grp, $pdo)
	{
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql  = "SELECT * FROM item_group_sc WHERE group_id=$grp AND service_center_id=$cent";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$grp_ = new ItemGrpSc($row['id']);
				$grp_->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id']));
				$grp_->setItemGroup((new ItemGroupDAO())->getItemGroup($row['group_id']));
				return $grp_;
			}
			return false;
		}catch (PDOException $e){
			error_log($e);
			return false;
		}
	}

	function getGrpSc($id, $pdo = null){
		if ($id == null || is_blank($id)){
			return null;
		}
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_sc WHERE id= $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$grp = new ItemGrpSc($row['id']);
				$grp->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id']));
				$grp->setItemGroup((new ItemGroupDAO())->getItemGroup($row['group_id']));
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
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_sc WHERE group_id= $gid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$grp = new ItemGrpSc($row['id']);
				$grp->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id']));
				return $grp;
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function getOrCreate($grpsc, $pdo){
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$return = $this->findCenterByGroup($grpsc->getServiceCenter()->getId(), $grpsc->getItemGroup()->getId(), $pdo);
			if(!$return == null){
				return $return;
			}else{
				$create =   (new ItemGrpSc())->setServiceCenter($grpsc->getServiceCenter())->setItemGroup($grpsc->getItemGroup())->add($pdo); //->setName($name)->setDescription($description)->add($pdo);
				return $create;
			}
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}

	function getByCenter($scid, $pdo = null){
		if ($scid == null || is_blank($scid)){
			return null;
		}
		$data = array();
		try{
			$pdo  = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_sc WHERE service_center_id=$scid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$data[] = $this->getGrpSc($row['id']);
			}
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			$data = [];
		}
		return $data;
	}


	function getGrpScs($pdo = null){
		$grps = [];
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item_group_sc";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$grps[] = $this->getGrpSc($row['id']);
			}
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
		return $grps;
	}


}