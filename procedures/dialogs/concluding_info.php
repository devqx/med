<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/10/15
 * Time: 5:00 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();
$p_p = (new PatientProcedureDAO())->get($_GET['id']);

$insurance = (new InsuranceDAO())->getInsurance($p_p->getPatient()->getId(), true);
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$bills = new Bills();
$pat = (new PatientDemographDAO())->getPatient($p_p->getPatient()->getId(), false, null, null);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;
?>
<section style="width: 600px">
	<div class="well">
		Patient's Outstanding balance <?= number_format($selfOwe,2)?>
	</div>
	<form id="concludingForm">
		<label>Add a concluding medical report
			<textarea name="message" id="concluding_info" rows="10"></textarea>
		</label>
		<div class="btn-block">
			<button class="btn" type="button" onclick="saveConcludingReport()" <?=$selfOwe - $creditLimit > 0? ' disabled':''?>>Save Report</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload(); $('#concluding_info').val('')">Cancel</button>
		</div>
		<input type="hidden" name="id" value="<?= $_GET['id']?>">
		<input type="hidden" name="status" value="close">
	</form>
</section>
<script type="text/javascript">
	function saveConcludingReport() {
		<?php if( $selfOwe - $creditLimit <= 0){?>
		$.post('/api/procedure_action.php', $("#concludingForm").serialize(), function (s) {
			var result = s.split(":");
			if (result[0] === "error") {
				Boxy.alert(result[1]);
			} else if (result[0] === "success") {
				reloadThisPage();
				Boxy.get($(".close")).hideAndUnload();
				Boxy.info(result[1]);
			}
		});
		<?php } else {?>
		Boxy.warn("Patient has an outstanding balance of <?=$selfOwe?>");
		<?php }?>
	}
</script>
