<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/10/16
 * Time: 12:12 PM
 */
class EggCollectionFollicleDataDAO
{
	private $conn = null;

	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/EggCollectionFollicleData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/FollicleSizeDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function forInstance($id, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM ivf_egg_collection_follicle_data WHERE egg_collection_id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new EggCollectionFollicleData($row['id']))->setValue($row['value'])->setSize( (new FollicleSizeDAO())->get($row['size_id'], $pdo) );
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}