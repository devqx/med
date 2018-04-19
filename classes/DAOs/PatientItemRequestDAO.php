<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/7/17
 * Time: 10:35 AM
 */
class PatientItemRequestDAO
{

	private $conn = null;

	function __construct()
	{
		try{
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequest.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequestData.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDataDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
			$this->conn = new MyDBConnector();
		}catch (PDOException $e){
			exit('ERROR: '. $e->getMessage());
		}
	}
	
	
	function getAllItems($page, $pageSize, $center=null, $getFull = FALSE, $patientId=null, $inpatientId=null, $statusFilter=null, $pdo = null){

		$filter = ($center != null ? " AND i.service_center_id=$center" : "");
		$statusFilter = $statusFilter != null ? "AND d.status IN('". $statusFilter . "')" : "";
		$patient = ((!is_null($patientId) ? $patientId : null));
		$inpatient = ((!is_null($inpatientId) ? $inpatientId : null));
		$extraFilter = $patient != null ? "AND i.patient_id = " . $patient : "";
		$extraFilter2 = $inpatient != null ? "AND i.inpatient_id=" . $inpatient : "";
		$sql = "SELECT i.* FROM patient_item_request i LEFT JOIN patient_item_request_data d ON i.group_code=d.group_code LEFT JOIN patient_demograph pd ON pd.patient_ID=i.patient_id WHERE pd.active IS TRUE $extraFilter $extraFilter2 $statusFilter $filter GROUP BY i.group_code ORDER BY i.requested_date DESC";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			error_log("ERROR: ". $e->getMessage());
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item = new PatientItemRequest($row['id']);

				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
//					$hosp = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
//					$hosp = new Clinic($row['hospid']);
				}
				$item->setPatient($pat);
				$item->setRequestDate(date("c", strtotime($row['requested_date'])));
				$item->setCode($row['group_code']);
				$item->setRequestedBy($req);
				$item->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));

				$item->setInPatient((new InPatientDAO())->getInPatient($row['inpatient_id'], false, $pdo));
				$item->setRequestNote($row['note']);
				//$pres->setHospital($hosp);
				$item->setData((new PatientItemRequestDataDAO())->getByCode($row['group_code'], TRUE,   $pdo));
				$items[] = $item;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$items = [];
		}
		$results = (object)null;
		$results->data = $items;
		$results->total = $total;
		$results->page = $page;
		unset($_SESSION['pid']);
		return $results;
	}

	function getRequestItemByCode($code, $getFull = FALSE, $pdo = null)
	{
		try{
			$pdo = $pdo == null  ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_item_request WHERE group_code='". $code ."'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
				$it_re = $this->getRequestItem($row['id'], $getFull, $pdo);
			}else{
				$it_re = null;
			}
			$stmt = null;
		}catch (PDOException $e){
			$it_re = null;
		}
		return $it_re;
	}

  function getItemByCode_($code, $getFull = FALSE, $pdo = null ){

	  try {
		  $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $sql = "SELECT * FROM patient_item_request WHERE group_code='" . $code . "'";
		  $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		  $stmt->execute();
		  if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			  $re = $this->getRequest($row['id'], $getFull, $pdo);
		  } else {
			  $re = null;
		  }
		  $stmt = null;
	  } catch (PDOException $e) {
		  $re = null;
	  }
	  return $re;
  }
	function getItemsByCode($code, $getFull = FALSE, $pdo = null ){

	  try {
		  $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $sql = "SELECT * FROM patient_item_request WHERE group_code='" . $code . "'";
		  $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		  $stmt->execute();
		  $re = [];
		  if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			  $re[] = $this->getRequest($row['id'], $getFull, $pdo);
		  } else {
			  $re = [];
		  }
		  $stmt = null;
	  } catch (PDOException $e) {
		  $re = [];
	  }
	  return $re;
  }

  function getItemsForProcedure($pro, $getFull = FALSE, $pdo = null ){

	  try {
		  $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $sql = "SELECT * FROM patient_item_request WHERE procedure_id=$pro";
		  $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		  $stmt->execute();
		  $re = [];
		  while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			  $re[] = $this->getRequest($row['id'], $getFull, $pdo);
		  }
		  $stmt = null;
	  } catch (PDOException $e) {
		  $re = [];
	  }
	  return $re;
  }


   function getRequest($id, $getFull = FALSE, $pdo)
   {
	   $pres = new PatientItemRequest();
	   try {
		   $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		   $sql = "SELECT * FROM patient_item_request WHERE id=" . $id;
		   $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		   $stmt->execute();
		   if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			   $pres->setId($row['id']);
			   if ($getFull) {
				   $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
				   $req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
				   $inpatient = (new InPatientDAO())->getInPatient($row['inpatient_id'], false, $pdo);
			   } else {
           $pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
           $req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
           $inpatient = (new InPatientDAO())->getInPatient($row['inpatient_id'], false, $pdo);
			   }
			   $pres->setPatient($pat);
			   $pres->setRequestDate(date("c", strtotime($row['requested_date'])));
			   $pres->setCode($row['group_code']);
			   $pres->setRequestedBy($req);
			   $pres->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));
			   $pres->setInPatient($inpatient);
			   $pres->setRequestNote($row['note']);

			   $pres->setData((new PatientItemRequestDataDAO())->getByCode($row['group_code'], TRUE, $pdo));
		   } else {
			   $pres = null;
		   }
		   $stmt = null;
	   } catch (PDOException $e) {
		   $pres = null;
	   }
	   return $pres;
   }

	function getRequestItem($code, $getFull = FALSE, $pdo = null)
	{
		$item_req = new PatientItemRequest();
	  try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $sql = "SELECT * FROM patient_item_request WHERE group_code= '". $code ."' " ;
		  $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		  $stmt->execute();
		  if ($row = $stmt->fetch(PDO::FETCH_NAMED,  PDO::FETCH_ORI_NEXT)){
			  $item_req->setId($row['id']);
			  if($getFull){
				  $item_req->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo));
				  $item_req->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo));
				  $item_req->setInpatient((new InPatientDAO())->getInPatient($row['inpatient_id'], FALSE, $pdo));
			  }else{
				  $item_req->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo));
				  $item_req->setRequestedBy((new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo));
				  $item_req->setInpatient((new InPatientDAO())->getInPatient($row['inpatient_id'], FALSE, $pdo));
			  }
				$item_req->setCode($row['group_code']);
			  $item_req->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));
			  $item_req->setRequestNote($row['note']);
			  $item_req->setRequestDate(date("c", strtotime($row['requested_date'])));

			  $item_req->setData((new PatientItemRequestDataDAO())->getRequestDatumByCode($row['group_code'], TRUE, $pdo));
		  }else{
			  $item_req = null;
		  }
     $stmt = null;
	  }catch (PDOException $e){
			error_log('ERROR: '. $e->getMessage());
	  }
		return $item_req;
	}

	function findItemRequests($filter, $start = null, $stop = null, $page, $pageSize, $getFull = FALSE, $patientId = null, $pdo = null)
	{
		$filter = escape($filter);

		if ($start == null) {
			$dateStart = '1970-01-01';
		} else {
			$dateStart = date("Y-m-d", strtotime($start));
		}
		if ($stop == null) {
			$dateStop = date("Y-m-d");
		} else {
			$dateStop = date("Y-m-d", strtotime($stop));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($dateStart, $dateStop), max($dateStart, $dateStop)];
		}
		$filter1 = !is_blank($filter) ? ' AND r.group_code = "' . $filter . '"' : '';
		$filter2 = !is_blank($patientId) ? " AND r.patient_id=" . escape($patientId) : '';
		$sql = "SELECT r.* FROM patient_item_request r LEFT JOIN patient_item_request_data d ON r.group_code=d.group_code LEFT JOIN patient_demograph dm ON r.patient_id = dm.patient_ID WHERE DATE(r.requested_date) BETWEEN '$dateStart' AND '$dateStop'{$filter2}{$filter1} GROUP BY r.group_code";

		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
			error_log("ERROR: Failed to return total number of records");
		}

		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		$ress = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " ORDER BY r.requested_date DESC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			error_log('PAGE. '. $sql);
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$res = new PatientItemRequest();
				$res->setId($row['id']);
				if ($getFull) {
					$pat = (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo);
					$req = (new StaffDirectoryDAO())->getStaff($row['requested_by'], FALSE, $pdo);
				} else {
					$pat = new PatientDemograph($row['patient_id']);
					$req = new StaffDirectory($row['requested_by']);
				}
				$res->setPatient($pat);
				$res->setRequestDate(date("c", strtotime($row['requested_date'])));
				$res->setCode($row['group_code']);
				$res->setRequestedBy($req);
				$res->setServiceCenter((new ServiceCenterDAO())->get($row['service_center_id'], $pdo));
				$res->setInPatient((new InPatientDAO())->getInPatient($row['inpatient_id'], false, $pdo));
				$res->setData((new PatientItemRequestDataDAO())->getRequestDatumByCode($row['group_code'], TRUE, $pdo));
				$ress[] = $res;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$ress = [];
		}
		$results = (object)null;
		$results->data = $ress;
		$results->total = $total;
		$results->page = $page;

		return $results;
	}
}