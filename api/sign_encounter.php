<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/16
 * Time: 5:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

//update the encounter
$encounter = (new EncounterDAO())->get($_POST['encounter_id'], FALSE, $pdo)->setSignedOn(date("Y-m-d H:i:s", time()))->setSignedBy( new StaffDirectory($_SESSION['staffID']) )->setOpen(FALSE)->update($pdo);

//update the queue
$pq = (new PatientQueueDAO())->getApproximateQueueItem($encounter->getStartDate(), 'Doctors', $encounter->getSpecialization(), $encounter->getPatient(), $pdo);
if($pq){
	$pq->setStatus('Attended');
	$pq->setSeenBy(new StaffDirectory($_SESSION['staffID']));
	if (!is_null($pq->getSpecialization())) {
		$specialtyCode = $pq->getSpecialization()->getCode();
		if(boolval($pq->getFollowUp())){
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialtyCode, $pq->getPatient()->getId(), TRUE, $pdo);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialtyCode, $pq->getPatient()->getId(), TRUE, $pdo);
		}
		$pq->setAmount($price);
	} else {
		$pq->setAmount(0);
	}
	if(!(new PatientQueueDAO())->changeQueueStatus($pq, $pdo)){
		$pdo->rollBack();
		exit('error:Queue failed to update!');
	}

} /*else {
	$pdo->rollBack();
	exit('error:Queue item not found!');
}*/
if($encounter != NULL){
	//commit the transaction
	$pdo->commit();
	ob_end_clean();
	exit('success:Signed Successfully');
}
$pdo->rollBack();
exit('error:Signing Failed.');
