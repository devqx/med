<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/4/16
 * Time: 11:28 AM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormAnswerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";

$editStyleByAdd = Clinic::$editStyleByAdd;

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$e = (new EncounterDAO())->get($_GET['encounter_id'], true);
$vitals = (new VitalSignDAO())->getEncounterVitalSigns($_GET['encounter_id'], false);
$complaints = $diagnoses = $plans = $investigations = $systems_reviews = $examinations = $examNotes = $addenda = $medicalHistory = $drugHistory = [];

$questions = (new SFormAnswerDAO())->forEncounter($_GET['encounter_id']);
foreach ($e->getPresentingComplaints() as $pc) {
	$complaints[] = $pc->description;
}
unset($pc);
foreach ($e->getDiagnoses() as $pc) {
	$diagnoses[] = $pc->description;
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
foreach ($e->getDrugHistory() as $pc) {
	foreach ($pc->getData() as $data) {
		$drugHistory[]
			= /*$data->getDose() . $data->getDuration() . $data->getFrequency() . */
			'<div class="row-fluid"><div class="span5">' . $data->getGeneric()->getName() . ' [' . $data->getGeneric()->getWeight() . ' ' . $data->getGeneric()->getForm() . ']</div><div class="fadedText span7">' . $data->getComment() . '</div></div>';
	}
	unset($data);
}
unset($pc);
?>
<section style="width: 850px;">
	<div class="row-fluid">
		<div class="span4">
			<div class="e-block">
				<div class="title">Start Date</div>
				<div class="content"><?= date(MainConfig::$dateTimeFormat, strtotime($e->getStartDate())) ?></div>
			</div>
		</div>
		<div class="span4">
			<div class="e-block">
				<div class="title">Department</div>
				<div class="content"><?= $e->getDepartment() ? $e->getDepartment()->getName() : '- - -' ?></div>
			</div>
		</div>
		<div class="span4">
			<div class="e-block">
				<div class="title">Specialization</div>
				<div class="content"><?= $e->getSpecialization() ? $e->getSpecialization()->getName() : '- - -' ?></div>
			</div>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Pre-encounter</div>
		<div class="content"></div>
	</div>
	<div class="e-block">
		<div class="title">Vital Signs</div>
		<div class="content">
			<?php if (count($vitals) == 0) { ?>
				Not available
			<?php } else { ?>
				
					<?php foreach ($vitals as $vital) {
						// $vital=new VitalSign();?>
						<div class="no-wrap"><strong><?= $vital->getType()->getName()?></strong>: <?= $vital->getValue()?><?= $vital->getType()->getUnit() ?></div>
					<?php } ?>
			<?php } ?>
		</div>
	</div>
	<div class="e-block">
		<div class="title">Presenting Complaints</div>
		<div class="content"><?= (count($complaints) > 0) ? "<ul><li>" . implode("</li><li>", $complaints) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Review of Systems / Examinations</div>
		<div class="content"><?= (count($systems_reviews) > 0) ? "<ul><li>" . implode("</li><li>", $systems_reviews) . "</li></ul>" : '- - -' ?></div>
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
		<div class="title">Past Medical History</div>
		<div class="content"><?= (count($medicalHistory) > 0) ? "<ul><li>" . implode("</li><li>", $medicalHistory) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Diagnoses</div>
		<div class="content"><?= (count($diagnoses) > 0) ? "<ul><li>" . implode("</li><li>", $diagnoses) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Past Drug History</div>
		<div class="content"><?= (count($drugHistory) > 0) ? "<ul><li>" . implode("</li><li>", $drugHistory) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Investigations</div>
		<div class="content"><?= (count($investigations) > 0) ? "<ul><li>" . implode("</li><li>", $investigations) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Plans</div>
		<div class="content"><?= (count($plans) > 0) ? "<ul><li>" . implode("</li><li>", $plans) . "</li></ul>" : '- - -' ?></div>
	</div>
	
	<div class="e-block hide_">
		<div class="title">Consulting Doctor</div>
		<div class="content"><?= (count($e->getPresentingComplaints()) > 0 && $e->getPresentingComplaints()[0]) ? $e->getPresentingComplaints()[0]->doctorName : '- - -' ?></div>
	</div>

	<div class="e-block">
		<div class="title">Signed</div>
		<div class="content">
			<span class="pull-left">
				<?= ($e->getSignedBy() ? $e->getSignedBy()->getFullname() . ' on ' . date(MainConfig::$dateTimeFormat, strtotime($e->getSignedOn())) : 'Not Signed Yet') ?>
			</span>
			<span class="pull-right">
				<?php if ($e->getSignedBy() == null) { ?>
				<a href="javascript:;" class="btn" data-encounter-id="<?= $e->getId() ?>" id="signLink"<?= (!$this_user->hasRole($protect->doctor_role) ? " disabled" : "") ?> >
						Sign & Close</a><?php } ?>
				<?php if($e->getSignedBy() != null){?>
				<?php if ($editStyleByAdd || !$editStyleByAdd) { ?>
					<a class="btn" href="javascript:" id="addLink" data-id="<?= $e->getId() ?>">Add Other Note</a>
				<?php } ?>
				<?php if ($editStyleByAdd) { ?>
					<a class="btn" href="javascript:" id="addDLink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Diagnosis</a>
				<?php } ?>
				<?php if ($editStyleByAdd) { ?>
					<a class="btn" href="javascript:" id="addPLink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Prescription</a>
				<?php } ?>
				<?php if (!$editStyleByAdd) { ?>
					<a class="btn" href="javascript:" id="editLink" data-id="<?= $e->getId() ?>">Edit</a>
				<?php } ?>
				<?php }?>
			</span>
		</div>
	</div>

	<div class="clear"></div>
	<div class="clear"></div>
	<div class="clear"></div>

	<div class="e-block">
		<div class="title">Other Notes</div>
		<div class="content">
			<?php foreach ($e->getAddenda() as $pc) {//$pc=new EncounterAddendum();?>
				<div class="row-fluid">
					<div class="span3"><?= date(MainConfig::$dateTimeFormat, strtotime($pc->getDate())) ?></div>
					<div class="span8"><?= $pc->getNote() ?></div>
					<div class="span1"><?= $pc->getUser()->getUsername() ?></div>
				</div>
			<?php } ?>
		</div>
	</div>
</section>
<script type="text/javascript">

	$(document).on('click', '#signLink', function (evt) {
		var id = $(this).data("encounter-id");
		if (!evt.handled) {
			showPinBox(function () {
				$.post('/api/sign_encounter.php', {encounter_id: id}, function (data) {
					var response = data.split(":");
					if (response[0] === "success") {
						Boxy.get($(".close")).hideAndUnload();
						showTabs(1);
					} else {
						Boxy.warn(response[1]);
					}
				});
			});
			evt.handled = true;
		}
	}).on('click', '#editLink', function (evt) {
		var id = $(this).data("id");
		if (!evt.handled) {
			Boxy.get($(".close")).hideAndUnload();
			Boxy.load('/encounter.edit.php' + '?enc_id=' + id, {
				title: "Encounter Edit", afterHide: function () {
					showTabs(1);
					Boxy.get($(".close")).hideAndUnload();//close this dialog after closing the editor
					setTimeout(function () {
						$('a[data-link-type="encounterDetails"][data-id="' + id + '"]').get(0).click();
					}, 100);
				}
			});
			evt.handled = true;
		}
	}).on('click', '#addLink', function (evt) {
		var id = $(this).data("id");
		if (!evt.handled) {
			Boxy.load('/encounter.addendum.php' + '?enc_id=' + id, {
				title: "Encounter Edit", afterHide: function () {
					showTabs(1);
					Boxy.get($(".close")).hideAndUnload();//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	}).on('click', '#addDLink', function (evt) {
		var id = $(this).data("id");
		var pid = $(this).data("pid");
		if (!evt.handled) {
			Boxy.load('/boxy.addDiagnosis.php?pid=' + pid + '&enc_id=' + id, {
				title: "Add Diagnosis", afterHide: function () {
					showTabs(1);
					Boxy.get($(".close")).hideAndUnload();//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	}).on('click', '#addPLink', function (evt) {
		var id = $(this).data("id");
		var pid = $(this).data("pid");
		if (!evt.handled) {
			Boxy.load('/boxy.addRegimen.php?id=' + pid + '&enc_id=' + id, {
				title: "Add Prescription", afterHide: function () {
					showTabs(1);
					Boxy.get($(".close")).hideAndUnload(function () {
						setTimeout(function () {
							alert('test');
							Boxy.get($(".close")).hideAndUnload()
						}, 1000);
					});//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	});
</script>

