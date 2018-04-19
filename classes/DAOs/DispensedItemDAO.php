<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/22/18
 * Time: 10:39 PM
 */

class DispensedItemDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DispensedItems.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $ex) {
		
		}
	}
	
	
	function add($item, $pdo = null){
		
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$serviceCenter = (new ItemBatchDAO())->getBatch($item->getBatch()->getId(), $pdo)->getServiceCenter();
			$type = !is_blank($item->getType()) ? quote_esc_str($item->getType()) : '';
			if($type != '' && $type === "'reversal'"){
				$qty = 0 - $item->getQuantity();
			}else{
				$qty = $item->getQuantity();
			}
			$sql = "INSERT INTO dispensed_items SET item_id = " . $item->getItem()->getId() . ", batch_id= " . $item->getBatch()->getId() . ", patient_id = '" . $item->getPatient()->getId() . "', quantity = " . $qty . ", unfilled_quantity = " . $item->getUnfiiledQuantity() . ", billed_to=" . $item->getPatient()->getScheme()->getId() . ", dispensed_date=NOW(), staff_id = " . $item->getDispensedBy()->getId() . ", service_center_id=" . $serviceCenter->getId().", transaction_type=$type";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
		
		return $item;
	}
	
	
	function get($id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM dispensed_items WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new DispensedItems($row['id']))->setItem((new ItemDAO())->getItem($row['item_id'], $pdo))->setBatch((new ItemBatchDAO())->getBatch($row['batch_id'], $pdo))//->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo))
				->setQuantity($row['quantity'])//->setBilledTo((new InsuranceSchemeDAO())->get($row['billed_to'], false, $pdo))
				->setDispensedDate($row['dispensed_date'])->setDispensedBy((new StaffDirectoryDAO())->getStaff($row['staff_id'], false, $pdo))->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($page = 0, $pageSize = 10, $from = null, $to = null, $pdo = null)
	{
		if ($from == null) {
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($from));
		}
		if ($to == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($to));
		}
		
		if (isset($from, $to)) {
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		
		$disps = [];
		$total = 0;
		
		try {
			$sql = "SELECT SUM(quantity) AS quantity, item_id FROM dispensed_items WHERE DATE(dispensed_date) BETWEEN DATE('$dateStart') AND DATE('$dateStop') GROUP BY item_id";
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
				$report = [];
				$report['item'] = (new ItemDAO())->getItem($row['item_id'], TRUE, $pdo);
				$report['quantity'] = $row['quantity'];
				
				$disps[] = $report;
				//$disps[] = $this->get($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$disps = [];
		}
		
		$results = (object)null;
		$results->data = $disps;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
}