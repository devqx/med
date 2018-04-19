<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/23/15
 * Time: 5:20 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AttachmentCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	// sleep(2);
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAttachment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AttachmentCategory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

	if (!isset($_SESSION['staffID'])) {
		exit("error:Session invalid; please login again");
	}
	if (is_blank($_POST['patient_id'])) {
		exit("error:Patient info not available");
	}
	if (is_blank($_POST['category_id'])) {
		exit("error:Document category not provided");
	}
	if (is_blank($_POST['note'])) {
		//exit("error:Document description not found");
	}
	
	$encounter = !is_blank($_POST['encounter_id']) ? new Encounter($_POST['encounter_id']) : null;

	$attach = (new PatientAttachment())
		->setUser(new StaffDirectory($_SESSION['staffID']))
		->setDateAdded(date("Y-m-d H:i:s", time()))
		->setPatient(new PatientDemograph($_POST['patient_id']))
		->setEncounter($encounter)
		->setCategory(new AttachmentCategory($_POST['category_id']))
		->setNote(@$_POST['note']);

	$newDoc = (new PatientAttachmentDAO())->add($attach, $_FILES['file1']);

	if ($newDoc === null) {
		exit("error:Failed to save document [General error]");
	} else if ($newDoc !== null && $newDoc['status'] !== "error") {
		exit("success:Document added");
	} else if ($newDoc !== null && $newDoc['status'] === "error") {
		exit("error:" . ucwords($newDoc['message']));
	}

}
?>
<section style="width: 500px;">
	<form enctype="multipart/form-data" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {onStart: start, onComplete: finished})">
		<?php if (isset($_REQUEST['pid'])) { ?>
			<input type="hidden" name="patient_id" value="<?= $_REQUEST['pid'] ?>">
		<?php } else { ?>
			<label>Patient: <input type="hidden" id="patient_id" name="patient_id"> </label>
		<?php } ?>
		<label>Select Document <input type="file" name="file1"> </label>
		<label>Document Category <select name="category_id" required data-placeholder="Select Document Category">
				<option></option>
				<?php foreach ((new AttachmentCategoryDAO())->all() as $category) { ?>
					<option value="<?= $category->getId() ?>"><?= $category->getName() ?></option>
				<?php } ?>
			</select> </label>
		<label>Description <textarea name="note"></textarea></label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<?php if(isset($_GET['enc_id'])){?><input type="hidden" name="encounter_id" value="<?= $_GET['enc_id']?>"><?php } ?>
	</form>
</section>
<script>
	$(document).ready(function () {
		$('#patient_id').select2({
			placeholder: "Search and select patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			}
		});
	});
	start = function () {
	};
	finished = function (s) {
		console.log(s);
		s = s.trim();
		var response = s.split(":")[1];
		var status = s.split(":")[0];
		if (status === "error") {
			Boxy.alert(response)
		} else {
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(response);
		}
	};
</script>