<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 7:35 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientMedicalReportNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientMedicalReport.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ExamReportingTemplateDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';

$allScans = (new ScanDAO())->getScans();
$reportingTpls = (new ExamReportingTemplateDAO())->all();

if (isset($_GET['id'])) {
	$noteID = $_GET['id'];
	$getNote = (new PatientMedicalReportNoteDAO())->get($noteID);
} else {
	$noteID = '';
}

$bills = new Bills();
$request = (new PatientMedicalReportDAO())->get($_GET['request_id']);
$pat = (new PatientDemographDAO())->getPatient($request->getPatient()->getId(), FALSE, null, null);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	if ($noteID == '') {
		exit("error:Select note to edit");
	}

	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	if ($selfOwe - $creditLimit > 0) {
		exit("error:Patient has outstanding credit");
	}
	if (!is_blank($_POST['report_content'])) {
		$note = (new PatientMedicalReportNoteDAO())->get($noteID)->setNote($_POST['report_content'])->update();
		if($note !== null){
			exit("success:Note updated");
		}
		exit("error:Failed to update note");
	} else {
		exit("error:Note is blank");
	}
}
?>
<section style="width: 730px">
	<div class="well">
		Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {onComplete: completed})">
		<h6>Note</h6>
		<label>Template <select name="template" id="template_selecter" data-placeholder="--select imaging template--">
				<option></option>
				<?php foreach ($reportingTpls as $tpl) { ?>
					<option value="<?= $tpl->getId() ?>"><?= $tpl->getTitle() ?></option>
				<?php } ?>
			</select></label>
		<label style="margin-top: 18px;"><textarea placeholder="type note here..." name="report_content" id="report_content"><?= (isset($getNote)) ? $getNote->getNote() : '' ?></textarea></label>
		<div class="btn-block">
			<button type="submit" class="btn"<?= ($selfOwe - $creditLimit > 0 ? ' disabled="disabled"' : '') ?>>Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function completed(s) {
		var result = s.split(":");
		if (result[0] == "error") {
			Boxy.alert(result[1]);
		} else {
			Boxy.info(result[1], function () {
				Boxy.ask("Approve this report?", ["Yes", "No"], function (choice) {
					if (choice == "Yes") {
						$.post('/medical_exam/ajax.approve.php', {id: <?= $_GET['request_id'] ?>}, function (s) {
							if (s.trim() == "ok") {
								Boxy.get($(".close")).hideAndUnload();
								$('.mini-tab > a.tab.on');//.get(0).click();
							} else {
								Boxy.alert("An error occurred");
							}
						});
					}
					else {Boxy.get($(".close")).hideAndUnload();}
				});
			});
		}
	}

	$(document).ready(function () {
		$("#template_selecter").change(function () {
			var d = $('#report_content').code() + "<br>";
			$.get('/api/get_medical_report_templates.php', {id: $(this).val()}, function (data) {
				var s = JSON.parse(data);
				$('#report_content').code(d + s.bodyPart);
			});
		});
		$('textarea[name="report_content"]').summernote(SUMMERNOTE_CONFIG);

	})
</script>