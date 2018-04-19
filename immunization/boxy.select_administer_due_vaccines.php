<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$pid = escape($_GET['id']);
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

$patient = new Manager();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
$bill = new Bills();
$pname = $patient->getPatientName($pid);
if (!isset($_SESSION)) {
	session_start();
}
$bills = new Bills();
$pat = (new PatientDemographDAO())->getPatient($pid, FALSE, null, null);

$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

?>
<div style="width: 700px">
	<?php if ($this_user->hasRole($protect->nurse)) { ?>
		<script type="text/javascript">
			function start() {
			}
			function done(s) {
				if (s == 'ok') {
					Boxy.info('Saved !', function () {
						showTabs(1);//then close this dialog,
						Boxy.get($('.close')).hideAndUnload();
					});//and reload this tab,

				} else {
					Boxy.alert(s);
				}
			}
			function take_vaccine() {
				var data_ = $('.cnt:visible > form').serialize();
				Boxy.load('/immunization/ajax.boxy.show_prepared_vaccines.php?data=' + encodeURIComponent(data_), {title: 'Ready Vaccines'});
			}

		</script>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	$pat = (new PatientDemographDAO())->getPatient($pid, FALSE); ?>
		<div class="well">Patient's Outstanding balance: &#8358;<?= number_format($selfOwe, 2) ?> </div>
		<div><i class="icon-info"></i> Please check that the vaccine you want to apply has been prepared</div>
		<div class="cnt" id="cnt0">&nbsp;<br>
			<form id="apply_immunization__" method="post" onsubmit="take_vaccine();return false;">
				<?php if (isset($_GET['direct_access_id'])) {
					echo $patient->showDueVaccinesForPatient($pid, '', false, $_GET['direct_access_id']);
				} else {
					echo $patient->showDueVaccinesForPatient($pid, '', false);
				} ?>
			</form>
		</div>

		<div class="cnt hide" id="cnt1">
			<form id="apply_immunization" method="post" onsubmit="take_vaccine();return false;">
				<span class="notify-bar">*Overdue vaccines are now visible for catch-up</span>
				<?php if (isset($_GET['direct_access_id'])) {
					echo $patient->showDueVaccinesForPatient($pid, '', $enableCatchUp = TRUE, $_GET['direct_access_id']);
				} else {
					echo $patient->showDueVaccinesForPatient($pid, '', $enableCatchUp = TRUE);
				} ?>
			</form>
		</div>

		<label><input type="checkbox" id="showDueVaccinesCheck"/> <em>Include overdue vaccines for
				catch-up</em><span style="margin-left: 10px;" class="removeVaccine"><i class="icon-remove-sign"></i><a href="javascript:;" class="CancelVaccinesCheck">Reverse vaccine(s)</a> </span>
		</label>

		<div>
			<button type="button" onclick="$('.cnt:visible > form').submit()"
			        class="btn" <?= ($selfOwe - $creditLimit > 0 ? ' disabled="disabled"' : "") ?>>Prepare
			</button>
			<button type="button" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel</button>
		</div>

		<?php
	} else {
		echo $protect->ACCESS_DENIED;
	} ?>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('table.catchUpTable').tableScroll({height: 250});
	});
	$('#showDueVaccinesCheck').on('click', function () {
		$("#cnt0, #cnt1").toggleClass('hide');
		$('table.catchUpTable').tableScroll({height: 250});
	});


	$('.CancelVaccinesCheck').click(function (e) {
		if (!e.handled) {
			if ($('input[name="vaccine[]"]:checked').length > 0) {
				if (window.confirm('Are you sure you want to REVERSE the vaccine billing?')) {
					$.post("/api/vaccine_reversal.php", $('.cnt:visible > form').serialize(), function (data) {
						var response = data.split(':');
						if(response[0]=='success'){
							Boxy.info(response[1], function () {
								Boxy.get($('.close')).hideAndUnload();
							});
						} else if(response[0]=='error'){
							Boxy.warn(response[1]);
						}
					});
				}
			} else {
				Boxy.warn("No vaccine selected");
			}
			e.handled = true;
		}

	});


</script>
