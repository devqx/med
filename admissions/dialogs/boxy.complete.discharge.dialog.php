<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/10/16
 * Time: 2:56 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Admission.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';


$instance = (new InPatientDAO())->getInPatient($_REQUEST['aid'], FALSE);
$patient = (new PatientDemographDAO())->getPatient($instance->getPatient()->getId(), FALSE, null, null);
//$rechargeBills = (new BillDAO())->getUnCompletedDischargeBills($instance->getId(), TRUE, null);
$rechargeBills = (new BillDAO())->getUnCompletedDischargeBillsSlim($instance->getId(), TRUE, null);

if ($_POST) {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$newBill = (new Bill())
		->setPatient($instance->getPatient())
		->setInPatient($instance->getId())
		->setAmount($_POST['amount_all'])
		->setSource((new BillSourceDAO())->findSourceById(5, $pdo))
		->setCostCentre($instance->getWard() ? $instance->getWard()->getCostCentre() : null)
		->setTransactionType("credit")
		->setDescription("Clinical Services Charge")
		->setDiscounted('no')
		->setDiscountedBy(null)
		->setReviewed(TRUE)
		->setBilledTo($patient->getScheme())
		->setItem(null);
	$appointment = $instance->getNextAppointment() ? $instance->getNextAppointment()->getGroup()->getId() : null;
	$medication = $instance->getNextMedication() ? $instance->getNextMedication()->getCode() : null;
	$charge = (new BillDAO())->addBill($newBill, 1, $pdo, $instance->getId());
	$discharge = (new InPatientDAO())->discharge($_REQUEST['aid'], null, $appointment, $medication, $pdo);
	if ($charge !== null && $discharge != null) {
		$pdo->commit();
		exit("ok:Success!");
	} else {
		$pdo->rollBack();
		exit("error:Error placing charge");
	}
}
?>
<div style="width: 850px;">
	<form method="post" action="<?= $_SERVER['SCRIPT_NAME'] ?>"
	      onsubmit="return AIM.submit(this, {onStart: __start(), onComplete: completeTransaction})">
		<div class="well">
			<?= $patient->getFullname() ?>
		</div>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>Bill Description</th>
				<th>Category</th>
				<th>Amount Charged</th>
				<th><label><input onchange="checkAll(this)" type="checkbox"></label></th>
			</tr>
			</thead>
			<?php foreach ($rechargeBills as $b) { //$b=new Bill();?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($b->transaction_date))  ?></td>
					<td><label for="amount<?= $b->bill_id ?>"><?= $b->description ?></label></td>
					<td><label for="amount<?= $b->bill_id ?>"><?= ucwords(str_replace('_',' ',$b->bill_source_name)) ?></label></td>
					<td><label class="amount" for="amount<?= $b->bill_id ?>"><?= $b->amount ?></label></td>
					<td><label><input id="amount<?= $b->bill_id ?>" <?= $b->bill_source_id!=8 ? ' checked':'' ?> type="checkbox" name="amount" value="<?= $b->amount ?>">
						</label></td>
				</tr>
			<?php } ?>
		</table>
		<div class="row-fluid">
			<label class="span8">
				<input type="number" name="percentage" step="any" min="0" max="100" value="<?= $patient->getScheme()->getClinicalServicesRate()?>" style="width: 50px;"> % Charges to Apply
			</label>
			<div id="newBillAmount" class="amount span4 border"></div>
			<input type="hidden" name="amount_all">
			<input type="hidden" name="aid" value="<?= $_REQUEST['aid'] ?>">
		</div>

		<div class="">
			<button type="submit" class="btn ">Completely Discharge</button>
			<button type="button" class="btn-link " onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	var total = 0;
	$(document).on('change', 'input[name="amount"]', function () {
		total = 0;
		$('input[name="amount"]:checked').each(function (index, element) {
			total += parseFloat($(element).val());
			updateAmount();
		});
	}).on('change', 'input[name="percentage"]', function () {
		updateAmount();
	}).ready(function (e) {
		$('input[name="amount"]').trigger('change');
		$('input[name="percentage"]').trigger('change');
		$('.boxy-content input:checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
			updateAmount();
		});
	});

	function updateAmount() {
		var Percentage = $('input[name="percentage"]').val();
		$('#newBillAmount').html(Percentage * total / 100);
		$('input[name="amount_all"]').val(Percentage * total / 100);
	}
	function __start() {
		jQuery.event.trigger("ajaxSend");
	}

	var completeTransaction = function (s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1])
		} else if (data[0] === "ok") {
			Boxy.info(data[1]);
			Boxy.get($(".close")).hideAndUnload()
		}
	};

	var checkAll = function (element) {
		if ($(element).is(":checked")) {
			$('input[name="amount"]').prop('checked', true).trigger('change').iCheck('update');
		} else {
			$('input[name="amount"]').prop('checked', false).trigger('change').iCheck('update');
		}
		jQuery.event.trigger("ajaxStop");
	}
</script>

