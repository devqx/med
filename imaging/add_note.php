<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/17/14
 * Time: 1:02 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScanNote.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ImagingTemplateDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';

$bills = new Bills();
$scan = (new PatientScanDAO())->getScan($_GET['scan_id']);
$pat = (new PatientDemographDAO())->getPatient($scan->getPatient()->getId(), false, null, null);
$imagingTpls = (new ImagingTemplateDAO())->getTemplates();

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$pdo = (new MyDBConnector())->getPDO();
	$note = new PatientScanNote();
	if ($selfOwe - $creditLimit > 0) {
		//exit("error:Patient has outstanding credit");
	}
	if (!is_blank($_POST['scan_note'])) {
		$pdo->beginTransaction();
		$note->setNote($_POST['scan_note']);
		$note->setPatientScan((new PatientScanDAO())->getScan($_POST['scan_id'], $pdo));
		$note->setCreator(new StaffDirectory($_SESSION['staffID']));
		$note->setIsComment(isset($_POST['comment']));
	} else {
		exit("error:Note is blank");
	}

	$newNote = (new PatientScanNoteDAO())->addNote($note, $pdo);
	
	if ($newNote !== null) {
		$scan = (new PatientScanDAO())->getScan($_POST['scan_id'], $pdo);
		$scan->setCapturedBy(( new StaffDirectoryDAO()) ->getStaff($_SESSION['staffID'], FALSE, $pdo));
		$scan->setCapturedDate(date("Y-m-d H:i:s"));
		$sc = (new PatientScanDAO())->capturedScan($scan, $pdo);
		if($sc != null){
			$pdo->commit();
			exit("success:Note added");
		}
		$pdo->rollBack();
		exit('error:An error occurred in transaction');
	}
	exit("error:Failed to add note");
}

?>
<section style="width: 730px">
	<div class="well">
		Patient's Outstanding balance: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: completed})">
		<h6>Note</h6>
		<?php if(!isset($_GET['comment'])){?><label>Template <select name="template" id="template_what_text" data-placeholder="--select imaging template--">
				<option></option>
				<?php foreach ($imagingTpls as $tpl) { ?>
					<option value="<?= $tpl->getId() ?>"><?= $tpl->getTitle() ?> (<?= $tpl->getCategory()->getName() ?>)</option>
				<?php } ?>
			</select></label>
		<?php }?>
		<label style="margin-top: 18px;"><textarea placeholder="type note here..." name="scan_note" id="scan_note"></textarea></label>
		<input type="hidden" name="scan_id" value="<?= $_REQUEST['scan_id'] ?>">
		<?php if(isset($_GET['comment'])&&$_GET['comment']=='true'){?>
			<input type="hidden" name="comment" value="true">
		<?php }?>

		<div class="btn-block">
			<button type="submit" class="btn"<?= ($selfOwe - $creditLimit > 0 ? 'disabled="disabled"' : '') ?>>Save</button>
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
									if (choice === "Yes") {
										$.post('/imaging/ajax.approve_.php', {id: data.id}, function (s) {
											if (s.trim() === "ok") {
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
									if (answer === 'Yes') {
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
			//all is good then, the dialog will just close
			//Boxy.info(result[1]);
			process_<?=$random?>();
		}
	}

	$(document).ready(function () {
		$("#template_what_text").change(function () {
			var d = $('#scan_note').code() + "<hr>";
			$.get('/api/get_imaging_tpl.php', {id: $(this).val()}, function (data) {
				var s = JSON.parse(data);
				$("#scan_note").code(d + s.bodyPart);
			});
		});
		$('textarea[name="scan_note"]').summernote(SUMMERNOTE_CONFIG);

	})
</script>