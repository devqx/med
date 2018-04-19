<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 12:11 PM
 */
class PackageTokenDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PackageToken.php';
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
			$sql = "SELECT * FROM package_token WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				
				return (new PackageToken($row['id']))->setPatient($patient)->setItemCode($row['item_code'])->setOriginalQuantity($row['original_quantity'])->setRemainingQuantity($row['quantity_left']);
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
			$sql = "SELECT * FROM package_token WHERE patient_id=$patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = null; // to save memory, since we already know this patient
				$data[] = (new PackageToken($row['id']))->setPatient($patient)->setItemCode($row['item_code'])->setOriginalQuantity((int)$row['original_quantity'])->setRemainingQuantity((int)$row['quantity_left']);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function forPatientItem($itemCode, $patient_id, $pdo = null)
	{
		if (is_null($patient_id)) {
			return null;
		}
		$itemCode = quote_esc_str($itemCode);
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_token WHERE patient_id=$patient_id AND item_code=$itemCode";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = null; // to save memory, since we already know this patient
				return (new PackageToken($row['id']))->setPatient($patient)->setItemCode($row['item_code'])->setOriginalQuantity((int)$row['original_quantity'])->setRemainingQuantity((int)$row['quantity_left']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}

}