<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/7/16
 * Time: 10:50 AM
 */

if (!isset($_SESSION)) {
	session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralsQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RefererTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';


$specialties = (new StaffSpecializationDAO)->getSpecializations();
$templates = (new RefererTemplateDAO())->all();
$encounter = $e = isset($_GET['encounter_id']) ? (new EncounterDAO())->get($_GET['encounter_id'], true) : null;
if($e) {
	foreach ($e->getPresentingComplaints() as $pc) {
		$complaints[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getDiagnoses() as $pc) {
		$diagnoses[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getPrescriptions() as $pc) {
		//$pc = new Prescription();
		$medications[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getPlan() as $pc) {
		$plans[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getInvestigations() as $pc) {
		$investigations[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getSystemsReviews() as $pc) {
		$systems_reviews[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getExaminations() as $pc) {
		$examinations[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getExamNotes() as $pc) {
		$examNotes[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getMedicalHistory() as $pc) {
		$medicalHistory[] = $pc->description;
	}
	unset($pc);
	foreach ($e->getAllergies() as $datum) {
		$allergies[] = ($datum->getCategory() ? '(' . $datum->getCategory()->getName() . ') ' : '') . ($datum->getSuperGeneric() ? $datum->getSuperGeneric()->getName() : '') . ($datum->getAllergen() ? ucfirst($datum->getAllergen()) : '') . ': ' . $datum->getSeverity() . ' ' . $datum->getReaction();
	}
	unset($pc);
	foreach ($e->getSocialHistory() as $pc) {
		$socialHistory[] = $pc->description;
	}
	unset($pc);
}
 ;//todo populate from the encounter details;
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ReferralsQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';

	if (is_blank($_POST['type'])) {
		exit("error:Is referral within or outside this facility? ");
	}
	if (is_blank($_POST['note_'])) {
		exit("error:Referral note is required");
	}
	$rfq = new ReferralsQueue();
	$external = @$_POST['type'] == "external" ? TRUE : FALSE;
	$rfq->setAcknowledged(FALSE)->setSpecialization(!is_blank($_POST['specialization_id']) ? new StaffSpecialization($_POST['specialization_id']) : NULL)->setExternal($external)->setDoctor(new StaffDirectory($_SESSION['staffID']))->setNote($_POST['note_'])->setPatient(new PatientDemograph($_POST['pid']))->add();

	if ($rfq!== null) {
		exit("success:Data saved");
	}
	exit("error:Failed to save");
}
?>
<script>
	function startSaving() {
		$('.loader').html('<img src="/img/loading.gif"/> Please Wait...');
	}
	function saveComplete(s) {
		var dat = s.split(":");
		if (dat[0] === "error") {
			$('.loader').html('<span class="warning-bar">' + dat[1] + '</span>');
		} else if (dat[0] === "success") {
			Boxy.info(dat[1], function () {
				Boxy.get($('.close')).hideAndUnload();
			});
		}
	}

	$(document).ready(function () {
		$('#note_').summernote(SUMMERNOTE_CONFIG);
	});
</script>
<section style="width:850px">
	<form method="post" onsubmit="return AIM.submit(this, {'onStart' : startSaving, 'onComplete' : saveComplete})" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
		<div>
			<div class="loader"></div>
			<label>Specialization <select name="specialization_id" data-placeholder="Refer to specialty [if applicable]">
					<option></option>
					<?php foreach ($specialties as $specialty) {?>
						<option value="<?= $specialty->getId()?>"><?= $specialty->getName() ?></option>
					<?php }?>
				</select></label>
			<div class="clear"></div>
			<div class="row-fluid clear">
				<label class="span6"><input type="radio" name="type" value="internal"> Within this facility</label>
				<label class="span6"><input type="radio" name="type" value="external"> To Another facility</label>
			</div>

			<label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="referrals_template_link" data-href="refer_template_help.php">help</a>
					 | <i class="icon-plus-sign"></i><a href="javascript:" class="referrals_template_link" data-href="refer_template_new.php">add to list</a></span>
				<select name="template_id" id="template_id" data-placeholder="Select Custom Text Templates">
					<option></option>
					<?php foreach ($templates as $t) { ?>
						<option value="<?= $t->getId() ?>" data-text="<?= ($t->getContent()) ?>"><?= $t->getCategory()->getName() ?>
						: <?= $t->getTitle() ?></option><?php } ?>
				</select>
			</label>

			<label>
				Referral Note:
				<textarea placeholder="type here" name="note_" id="note_"><?php if($encounter){?>
					<div class="e-block">
		<div class="title">Presenting Complaints</div>
		<div class="content"><?= (count($complaints) > 0) ? "<ul><li>" . implode("</li><li>", $complaints) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Review of Systems / Examinations</div>
		<div class="content"><?= (count($systems_reviews) > 0) ? "<ul><li>" . implode("</li><li>", $systems_reviews) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Past Medical History</div>
		<div class="content"><?= (count($medicalHistory) > 0) ? "<ul><li>" . implode("</li><li>", $medicalHistory) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Past Drug History</div>
		<div class="content"><?= (count($drugHistory) > 0) ? "<ul><li>" . implode("</li><li>", $drugHistory) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Treatment Plan</div>
		<div class="content"><?= (count($plans) > 0) ? "<ul><li>" . implode("</li><li>", $plans) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Investigations</div>
		<div class="content"><?= (count($investigations) > 0) ? "<ul><li>" . implode("</li><li>", $investigations) . "</li></ul>" : '- - -' ?></div>
	</div>
	
	<div class="e-block">
		<div class="title">Allergies</div>
		<div class="content"><?= (count($allergies) > 0) ? "<ul><li>" . implode("</li><li>", $allergies) . "</li></ul>" : '- - -' ?></div>
	</div>
	
	<div class="e-block">
		<div class="title">Family/Social History</div>
		<div class="content"><?= (count($socialHistory) > 0) ? "<ul><li>" . implode("</li><li>", $socialHistory) . "</li></ul>" : '- - -' ?></div>
	</div>

	<div class="e-block">
		<div class="title">Physical Examination</div>
		<div class="content"><?= (count($examinations) > 0) ? "<ul><li>" . implode("</li><li>", $examinations) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Physical Examination Summary</div>
		<div class="content"><?= (count($examNotes) > 0) ? "<ul><li>" . implode("</li><li>", $examNotes) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Diagnoses</div>
		<div class="content"><?= (count($diagnoses) > 0) ? "<ul><li>" . implode("</li><li>", $diagnoses) . "</li></ul>" : '- - -' ?></div>
	</div>
					<?php }?>
				</textarea>
				<input type="hidden" name="pid" value="<?= $_GET['id']; ?>">
			</label>
			<div class="clear"></div>
		</div>

		<div class="btn-block pull-right_">
			<div align="right_">
				<button class="btn" id="btn$" type="submit">Save</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
			</div>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('label.span6 > input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function(event){
			$(event.currentTarget).trigger('change');
		});

		$('.boxy-content a.referrals_template_link').click(function () {
			Boxy.load("referrals/" + $(this).data("href"));
		});

		$('.boxy-content #template_id').select2().change(function (data) {
			//console.log(data.added);
			if (data.added !== undefined) {
				var content = $(data.added.element).data("text");
				$('textarea[name="note_"]').code(content).focus();
			} else {
				$('textarea[name="note_"]').code('').focus();
			}
		})//.trigger('change');


	});
	function refreshTemplates() {
		$.ajax({
			url: "/api/get_referral_templates.php",
			dataType: 'json',
			complete: function (s) {
				var data = s.responseJSON;
				//console.log(data);
				var str = '<option></option>';
				for (var i = 0; i < data.length; i++) {
					str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">' + data[i].category.name + ': ' + data[i].title + '</option>';
				}
				$('#template_id').html(str);
			}
		});
	}
</script>