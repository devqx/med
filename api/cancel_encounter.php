<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/15/16
 * Time: 6:32 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
if (!isset($_SESSION)) {
	@session_start();
}
$response = (object)null;
try {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$encounter = (new EncounterDAO())->get($_POST['id'], false, $pdo);
	$patient = $encounter->getPatient();
	$patientId = $encounter->getPatient()->getId();
	$specializationId = $encounter->getSpecialization()->getId();
	$itemCode = (new StaffSpecializationDAO())->get($specializationId, $pdo)->getCode();
	// this one hard o
	$appointment = "SELECT g.*, a.* FROM appointment a LEFT JOIN appointment_group g ON g.id=a.group_id WHERE g.patient_id=$patientId";
	//cancel the appointment that yielded this,
	//cancel the queue items involved, especially for doctors and nurses
	$appointment2 = "UPDATE appointment a LEFT JOIN appointment_group g ON g.id=a.group_id SET a.status='Cancelled' WHERE a.status in ('Active', 'Scheduled') AND patient_id=$patientId AND DATE(a.start_time) = DATE('" . $encounter->getStartDate() . "')";
	$stmt = $pdo->prepare($appointment2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt->execute();
	unset($stmt);
	$queues = "UPDATE patient_queue SET `status`='Cancelled' WHERE patient_id=$patientId AND specialization_id=$specializationId AND DATE_FORMAT(entry_time, '%Y-%m-%d %H:%i')='" . date('Y-m-d H:i', strtotime($encounter->getStartDate())) . "' AND `status`='Active' AND type IN ('Doctors', 'Nursing')";
	//error_log($queues);
	$stmt = $pdo->prepare($queues, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt->execute();
	unset($stmt);
	
	//cancel the `related` lines for the bill,
	//we cancel the related items first so that we will be able to get it's related lines
	(new BillDAO())->cancelRelatedItems($patientId, $itemCode, $encounter->getStartDate(), $pdo);
	//cancel the bill line for the consultation
	
	/*`OLD METHOD`: $getBill = "SELECT bill_id FROM bills WHERE item_code='$itemCode' AND bill_source_id=3 AND DATE_FORMAT(transaction_date, '%Y-%m-%d %H:%i')='" . date('Y-m-d H:i', strtotime($encounter->getStartDate())) . "' AND patient_id=$patientId AND cancelled_by IS NULL";
	$stmt = $pdo->prepare($getBill, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt->execute();
	while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
		$b2 = (new BillDAO())->getBill($row['bill_id'], true, $pdo);
		(new BillDAO())->cancelConsultationVisit($b2, (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo), TRUE, $pdo);
	}
	unset($stmt);
	*/
	/*`NEW METHOD`:*/
	
	$billTransf = (new BillDAO())->checkBill($encounter->getBill()[0]->getId(), true, $pdo);
	// get bills if transferred for cancellation
	$checkBill = (new BillDAO())->getTransferCreditOnly($encounter->getBill()[0]->getId(), true, $pdo);
	
	
	if ($encounter->getBill()) {
		foreach ($encounter->getBill() as $eBill) {
			//$b2 = (new BillDAO())->getBill($eBill->getId(), true, $pdo);
			
			
			
			if ($billTransf && $checkBill == null){
				$pdo->rollBack();
				//$status =  false;
			}
			(new BillDAO())->cancelConsultationVisit($eBill, (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo), true, $pdo);
		}
	} else {
		//now do the antenatal and package part
		//consider the antenatal scenario first
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
		$activeAntenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($patient->getId(), false, $pdo);
		if ($activeAntenatalInstance !== null) {
			//error_log("HERE is an antenatal patient");
			//if the patient has/is enrolled into antenatal and the package has the items covered
			//yay!!! we know the item
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackageItem.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
			$thisItemCode = $encounter->getSpecialization()->getCode();
			$itemsCodes = [];
			
			$patientTokens = (new AntenatalPackagesDAO())->get($activeAntenatalInstance->getPackage()->getId(), $pdo)->getItems();
			foreach ($patientTokens as $token) {
				//$token = new AntenatalPackageItem();
				$itemsCodes[$token->getItemCode()] = $token->getUsage();
			}
			
			//if(in_array($thisItemCode, $itemsCodes)){
			if (isset($itemsCodes[$thisItemCode])) {
				$billQuantity = (int)1;
				$item_type = getAntenatalItemType($thisItemCode);
				// it's a reversal
				(new PatientAntenatalUsages())->setPatient($patient)->setItemCode($thisItemCode)->setItem($encounter->getSpecialization()->getId())->setType($item_type)->setAntenatal($activeAntenatalInstance)->setUsages(parseNumber(0 - $billQuantity))->setDateUsed(date(MainConfig::$mysqlDateTimeFormat))->add($pdo);
				//$pdo->commit();
				//$response->success = true;
				//$response->message = "Cancelled Encounter";
			}
			$pdo->rollBack();
			$response->error = true;
			$response->message = "Failed to cancel encounter bill line might been transferred, please revers and try again";
		} else {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageTokenUsage.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageTokenUsageDAO.php';
			$thisItemCode = $encounter->getSpecialization()->getCode();
			$itemsCodes = [];
			//$itemTokens = [];
			$patientTokens = (new PackageTokenDAO())->forPatient($patient->getId(), $pdo);
			foreach ($patientTokens as $token) {
				//$token = new PackageToken();
				//$itemTokens[] = array('code'=>$token->getItemCode(),'quantity_left'=>$token->getRemainingQuantity());
				$itemsCodes[] = $token->getItemCode();
			}
			if (in_array($thisItemCode, $itemsCodes)) {
				//we used a token:package
				$itemQuantity = (new PackageTokenDAO())->forPatientItem($thisItemCode, $patient->getId(), $pdo);
				$availableTokenItemQty = $itemQuantity->getRemainingQuantity();
				$billQuantity = 1;
				
				$itemQuantity->setRemainingQuantity($availableTokenItemQty + $billQuantity)->setPatient($patient)->update($pdo);
				(new PackageTokenUsage())->setItemCode($thisItemCode)->setPatient($patient)->setQuantity(0 - $billQuantity)->add($pdo);
				
				//we have reduced token, so we need not charge this patient, exit from this function
				//$pdo->commit();
				//$response->success = true;
				//$response->message = "Cancelled Encounter";
				
			} else if (!in_array($thisItemCode, $itemsCodes)) {
				//$pdo->commit();
				//$response->success = true;
				//$response->message = "Cancelled Encounter";
				//pass b/cos we don't know what to refund
			}
		}
		
	}
	$bill = "UPDATE bills SET cancelled_by=" . $_SESSION['staffID'] . ", cancelled_on=NOW() WHERE item_code='$itemCode' AND bill_source_id=3 AND DATE_FORMAT(transaction_date, '%Y-%m-%d %H:%i')='" . date('Y-m-d H:i', strtotime($encounter->getStartDate())) . "' AND patient_id=$patientId AND cancelled_by IS NULL";
	//then cancel the encounter
	$stmt = $pdo->prepare($bill, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt->execute();
	unset($stmt);
	
	$encounter->setOpen(false)->setCanceled(true)->update($pdo);
	
	$pdo->commit();
	$response->success = true;
	$response->message = "Cancelled Encounter";
} catch (Exception $e) {
	$response->error = true;
	$response->message = "Failed to cancel encounter";
}


exit(json_encode($response));