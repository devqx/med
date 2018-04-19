<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 11:53 AM
 */
class PackageSubscriptionDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PackageSubscription.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $full = false, $pdo = null)
	{
		if (is_null($id)) {
			return null;
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_subscription WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				$package = (new PackageDAO())->get($row['package_id'], $full, $pdo);
				
				return (new PackageSubscription($row['id']))->setPatient($patient)->setPackage($package	)->setDateSubscribed($row['date_subscribed'])->setActive((bool)$row['active'])
					//->setCreateUser(null); forget about this for now
					;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function forPatient($patient_id, $full = false, $pdo = null)
	{
		if (is_null($patient_id)) {
			return [];
		}
		$data = new ArrayObject();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_subscription WHERE patient_id=$patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = null; // to save memory, since we already know this patient
				$package = (new PackageDAO())->get($row['package_id'], $full, $pdo);
				
				$data[] = (new PackageSubscription($row['id']))->setPatient($patient)->setPackage($package)->setDateSubscribed($row['date_subscribed'])->setActive((bool)$row['active'])
					//->setCreateUser(null); forget about this for now
					;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	function all($full = false, $pdo = null)
	{
		//provides an iterface to browse all actively subscribed patients filterable by patient, package, ...
		$data = new ArrayObject();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM package_subscription";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				$package = (new PackageDAO())->get($row['package_id'], $full, $pdo);
				
				$data[] = (new PackageSubscription($row['id']))->setPatient($patient)->setPackage($package)->setDateSubscribed($row['date_subscribed'])->setActive((bool)$row['active'])
					//->setCreateUser(null); forget about this for now
					;
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
}