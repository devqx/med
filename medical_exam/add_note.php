<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/5/16
 * Time: 5:30 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientMedicalReportDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamReportingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';

@session_start();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$protect = new Protect();

$currency = (new CurrencyDAO())->getDefault();
$templates = (new ExamReportingTemplateDAO())->all();
$request = (new PatientMedicalReportDAO())->get($_REQUEST['request_id']);
$pat = $request->getPatient();
$bills = new Bills();
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientMedicalReport.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientMedicalReportNote.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$note = new PatientMedicalReportNote();
	if ($selfOwe - $creditLimit > 0) {
		exit("error:Patient has outstanding credit");
	}
	if (is_blank($_POST['report_content'])) {
		exit("error:Note is blank");
	} else {
		$note->setNote($_POST['report_content'])
			->setPatientMedicalReport(new PatientMedicalReport($_POST['request_id']))
			->setCreateUser(new StaffDirectory($_SESSION['staffID']))->add();

		if ($note !== null) {
			exit("success:Report saved");
		} else if ($note == null) {
			exit("error:Failed to save report");
		}
		exit("error:An unknown error has occurred");
	}
}

$request = (new PatientMedicalReportDAO())->get($_REQUEST['request_id']);
$patientId = $request->getPatient()->getId();
?>
<!--suppress JSUnresolvedVariable -->
<section style="width: 850px">
	<div class="well">
		Patient's Outstanding balance: <?= $currency ?><?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: completed})">
		<p><strong>Request Items</strong></p>
		<div class="panel-group" id="accordion2">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseOne2">Lab Requests</a></h5>
				</div>
				<div id="collapseOne2" class="panel-collapse collapse">
					<div class="panel-body">
						<p><?php foreach ($request->getLabs() as $lab){//$lab=new PatientLab();$lab=new LabGroup();?><a href="javascript:" data-item="<?= $lab->getLabGroup()->getGroupName() ?>" data-patientId="<?=$patientId?>" data-type="lab" class="tag"><?= $lab->getLabGroup()->getGroupName() ?></a><?php } ?></p>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo2">Imaging Requests</a></h5>
				</div>
				<div id="collapseTwo2" class="panel-collapse collapse">
					<div class="panel-body">
						<p><?php foreach ($request->getImagings() as $scan){/*$scan=new PatientScan()*/?><a href="javascript:" data-item="<?=$scan->getRequestCode()?>" data-id="<?= $scan->getId()?>" data-type="scan" class="tag"><?=$scan->getRequestCode()?></a><?php }?></p>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<h5 class="panel-title"><a data-toggle="collapse" data-parent="#accordion2" href="#collapseThree2">Procedure Requests</a></h5>
				</div>
				<div id="collapseThree2" class="panel-collapse collapse">
					<div class="panel-body">
						<p><?php foreach ($request->getProcedures() as $procedure) {/*$procedure=new PatientProcedure()*/?><a href="javascript:;" data-item="<?=$procedure->getRequestCode()?>" data-id="<?=$procedure->getId()?>" data-type="procedure" class="tag"><?=$procedure->getRequestCode()?></a><?php }?></p>
					</div>
				</div>
			</div>
		</div>

		<h6>Report Summary</h6>

		<label>Template <select name="template" id="template_selector" data-placeholder="--Select Reporting Template--">
				<option></option>
				<?php foreach ($templates as $template) { ?>
					<option value="<?= $template->getId() ?>"><?= $template->getTitle() ?></option>
				<?php } ?>
			</select></label>
		<label style="margin-top: 18px;"><textarea placeholder="Type note here..." name="report_content" id="report_content"></textarea></label>
		<input type="hidden" name="request_id" value="<?= $_REQUEST['request_id'] ?>">

		<div class="btn-block">
			<button type="submit" class="btn"<?= ($selfOwe - $creditLimit > 0 ? 'disabled="disabled"' : '') ?>>Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function completed(s) {
		var result = s.split(":");
		if (result[0] === "error") {
			Boxy.alert(result[1]);
		} else {
			//all is good then, the dialog will just close
			Boxy.info(result[1], function () {
				Boxy.get($(".close")).hideAndUnload();
				<?php if($this_user->hasRole($protect->medical_report_approver)) {?>
				Boxy.ask("Approve this report?", ["Yes", "No"], function (choice) {
					if (choice === "Yes") {
						$.post('/medical_exam/ajax.approve.php', {id: <?= $_GET['request_id'] ?>}, function (s) {
							if (s.trim() === "ok") {
								Boxy.get($(".close")).hideAndUnload();
								$('.mini-tab > a.tab.on'); //.get(0).click();
							} else {
								Boxy.alert("An error occurred");
							}
						});
					}	else { Boxy.get($(".close")).hideAndUnload();}
				});
				<?php }?>
			});
		}
	}
	$(document).ready(function () {
		$("#template_selector").change(function () {
			var d = $('#report_content').code() + "<hr>";
			$.get('/api/get_exam_report_tpl.php', {id: $(this).val()}, function (data) {
				var s = JSON.parse(data);
				$("#report_content").code(d + s.bodyPart);
			});
		});
		$('textarea[name="report_content"]').summernote(SUMMERNOTE_CONFIG);
	});
</script> 