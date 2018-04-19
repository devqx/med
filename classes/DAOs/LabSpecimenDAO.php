<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/12/14
 * Time: 10:36 AM
 */
class LabSpecimenDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LabSpecimen.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	public function getSpecimen($id, $pdo = null)
	{
		$s = new LabSpecimen();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_specimen WHERE id = $id";

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$s->setId($row['id']);
				$s->setName($row['name']);
			} else {
				$s = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$s = null;
			errorLog($e);
		}
		return $s;
	}

	public function getSpecimens($pdo = null)
	{
		$s = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM lab_specimen ORDER BY `name`";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$s[] = $this->getSpecimen($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$s = null;
			errorLog($e);
		}
		return $s;
	}
	//todo: implement add specimen
} 