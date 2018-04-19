<?php

/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/11/15
 * Time: 3:46 PM
 */
class PatientAntenatalUsagesDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	public function addItem($uitem, $pdo = null)
	{
		return $uitem->add($pdo);
		//$uitem = new PatientAntenatalUsages();
		//try {
		//	$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		//	$sql = "INSERT INTO patient_antenatal_usages (aid, patient_id, item_id, item_type, usages)  VALUES " . "('" . $uitem->getAntenatal()->getId() . "', " . $uitem->getPatient()->getId() . ", " . $uitem->getItem() . ", '" . $uitem->getType() . "', '" . $uitem->getUsages() . "')";
		//	$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		//	$stmt->execute();
		//	if ($stmt->rowCount() > 0) {
		//		$uitem->setId($pdo->lastInsertId());
		//	}
		//	$stmt = null;
		//} catch (PDOException $e) {
		//	errorLog($e);
		//	$uitem = null;
		//}
		//return $uitem;
	}
	
	public function removeItem($usedItem, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM patient_antenatal_usages WHERE id=" . $usedItem->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				return true;
			} else {
				return false;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return false;
		}
	}
	
	public function getItemUsed($iusage, $pdo = null)
	{
		$usages = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_antenatal_usages WHERE aid=" . $iusage->getAntenatal()->getId() . " AND patient_id=" . $iusage->getPatient()->getId() . " AND item_id=" . $iusage->getItem() . " AND item_type='" . $iusage->getType() . "'";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$usage = new PatientAntenatalUsages();
				$usage->setId($row['id']);
				$usage->setPatient($row['patient_id']);
				$usage->setAntenatal($row['aid']);
				$usage->setItem($row['item_id']);
				$usage->setType($row['item_type']);
				$usage->setUsages($row['usages']);
				$usage->setDateUsed($row['date_used']);
				$usages[] = $usage;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$usages = [];
			errorLog($e);
		}
		return $usages;
	}
	
	function forPatientItem($pid, $itemCode, $instanceId, $pdo=null){
		try {
			$pdo = $pdo==null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "SELECT item_code, SUM(usages) AS remaining FROM patient_antenatal_usages WHERE patient_id=$pid AND item_code='$itemCode' AND aid=$instanceId AND item_code IS NOT NULL GROUP BY item_code";
			// error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$obj = new stdClass();
				$obj->quantity = $row['remaining'];
				return $obj;
			}
			$obj = new stdClass();
			$obj->quantity = 0;
			return $obj;
		}catch (PDOException $e){
			errorLog($e);
			return [];
		}
	}
	
	public function get($iid, $pdo = null)
	{
		$usage = new PatientAntenatalUsages();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_antenatal_usages WHERE aid=" . $iid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$usage->setId($row['id']);
				$usage->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], false, $pdo));
				$usage->setAntenatal((new AntenatalEnrollmentDAO())->get($row['aid'], false, $pdo));
				//                if($row['item_type']=='Lab'){
				//                    $usage->setItem((new LabDAO())->getLab($row['item_id'], $pdo));
				//                }
				//                else {
				//                    $usage->setItem((new ScanDAO())->getScan($row['item_id'], $pdo));
				//                }
				$usage->setItem($row['item_id']);
				$usage->setType($row['item_type']);
				$usage->setUsages($row['usages']);
				$usage->setDateUsed($row['date_used']);
			} else {
				$usage = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$usage = null;
			errorLog($e);
		}
		return $usage;
	}
}