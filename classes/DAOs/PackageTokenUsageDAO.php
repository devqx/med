<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 12:20 PM
 */
class PackageTokenUsageDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_token_usage WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				
				return (new PackageTokenUsage($row['id']))->setPatient($patient)->setItemCode($row['item_code'])->setQuantity($row['quantity'])->setUsedDate($row['use_date'])
					->setResponsible(null) // to save memory
					;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function forPatient($patient_id, $pdo = null)
	{
		if (is_null($patient_id)) {
			return [];
		}
		$data = new ArrayObject();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_token_usage WHERE patient_id=$patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = null; // to save memory, since we already know this patient
				$data[] = (new PackageTokenUsage($row['id']))->setPatient($patient)->setItemCode($row['item_code'])->setQuantity($row['quantity'])->setUsedDate($row['use_date'])
					->setResponsible(null) // to save memory
				;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
}