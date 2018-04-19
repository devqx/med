<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:41 PM
 */
class PhysioItemsRequestDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			@session_start();
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PhysioItemsRequest.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioItemsRequestDataDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if (is_null($id))
			return null;
		$itRequest = new PhysioItemsRequest();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM physiotherapy_items_request WHERE id = " . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$itRequest->setId($row['id']);
				$itRequest->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo));
				$itRequest->setItems((new PhysioItemsRequestDataDAO())->getRequestItems($row['id'], $pdo));
				$itRequest->setRequester((new StaffDirectoryDAO())->getStaff($row['requested_by'], false, $pdo));
				$itRequest->setReceiver((new StaffDirectoryDAO())->getStaff($row['received_by'], false, $pdo));
				$itRequest->setDeliverer((new StaffDirectoryDAO())->getStaff($row['delivered_by'], false, $pdo));
				$itRequest->setStatus($row['status']);
				$itRequest->setRequestTime($row['time_entered']);
				$itRequest->setReceiveTime($row['time_received']);
				$itRequest->setDeliverTime($row['time_delivered']);
				
				$itRequest->setAmount($row['amount']);
				$itRequest->setServiceCentre((new ServiceCenterDAO())->get($row['service_centre_id'], $pdo));
			} else {
				$itRequest = null;
			}
		} catch (PDOException $e) {
			error_log("PDO Error occurred");
		}
		return $itRequest;
	}
	
	function all($status = null, $page = 0, $pageSize = 10, $patientId = null, $opCentre = null, $pdo = null)
	{
		$requests = [];
		$centre = ($opCentre != null) ? " AND service_centre_id=" . $opCentre : '';
		$filter = ($status != null && is_array($status)) ? " WHERE status IN ('" . implode("','", $status) . "')" : " WHERE 1";
		$filter2 = ($patientId != null) ? " AND patient_id=" . $patientId : "";
		$sql = "SELECT * FROM physiotherapy_items_request $filter$filter2$centre";
		
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: Failed to return total number of records");
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$requests[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$requests = [];
		}
		
		$results = (object)null;
		$results->data = $requests;
		$results->total = $total;
		$results->page = $page;
		return $results;
	}
	
	function add($request, $pdo = null)
	{
		// $request = new PhysioItemsRequest();
		$patient_id = $request->getPatient()->getId();
		$patient = (new PatientDemographDAO())->getPatient($patient_id, false, $pdo);
		$requested_by = $request->getRequester()->getId();
		$amount = $request->getAmount();
		$service_centre_id = $request->getServiceCentre()->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$canCommit = !$pdo->inTransaction();
			try {
				$pdo->beginTransaction();
			} catch (PDOException $e) {
			}
			$sql = "INSERT INTO physiotherapy_items_request (patient_id, requested_by, time_entered, amount, service_centre_id) VALUES ($patient_id, $requested_by, NOW(), $amount, $service_centre_id)";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$newItems = [];
			if ($stmt->rowCount() == 1) {
				$request->setId($pdo->lastInsertId());
				foreach ($request->getItems() as $item) {
					//$item = new PhysioItemsRequestData();
					$item->setRequest($request);
					$newItems[] = (new PhysioItemsRequestDataDAO())->add($item, $pdo);
					$bil = new Bill();
					$bil->setPatient($request->getPatient());
					$bil->setDescription($item->getItem()->getName());
					$bil->setItem($item->getItem());
					$bil->setSource((new BillSourceDAO())->findSourceById(20, $pdo));
					$bil->setTransactionType("credit");
					$bil->setAmount($item->getItem()->getBasePrice());
					$bil->setDiscounted(null);
					$bil->setDiscountedBy(null);
					$bil->setClinic(new Clinic(1));
					$bil->setBilledTo($patient->getScheme());
					$costCentre = (is_null($request->getServiceCentre())) ? null : (new ServiceCenterDAO())->get($request->getServiceCentre()->getId(), $pdo)->getCostCentre();
					$bil->setCostCentre($costCentre);
					
					(new BillDAO())->addBill($bil, 1, $pdo);
				}
				
				if (count($request->getItems()) == count($newItems)) {
					if ($canCommit) {
						$pdo->commit();
					}
					return $request;
				}
			}
			if ($canCommit) {
				$pdo->rollBack();
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function updateStatus($request, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE physiotherapy_items_request SET status = '" . $request->getStatus() . "' WHERE id=" . $request->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return $request;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}