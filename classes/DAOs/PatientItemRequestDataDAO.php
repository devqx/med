<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/7/17
 * Time: 10:41 AM
 */
class PatientItemRequestDataDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequestData.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			error_log("ERROR: " . $e->getMessage());
		}
	}
	
	function getByCode($iCode, $getFull = false, $pdo = null)
	{
		
		$pds = array();
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_item_request_data WHERE group_code='" . $iCode . "'";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd = new PatientItemRequestData();
				$pd->setId($row['id']);
				$pd->setGroupCode($row['group_code']);
				$pd->setBatch($row['batch_id'] != "NULL" ? (new ItemBatchDAO())->getBatch($row['batch_id'], $pdo) : null);
				
				if ($getFull) {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$gen = (new ItemGenericDAO())->get($row['generic_id'], $pdo);
					$hosp = (new ClinicDAO())->getClinic(1, false, $pdo);
				} else {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$gen = (new ItemGenericDAO())->get($row['generic_id'], $pdo);
					$hosp = new Clinic($row['hospid']);
				}
				$pd->setQuantity($row['quantity']);
				$pd->setFilledQuantity($row['filled_qty']);
				$pd->setItem($it);
				$pd->setGeneric($gen);
				$pd->setStatus($row['status']);
				$pd->setFilledDate($row['filled_date']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], true, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelledNote($row['cancelled_note']);
				$pd->setHospId($hosp);
				$pds[] = $pd;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pds = [];
		}
		return $pds;
	}
	
	
	function getRequestDatum($id, $getFull = false, $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_item_request_data WHERE id= $id ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$pd = new PatientItemRequestData();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd->setId($row['id']);
				$pd->setGroupCode($row['group_code']);
				$pd->setBatch($row['batch_id'] != "NULL" ? (new ItemBatchDAO())->getBatch($row['batch_id'], $pdo) : null);
				
				if ($getFull) {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hosp_id'], false, $pdo);
				} else {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = new Clinic($row['hosp_id']);
				}
				$pd->setQuantity($row['quantity']);
				$pd->setItem($it);
				$pd->setStatus($row['status']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], true, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelledNote($row['cancelled_note']);
				$pd->setHospId($hosp);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pd = null;
		}
		return $pd;
	}
	
	function getRequestDatumByCode($code, $getFull = false, $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_item_request_data WHERE group_code='" . $code . "' ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$pd = new PatientItemRequestData();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd->setId($row['id']);
				$pd->setGroupCode($row['group_code']);
				$pd->setBatch($row['batch_id'] != "NULL" ? (new ItemBatchDAO())->getBatch($row['batch_id'], $pdo) : null);
				
				if ($getFull) {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hosp_id'], false, $pdo);
				} else {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = new Clinic($row['hosp_id']);
				}
				$pd->setQuantity($row['quantity']);
				$pd->setItem($it);
				$pd->setFilledDate($row['filled_date']);
				$pd->setStatus($row['status']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], true, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelledNote($row['cancelled_note']);
				$pd->setHospId($hosp);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pd = null;
		}
		return $pd;
	}
	
	function getRequestDatumById($id, $getFull = false, $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_item_request_data WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$pd = new PatientItemRequestData();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pd->setId($row['id']);
				$pd->setGroupCode($row['group_code']);
				$pd->setBatch($row['batch_id'] != "NULL" ? (new ItemBatchDAO())->getBatch($row['batch_id'], $pdo) : null);
				
				if ($getFull) {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = (new ClinicDAO())->getClinic($row['hosp_id'], false, $pdo);
				} else {
					$it = (new ItemDAO())->getItem($row['item_id'], $pdo);
					$hosp = new Clinic($row['hosp_id']);
				}
				$pd->setQuantity($row['quantity']);
				$pd->setFilledQuantity($row['filled_qty']);
				$pd->setItem($it);
				$pd->setStatus($row['status']);
				$pd->setCancelledBy((new StaffDirectoryDAO())->getStaff($row['cancelled_by'], true, $pdo));
				$pd->setCancelledOn($row['cancelled_on']);
				$pd->setCancelledNote($row['cancelled_note']);
				$pd->setFilledDate($row['filled_date']);
				$pd->setHospId($hosp);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pd = null;
		}
		return $pd;
	}
	
	function cancelRequestData($pres, $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			if ($pres->getCancelledBy() == null)return false;
			$staff_id = $pres->getCancelledBy()->getId();
			$staff = (new StaffDirectoryDAO())->getStaff($staff_id, false, $pdo);
			$parent_pres = (new PatientItemRequestDAO())->getRequestItem($pres->getGroupCode(), false, $pdo);
			$patient = $parent_pres->getPatient();
			$cancelReason = !is_blank($pres->getCancelledNote()) ? quote_esc_str($pres->getCancelledNote()) : "NULL";
			$status = false;
			$sql = "UPDATE patient_item_request_data SET status='cancelled', cancelled_on=NOW(), cancelled_by='" . $pres->getCancelledBy()->getId() . "', cancelled_note=$cancelReason WHERE /* `status` != 'completed'  AND */ id = " . $pres->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$bill = null;
			if ($pres->getItem() != null) {
				$qty = $pres->getFilledQuantity() ? $pres->getFilledQuantity() : '0';
				$item_price =  (new InsuranceItemsCostDAO())->getItemPriceByCode($pres->getItem()->getCode(), $patient->getId(), true, $pdo);
				$amount = $item_price * $qty;
				$patient = (new PatientDemographDAO())->getPatient($patient->getId(), false, $pdo);
				$pat = (new PatientItemRequestDAO())->getItemByCode_($pres->getGroupCode(), FALSE, $pdo)->getPatient();
				
				
				if ($pres->getBatch()) {
					$dispense = (new ItemDAO())->dispenseItem($pres->getItem(), $pres->getFilledQuantity(), $pres->getBatch(), $pat, $type="reversal", $pdo);
					$BATCH_DAO = new ItemBatchDAO();
					$batch = $BATCH_DAO->getBatch($pres->getBatch()->getId(), $pdo);
					$batch->setQuantity($qty);
					$BATCH_DAO->stockUp($batch, $pdo);
					$bill_line = (new BillDAO())->cancelRelatedItems($patient->getId(), $pres->getItem()->getCode(), $pres->getFilledDate(), $pdo);
					
					if($bill_line) {
						$bil = new Bill();
						$bil->setPatient($patient);
						$bil->setDescription("Item request Cancellation: " . (($pres->getItem() != null) ? $pres->getItem()->getName() : $pres->getItem()->getName()));
						$bil->setItem($pres->getItem());
						$bil->setSource((new BillSourceDAO())->findSourceById(11, $pdo));
						$bil->setTransactionType("reversal");
						$bil->setTransactionDate(date("Y-m-d H:i:s"));
						$bil->setDueDate($pres->getFilledDate());
						$bil->setAmount(0 - $amount);
						$bil->setDiscounted(null);
						$bil->setCancelledBy($pres->getCancelledBy());
						$bil->setCancelledOn(date("Y-m-d H:i:s"));
						$bil->setActiveBill('not_active');
						$bil->setDiscountedBy(null);
						$bil->setClinic($staff->getClinic());
						$bil->setBilledTo($patient->getScheme());
						$bil->setParent($bill_line);
						$bil->setCostCentre((new PatientItemRequestDAO())->getRequestItem($pres->getGroupCode(), false, $pdo)->getServiceCenter() !== null ? (new PatientItemRequestDAO())->getRequestItem($pres->getGroupCode(), false, $pdo)->getServiceCenter()->getCostCentre() : null);
						
						$bill = (new BillDAO())->addBill($bil, $qty, $pdo, null);
					}else{
						$pdo->rollBack();
						$status = false;
					}
				}
			}
			if ($stmt->rowCount() <= 0 || $bill == null) {
				error_log("Couldn't cancel item request");
				$pdo->rollBack();
			} else {
				$status = true;
				$pdo->commit();
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
		}
		return $status;
	}
	
	
	function fillRequest($req, $pdo = null)
	{
		//$dispense = null;
		//$result = FALSE;
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE patient_item_request_data SET  `status` ='filled',item_id='" . $req->getItem()->getId() . "',  batch_id ='" . $req->getBatch()->getId() . "', filled_qty='" . $req->getFilledQuantity() . "', filled_date=NOW(), filled_by='" . $req->getFilledBy() . "' WHERE `status`='open' AND id = " . $req->getId();
			$pat = (new PatientItemRequestDAO())->getItemByCode_($req->getGroupCode(), FALSE, $pdo)->getPatient();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($req->getBatch() != null) {
				$dispense = (new ItemDAO())->dispenseItem($req->getItem(), $req->getFilledQuantity(), $req->getBatch(), $pat, $type="fill", $pdo);
				
			} else{
				$result = FALSE;
			}
			
			if ($req->getBatch() != null &&  $dispense != false && $stmt->rowCount() == 1) {
				$result = TRUE;
			}
		} catch (PDOException $e) {
			error_log("Error: " . $e->getMessage());
			$result = FALSE;
		}
		return $result;
	}
	
	
	function completeRequestData($pres, $pdo = null)
	{
		try {
			$pdo = ($pdo === null) ? $this->conn->getPDO() : $pdo;
			if ($pres->getCompletedBy() == null)
				return false;
			$status = false;
			$sql = "UPDATE patient_item_request_data SET `status`='completed', completed_on=NOW(), completed_by='" . $pres->getCompletedBy()->getId() . "' WHERE `status` = 'filled' AND group_code = '" . $pres->getGroupCode() . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() >= 1) {
				$status = true;
			}
			
		} catch (PDOException $e) {
			$status = false;
			errorLog($e);
		}
		return $status;
	}
}
