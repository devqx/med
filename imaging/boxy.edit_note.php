<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 11:20 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScanNote.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ImagingTemplateDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';

$allScans = (new ScanDAO())->getScans();
$imagingTpls = (new ImagingTemplateDAO())->getTemplates();

if (isset($_GET['id'])) {
	$noteID = $_GET['id'];
	$getNote = (new PatientScanNoteDAO())->getNote($noteID);
} else {
	$noteID = '';
}

$bills = new Bills();
$scan = (new PatientScanDAO())->getScan($_GET['scan_id']);
$pat = (new PatientDemographDAO())->getPatient($scan->getPatient()->getId(), FALSE, null, null);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	if ($noteID == '') {
		exit("error:Select note to edit");
	}

	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$note = new PatientScanNote();
	if ($selfOwe - $creditLimit > 0) {
		exit("error:Patient has outstanding credit");
	}
	if (!is_blank($_POST['scan_note'])) {
		$note->setId($noteID);
		$note->setNote($_POST['scan_note']);
	} else {
		exit("error:Note is blank");
	}

	$updatedNote = (new PatientScanNoteDAO())->editNote($note);
	if ($updatedNote !== null) {
		exit("success:Note updated");
	}
	exit("error:Failed to update note");
}
?>
<section style="width: 730px">
	<div class="well">
		Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {onComplete: completed})">
		<h6>Note</h6>
		<label>Template <select name="template" id="template_what_text" placeholder="--select imaging template--">
				<option></option>
				<?php foreach ($imagingTpls as $tpl) { ?>
					<option value="<?= $tpl->getId() ?>"><?= $tpl->getTitle() ?> (<?= $tpl->getCategory()->getName() ?>)</option>
				<?php } ?>
			</select></label>
		<label style="margin-top: 18px;"><textarea placeholder="type note here..." name="scan_note" id="scan_note"><?= (isset($getNote)) ? $getNote->getNote() : '' ?></textarea></label>
		<div class="btn-block">
			<button type="submit" class="btn"<?= ($selfOwe - $creditLimit > 0 ? ' disabled="disabled"' : '') ?>>Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	<?php $random = time(); ?>
	function process_<?= $random?>(){
		Boxy.get($(".close")).hideAndUnload(function () {
			setTimeout(function () {
				Boxy.load('/imaging/scan.details.php?id=<?= $_GET['scan_id'] ?>', {
					afterShow: function () {
						$.getJSON('/api/get_patient_scan.php?id=<?= $_GET['scan_id'] ?>', function(data){
							if(!data.status_ && !data.approved){
								Boxy.ask("Submit for approval?", ["Yes", "No"], function (choice) {
									if (choice == "Yes") {
										$.post('/imaging/ajax.approve_.php', {id: data.id}, function (s) {
											if (s.trim() == "ok") {
												setTimeout(function(){process_<?= $random?>();}, 5);
											} else {
												Boxy.alert("An error occurred");
											}
										});
									}
									else {
										Boxy.get($(".close")).hideAndUnload();
									}
								});
							} else if(data.status_ && !data.approved){
								/*Boxy.ask("Approve this report now", ["Yes", "No"], function (choice) {
									if (choice == "Yes") {
										$.post('/imaging/ajax.approve.php', {id: data.id}, function (s) {
											setTimeout(function(){process_();}, 5);
										});
									}
									else {
										Boxy.get($(".close")).hideAndUnload();
									}
								});*/
							} else if(data.status_ && data.approved){
								Boxy.ask('Print this report now?', ['Yes', 'No'], function (answer) {
									if (answer == 'Yes') {
										var link = document.createElement('a');
										link.href = "printNotes.php?id=<?= $_GET['scan_id'] ?>";
										link.target = '_blank';
										document.body.appendChild(link);
										link.click();
									}
								});
							}
						});
						//if not yet approved

						//if not yet approved but has been submitted for approval
					}
				});


			}, 500);

		});
	}

	function completed(s) {
		var result = s.split(":");
		if (result[0] == "error") {
			Boxy.alert(result[1]);
		} else {
			process_<?= $random?>();
		}
	}

	$(document).ready(function () {
		$("#template_what_text").change(function () {
			var d = $('#scan_note').code() + "<br>";
			$.get('/api/get_imaging_tpl.php', {id: $(this).val()}, function (data) {
				var s = JSON.parse(data);
				$('#scan_note').code(d + s.bodyPart);
			});
		});
		$('textarea[name="scan_note"]').summernote(SUMMERNOTE_CONFIG);

	});


</script>