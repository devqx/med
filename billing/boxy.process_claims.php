<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/4/16
 * Time: 5:23 PM
 */
unset($_SESSION['checked_items_all']);
unset($_SESSION['checked_items']);
?>
<section style="width: 600px;">
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Claim.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ClaimLines.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Signature.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	
	if (!isset($_SESSION)) {
		@session_start();
	}
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	//get the signature
	$signature = !is_blank(@$_POST['signature']) ? @$_POST['signature'] : 0;
	
	
	//get the encounter
	$encounter = null;
	if ($_POST['encounter_type'] == "op") {
		$encounter = (new EncounterDAO())->get($_POST['encounter_id'], false, $pdo);
	} else if ($_POST['encounter_type'] == "ip") {
		$encounter = (new InPatientDAO())->getInPatient($_POST['encounter_id'], false, $pdo);
		$encounter1 = (new InPatientDAO())->getInPatient($_POST['encounter_id'], false, $pdo);
	}
	
	if ($encounter && $encounter->getClaimed()) {
		$pdo->rollBack();
		exit('<div class="warning-bar">Error: Encounter has been claimed already</div></section>');
	}
	//instantiate a claims object
	//$claim = (new Claim())->setReason($_POST['reason'])->setScheme(new InsuranceScheme($_POST['scheme_id']))->setLines($lines)->setEncounter($encounter)->setCreateUser(new StaffDirectory($_SESSION['staffID']))->setType($_POST['encounter_type'])->setPatient(new PatientDemograph($_POST['patient_id']))->setSignature(new Signature($signature));
	$claim = (new Claim())->setDateAdmitted($encounter1 ? $encounter1->getDateAdmitted() : '')->setDateDischarged($encounter1 ? $encounter1->getDateDischarged() :'')->setReason($_POST['reason'])->setScheme(new InsuranceScheme($_POST['scheme_id']))->setEncounter($encounter)->setCreateUser(new StaffDirectory($_SESSION['staffID']))->setType($_POST['encounter_type'])->setPatient(new PatientDemograph($_POST['patient_id']))->setSignature(new Signature($signature));
	
	
	//then mark the encounter as `claimed`
	
	if ($encounter) {
		if ($_POST['encounter_type'] == "op" && $encounter->setClaimed(true)->update($pdo) == null) {
			$pdo->rollBack();
			exit('<div class="warning-bar">Error: Failed to claim the Out-Patient Encounter</div></section>');
		} else if ($_POST['encounter_type'] == "ip" && (new InPatientDAO())->claimIpEncounter($_POST['encounter_id'], $pdo) == null) {
			$pdo->rollBack();
			exit('<div class="warning-bar">Error: Failed to claim the In-Patient Encounter</div></section>');
		}
	}
	$claimReturn = (new ClaimDAO())->add($claim, $pdo);
	
	
	//get the signature
	$signature = !is_blank(@$_POST['signature']) ? @$_POST['signature'] : 0;
	//get the lines
	$_lines = !is_blank(@$_POST['lines']) ? array_filter(@$_POST['lines']) : [];
	$lines = [];
	
	$totalCharge = 0;
	
	
	foreach ($_lines as $line) {
		//mark each bill line as `claimed`
		$bill = (new BillDAO())->getBill($line, true, $pdo);
		if ($bill->getClaimed()) {
			$pdo->rollBack();
			exit('<div class="warning-bar">Error: One or more bills have already been claimed</div></section>');
		}
		$b = $bill->setClaimed(true)->update($pdo);
		$lines[] = (int)$line;
		if ($b == null) {
			$pdo->rollBack();
			exit('<div class="warning-bar">Error: Transaction failed midway</div></section>');
		}
		$totalCharge += $bill->getAmount();
		// create claim line object in the loop
		if((new ClaimLines())->setBillLine($bill)->setAmount($bill->getAmount())->setClaim($claimReturn)->add($pdo)  == null){
			$pdo->rollBack();
			exit('<div class="warning-bar">Error: Could not create claim lines</div></section>');
		}
		
	}
	
	if($claim->setTotalCharge($totalCharge)->setBalance($totalCharge)->setTotalPayment(0)->update($pdo) == null){
		$pdo->rollBack();
		exit('<div class="warning-bar">Error: Failed to update claim charges</div></section>');
		
	}
 
	if ($claimReturn != null) {
		$pdo->commit();
		(new Signature())->MarkUsedSignature($signature);
		exit('<div class="notify-bar">Success: Claims generated <a target="_blank" href="/billing/claims_sheet.php?id=' . $claim->getId() . '&type=' . $_POST['encounter_type'] . '">Print</a></div></section>');
	} else {
		$pdo->rollBack();
		exit('<div class="warning-bar">Error: Failed to generate claim document</div></section>');
	}
	
	
	?>
</section>
