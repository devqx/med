<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/19/16
 * Time: 10:33 PM
 */
class BodyPartDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/BodyPart.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo = NULL)
	{
		if (null === $id || is_blank($id)){
			return NULL;
		}
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM body_part WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new BodyPart($row['id']))->setName($row['name']);
			}
			return null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}


	function all($pdo = NULL)
	{
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = 'SELECT * FROM body_part';
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			 $data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (new BodyPart($row['id']))->setName($row['name']);
			}
			return $data;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
}