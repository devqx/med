<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/3/16
 * Time: 10:26 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/SignatureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Signature.php';
@session_start();
if (isset($_GET['items']) && isset($_SESSION['checked_items_all'])) {
	$_SESSION['checked_items_all'][] = explode(',', $_GET['items']);
}

$items = array_flatten($_SESSION['checked_items_all']);
$_GET['items'] = isset($_SESSION['checked_items_all']) ? implode(',', $items) : '';

$data = (new BillDAO())->getBillsToClaim($_GET['items']);

$schemes = (new BillDAO())->getSchemesOfBills($_GET['items']);
$patient = (new PatientDemographDAO())->getPatient($_GET['id'], false);
$encounters = (new EncounterDAO())->unclaimedForPatient($_GET['id']);
$ip_instances = (new InPatientDAO())->getInActiveUnclaimedInPatient($_GET['id'], false);
$signature = (new SignatureDAO())->getPatientSignature($_GET['id']);
if ($_POST) {
	// if validation passes, then `redirect`
	include_once "boxy.process_claims.php";
	exit;
	// else
	//exit(json_encode(array("error"=>"Something bad happened, Check your submission")));
}
?>
<section id="docu" style="width: 650px;">
	<div class="alert" style="font-size: 100%;display: block;"><i class="icon-info-sign"></i> Please select Bill Lines for
		same Scheme
	</div>
	<form id="startClaimForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: processing_claim, onStart: start_claim})">
		<label>Insurance Scheme <select name="scheme_id"><?php foreach ($schemes as $scheme) { ?>
					<option value="<?= $scheme->id ?>"><?= $scheme->scheme_name ?></option>
				<?php } ?></select> </label>
		<label>Select Encounter <select name="encounter_id" data-placeholder="Select Encounter">
				<option></option>
				<?php foreach ($encounters as $e) {//$e=new Encounter;?>
					<option value="<?= $e->getId() ?>" data-type="op"><?= $e ?> [Out-Patient]</option><?php } ?>
				<?php foreach ($ip_instances as $e) {//$e=new InPatient flat object;?>
					<option value="<?= $e->id ?>" data-type="ip"><?= date(MainConfig::$dateTimeFormat, strtotime($e->date_admitted)) ?>: <?= $e->reason ?>[In-Patient]</option><?php } ?>
			</select></label>
		<input name="encounter_type" type="hidden">
		<input name="patient_id" type="hidden" value="<?= $_GET['id'] ?>">
		<label>Bill Lines</label>
		<table class="table table-striped">
			<thead>
			<tr>
				<th><input title="Select all" type="checkbox" id="check_all"></th>
				<th>Description</th>
				<th>HMO/Scheme</th>
			</tr>
			</thead>
			<?php foreach ($data as $bills) { ?>
				<tr>
					<td>
						<label><input type="checkbox" class="checks" name="lines[]" value="<?= $bills->bill_id ?>" id="b<?= $bills->bill_id ?>"></label>
					</td>
					<td><label for="b<?= $bills->bill_id ?>"><?= $bills->description ?></label></td>
					<td><label for="b<?= $bills->bill_id ?>"><?= $bills->scheme_name ?></label></td>
				</tr>
			<?php } ?>
		</table>
		<p></p>
		<label>Reason <textarea required name="reason" class="wide"></textarea> </label>

		<div class="btn-block">
			<button type="submit" class="btn">CONTINUE</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">CANCEL</button>
            <?php if ($signature !== NULL){?>
               <span class="pull-right"> <label><input name="signature" value="<?= $signature->getId() ?>" type="checkbox"> Include available signature here!</label></span>
            <?php }?>

		</div>

	</form>
	<script type="text/javascript">
		function start_claim() {
			$('#startClaimForm').parent().parent().block({
				message: '<div class="ball"></div>',
				css: {
					borderWidth: '0',
					backgroundColor: 'transparent'
				}
			});
		}

		function processing_claim(s) {
			var area = $('#startClaimForm').parent();
			area.parent().unblock();
			try {
				if (JSON.parse(s).error !== "undefined") {
					Boxy.alert(JSON.parse(s).error)
				}
			} catch (except) {
				area.html($(s).filter('section').html());
			}
		}

		if (typeof window.jQuery !== "undefined") {
			$(document).on('change', '#check_all', function (e) {
				if ($(this).is(":checked")) {
					$('input.checks[id*=b]:checkbox').prop('checked', true).iCheck('update');
				} else {
					$('input.checks[id*=b]:checkbox').prop('checked', false).iCheck('update');
				}
			}).on('change', 'select[name="encounter_id"]', function (e) {
				if($(e.target).find("option:selected").data("type")){
					$('textarea[name="reason"]').prop('required', false);
					$('input[name="encounter_type"]').val($(e.target).find("option:selected").data("type"));
				} else {
					$('textarea[name="reason"]').prop('required', true);
				}
			}).ready(function () {
				$('select[name="encounter_id"]').trigger('change');
			});
		}

	</script>
</section>
