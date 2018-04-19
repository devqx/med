<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/26/16
 * Time: 9:10 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureAttachmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureAttachment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	$status = (new ProcedureAttachmentDAO())->uploadFile($_POST['patient_procedure_id'], $_FILES['attachment']);
	$pdo = (new MyDBConnector())->getPDO();
	
	if ($status['status'] == 'error') {
		exit('error:' . ucfirst($status['message']));
	} else if ($status['status'] == 'success') {
		$url = $status['filename'];
		$mimeType = $status['mimetype'];
		$attachment = (new ProcedureAttachment())->setPatientProcedure(new PatientProcedure($_POST['patient_procedure_id']))->setUrl($url)->setMimeType($mimeType)->setDescription($_POST['description'])->setUploadDate(date(MainConfig::$mysqlDateTimeFormat))->setUploadBy(new StaffDirectory($_SESSION['staffID']))->add($pdo);
		if ($attachment != null) {
			exit('success:Attachment added');
		}
		exit('error:Failed to upload selected file');
	}
	exit('error:Failed to add attachment');
	
}
?>
<section>
	<form method="post" enctype="multipart/form-data" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:___start,onComplete:___done})">
		<label>Attachment</label>
		<label>File:<input type="file" name="attachment" style="display: block"></label>
		<label>Description: <input type="text" name="description"></label>
		<input type="hidden" name="patient_procedure_id" value="<?= $_REQUEST['id'] ?>">

		<div class="btn-block">
			<button type="submit" class="btn">Attach</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	function ___start() {
	}
	function ___done(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else if (data[0] === "success") {
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	}
</script>
