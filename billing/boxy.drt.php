<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/16/17
 * Time: 10:58 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DRTDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

$drts = (new DRTDAO())->all();
$serviceCenters = (new ServiceCenterDAO())->all();
@session_start();
if (isset($_GET['items']) && isset($_SESSION['checked_bill_all'])) {
	$_SESSION['checked_bill_all'][] = explode(',', $_GET['items']);
}

$items = array_flatten($_SESSION['checked_bill_all']);
$_GET['items'] = isset($_SESSION['checked_bill_all']) ? implode(',', $items) : '';

if ($_POST) {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$selectedLines = array_filter(explode(',', $_GET['items']));
	if(is_blank($_POST['drt_id'])){
		exit('error:Select a DRG to apply');
	}
	//these should be only transfer-credits and credits only
	//we've filtered them out from the statement page already unless there's a url `phishing`
	$selectedDrt = (new DRTDAO())->get($_POST['drt_id'], $pdo);
	$patient = (new PatientDemographDAO())->getPatient($_POST['patient_id'], false, $pdo);
	$scheme = $patient->getScheme();
	$charge = (new Bill)->setPatient($patient)->setCostCentre( (new ServiceCenterDAO())->get($_POST['service_center_id'], $pdo)->getCostCentre() )->setDescription($selectedDrt->getName())->setItem($selectedDrt)->setSource((new BillSourceDAO())->findSourceById(25, $pdo))->setTransactionType("credit")->setAmount($selectedDrt->getBasePrice())->setDiscounted(null)->setDiscountedBy(null)->setClinic(new Clinic(1))->setBilledTo($scheme)->setReferral(null)->setCostCentre(null)->add(1, null, $pdo);
	
	foreach ($selectedLines as $selectedLine) {
		$line = (new BillDAO())->getBill($selectedLine, true, $pdo);
		if($line->getTransferred()){
			$pdo->rollBack();
			exit('error:DRG might have been applied to Bill Line ['.$line->getDescription(). '], earlier');
		}
		$bill = (new BillDAO())->getBill($selectedLine, true, $pdo)->setTransferred(TRUE)->update($pdo);
		//$bill->setTransactionType('reversal')->setAmount(0-$bill->getAmount())->setDueDate($bill->getTransactionDate())->add($bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo);
	}
	foreach ($selectedLines as $selectedLine) {
		$bill = (new BillDAO())->getBill($selectedLine, true, $pdo);
		$bill->setParent($charge)->setTransactionType('reversal')->setTransferred(FALSE)->setAmount(0-$bill->getAmount())->setDueDate($bill->getTransactionDate())->add($bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo);
	}
	
	$pdo->commit();
	exit('success:DRG applied successfully');
}

?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: startDrt, onComplete: processedDrtRq})">
		<label>Select DRT Service to apply <select name="drt_id" data-placeholder="Select">
				<option></option><?php foreach ($drts as $drt) { ?>
					<option value="<?= $drt->getId() ?>" data-description="<?= htmlentities($drt->getDescription()) ?>"><?= $drt->getName() ?></option><?php } ?></select> </label>

		<label>Service Center <select name="service_center_id" data-placeholder="Select Service Center" required>
				<option></option>
				<?php foreach ($serviceCenters as $center){?>
					<option value="<?= $center->getId()?>"><?=$center->getName() ?> (<?= $center->getType()?>)</option>
				<?php }?>
			</select></label>
		<p class="fadedText" style="margin-bottom: 50px;" id="x_display"></p>
		<div class="btn-block">
			<button type="submit" class="btn">Apply</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden" name="patient_id" value="<?= $_GET['id'] ?>">
	</form>
</section>
<script type="text/javascript">
	var startDrt = function () {
		$(document).trigger('ajaxSend');
	};

	var processedDrtRq = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.warn(data[1]);
		} else {
			Boxy.get($('.close')).hideAndUnload();
			<?php if(!isset($_GET['aid'])){?>
			showTabs(7);
			<?php } else {?>showTabs(13);<?php }?>
		}
	};
	$(document).on('change', 'select[name="drt_id"]', function (e) {
		if ($(e.target).val() !== '') {
			$('#x_display').html('* ' + $(e.target).find("option:selected").data('description'));
		} else {
			$('#x_display').empty();
		}
	});


</script>
