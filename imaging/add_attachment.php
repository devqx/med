<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 5:12 PM
 */
$valid_file_types = array("image/jpeg", "image/png", "image/gif", "bmp", "application/pdf", "video/x-msvideo");

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScanAttachment.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$bills = new Bills();
$scan = (new PatientScanDAO())->getScan($_GET['scan_id']);
$pat = (new PatientDemographDAO())->getPatient($scan->getPatient()->getId(), false, null, null);

$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId()) - (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST && $_FILES) {
	if ($selfOwe > 0) {
		//exit("error:Patient has outstanding credit");
	}
	if (empty($_FILES['scan_attachment'])) {
		exit("error:Select a file");
	}
	//we should check the maximum upload file limit
	if ($_FILES['scan_attachment']['size'] <= 0 || $_FILES['scan_attachment']['size'] > getMaximumFileUploadSize()) {
		exit("error:Invalid file size; File size is 0 or exceeds the max. limit");
	}
	
	//we're almost ready to upload
	$attach = new PatientScanAttachment();
	if (!in_array($_FILES['scan_attachment']['type'], $valid_file_types)) {
		exit("error:File is not an allowed type. Only images and videos are allowed. (" . $_FILES['scan_attachment']['type'] . ")");
	} else {
		$attach->setAttachment($_FILES['scan_attachment']);
	}
	
	if (!empty($_POST['scan_note'])) {
		$attach->setNote($_POST['scan_note']);
	} else {
		exit("error:Note required");
	}
	
	$attach->setCreator(new StaffDirectory($_SESSION['staffID']));
	
	$attach->setPatientScan((new PatientScanDAO())->getScan($_POST['scan_id']));
	$newAttach = (new PatientScanAttachmentDAO())->addAttachment($attach);
	
	exit(json_encode($newAttach));
}
?>
<section>
	<div class="well">
		Patient's Outstanding balance: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<!--    <span class="pull-right action"><i class="icon-paper-clip"></i><a href="javascript:;">add another file</a></span>-->
	<form method="post" enctype="multipart/form-data" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: uploaded})">
		<label>Attachment</label>
		<label>File:<input type="file" name="scan_attachment" style="display: block"></label>
		<label>Description:
			<input type="text" name="scan_note" maxlength="50"></label>
		<input type="hidden" name="scan_id" value="<?= $_REQUEST['scan_id'] ?>">

		<div class="btn-block">
			<button type="submit" class="btn"<?= ($selfOwe > 0 /*|| $bb > $credit_limit*/ ? 'disabled="disabled"' : '') ?>>
				Attach
			</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function uploaded(s) {
		try {
			JSON.parse(s);
			Boxy.get($(".close")).hideAndUnload();
		} catch (exception) {
			var result = s.split(":");
			if (result[0] == "error") {
				Boxy.alert(result[1]);
			} else {
				Boxy.ask("Would you like to move this request to the approval tab?", ["Yes", "No"], function (choice) {
					if (choice == "Yes") {
						$.post('/imaging/ajax.approve_.php', {id: <?= $_GET['scan_id'] ?>}, function (s) {
							if (s.trim() == "ok") {
								Boxy.get($(".close")).hideAndUnload();
								$('#scanHomeMenuLinks a.approve').click();
							} else {
								Boxy.alert("An error occurred");
							}
						});
					}
					else {
						Boxy.get($(".close")).hideAndUnload();
					}
				});
			}
		}
	}
</script>