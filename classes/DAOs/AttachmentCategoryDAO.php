<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/16
 * Time: 5:27 PM
 */
class AttachmentCategoryDAO
{
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AttachmentCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffRolesDAO.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function all($pdo = null)
	{
		$data = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM attachment_category";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			$data = [];
		}
		return $data;
	}
	
	function get($id, $pdo = null)
	{
		if (is_blank($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM attachment_category WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$roles_raw = array_filter(explode(",", $row['role_ids']));
				$roles = [];
				foreach ($roles_raw as $value){
					$roles[] = (new StaffRolesDAO())->getRole($value, $pdo);
				}
				$item = (new AttachmentCategory($row["id"]))->setName($row["name"])->setRoles($roles_raw)->setRolesFull($roles);
				
				return $item;
			}
			return null;
		} catch (PDOException $e) {
			return null;
		}
	}
}