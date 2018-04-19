<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/20/14
 * Time: 3:29 PM
 */
class StaffRolesDAO
{

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffRole.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getRole($id, $pdo = null)
	{
		if (is_blank($id)) return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_roles WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$role = new StaffRole();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$role->setId($row['id']);
				$role->setCode($row['code']);
				$role->setDescription($row['description']);
				$stmt = null;
			} else {
				return null;
			}
			return $role;
		} catch (PDOException $e) {
			return null;
		}
	}

	function getRoles($pdo = null)
	{
		$roles = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_roles ORDER BY `code`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$roles[] = $this->getRole($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $roles;
	}

	function getStaffRoles($staff, $pdo = null)
	{
		$roles = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT roles FROM staff_directory WHERE staffId = '$staff'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$r_ = array_filter(explode("|", $row['roles']));
				foreach ($r_ as $r) {
					$roles[] = $this->getRole($r, $pdo);
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			return null;
		}
		return $roles;
	}

	function updateStaffRoles($staff_id, $roles, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AuditLog.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], $pdo);
		$protect = new Protect();
		$return = new stdClass();
		if (!$this_user->hasRole($protect->mgt)) {
			$return->status = "error";
			$return->message = "Access denied";
			return $return;
		}
		$new_roles = array();
		foreach ($roles as $r) {
			$new_roles[] = $r;
		}
		if (sizeof($new_roles) > 0) {
			$new_roles = implode("|", $new_roles);
		} else {
			$new_roles = "NULL";
		}

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$oldRoles = (new StaffDirectoryDAO())->getStaff($staff_id, true, $pdo)->getRoles();
			
			$old = [];
			foreach ($oldRoles as $oldRole) {
				$old[] = $oldRole->getId();
			}
			$old = implode("|", $old);
			$sql = "UPDATE staff_directory SET roles = '$new_roles' WHERE staffId='$staff_id'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 0) {
				$return->status = "ok";
				$return->message = "Roles updated";
				if ($stmt->rowCount() > 0) {
					(new AuditLog())->setObject('staff_directory')->setField('roles')->setObjectId($staff_id)->setOldValue($old)->setNewValue($new_roles)->add($pdo);
				}
				return $return;
			} else {
				$stmt = null;
				$return->status = "error";
				$return->message = "Failed to update roles";
				return $return;
			}
		} catch (PDOException $e) {
			$return->status = "error";
			$return->message = "Database error";
			return $return;
		}
	}

}