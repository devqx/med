<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/11/17
 * Time: 4:59 PM
 */
class AppointmentResourceDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Resource.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AppointmentResource.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_resource WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				return (new AppointmentResource($row['id']))->setResource( (new ResourceDAO())->getResource($row['resource_id'], $pdo) );
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function getForGroup($groupId, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM appointment_resource WHERE group_id=$groupId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$resources = [];
			while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$resources[] = $this->get($row['id'], $pdo);
			}
			return $resources;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}