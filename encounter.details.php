<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/4/16
 * Time: 11:28 AM
 */

//include "encounter.details.euracare.php";
//exit;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterFormDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/EncounterForm.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Form.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$sForms = (new SFormDAO())->all();

$eF = (new EncounterFormDAO())->forEncounter($_GET['encounter_id']);
$editStyleByAdd = Clinic::$editStyleByAdd;

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$e = (new EncounterDAO())->get($_GET['encounter_id'], true);
$vitals = (new VitalSignDAO())->getEncounterVitalSigns($_GET['encounter_id'], false);
$complaints = $socialHistory = $allergies = $diagnoses = $plans = $medications = $investigations = $systems_reviews = $examinations = $examNotes = $addenda = $medicalHistory = $drugHistory = [];
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
	$allergies[] = ($datum->getCategory() ? '('.$datum->getCategory()->getName().') ' : '').($datum->getSuperGeneric() ? $datum->getSuperGeneric()->getName() : '').( $datum->getAllergen() ? ucfirst($datum->getAllergen()) : '' ).': '.$datum->getSeverity().' '.$datum->getReaction();
}
unset($pc);
foreach ($e->getSocialHistory() as $pc) {
	$socialHistory[] = $pc->description;
}
unset($pc);
foreach ($e->getDrugHistory() as $pc) {
	foreach ($pc->getData() as $data) {
		//$data = new PrescriptionData();
		if($data->getStatus()=='history') {
			$drugHistory[]
				= /*$data->getDose() . $data->getDuration() . $data->getFrequency() . */
				'<div class="row-fluid"><div class="span5">' . $data->getGeneric()->getName() . ' [' . $data->getGeneric()->getWeight() . ' ' . $data->getGeneric()->getForm() . ']</div><div class="fadedText span7">' . $data->getComment() . '</div></div>';
		}
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
		<div class="title">Pre-Encounter Data</div>
		<div class="content">
			
			<?php if(count($eF)>0){?>
				<ul class="list-blocks sforms" style="column-count: 2;">
					<?php foreach ($eF as $ef){$datum = $ef->getForm();?>
						<li class="tag" data-id="<?=$datum->getId()?>">
							<?=$datum->getName()?>
							<div class="pull-right">
								<a href="javascript:" title="View Form Data" class="_sform" data-title="<?= escape($datum->getName())?>" data-href="/pages/pm/sforms/boxy.sform.fill.php?id=<?=$datum->getId()?>&encounter_id=<?= $_GET['encounter_id']?>&readonly">View Form Data</a>
							</div>
						</li>
					<?php }?>
				</ul>
			<?php } else {?>
				- - -
			<?php }?>
		</div>
	</div>
	
	<div class="e-block">
		<div class="title">Vital Signs</div>
		<div class="content">
			<?php if (count($vitals) == 0) { ?>
				Not available
			<?php } else { ?>
					<?php foreach ($vitals as $vital) {
						//$vital=new VitalSign();?>
						<div class="no-wrap"><strong><?= $vital->getType()->getName()?></strong>: <?= $vital->getValue()?><?= utf8_encode($vital->getType()->getUnit()) ?></div>
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
		<div class="title">Past Medical History</div>
		<div class="content"><?= (count($medicalHistory) > 0) ? "<ul><li>" . implode("</li><li>", $medicalHistory) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Past Drug History</div>
		<div class="content"><?= (count($drugHistory) > 0) ? "<ul><li>" . implode("</li><li>", $drugHistory) . "</li></ul>" : '- - -' ?></div>
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
	<div class="e-block">
		<div class="title">Investigations</div>
		<div class="content"><?= (count($investigations) > 0) ? "<ul><li>" . implode("</li><li>", $investigations) . "</li></ul>" : '- - -' ?></div>
	</div>
	<div class="e-block">
		<div class="title">Plans</div>
		<div class="content">
			<?= (count($plans) > 0) ? "<ul><li>" . implode("</li><li>", $plans) . "</li></ul>" : '- - -' ?>
			<?= (count($medications) > 0) ? "<u>Medications</u><ul><li>" . implode("</li><li>", $medications) . "</li></ul>" : '- - -' ?>
		</div>
	</div>
	
	<div class="e-block hide_">
		<div class="title">Consulting Doctor</div>
		<div class="content">
			<?= $e->getSignedBy() ? $e->getSignedBy()->getFullname() : ' - -'?>
			<?php //= (count($e->getPresentingComplaints()) > 0 && $e->getPresentingComplaints()[0]) ? $e->getPresentingComplaints()[0]->doctorName : '- - -' ?>
		</div>
	</div>

	<div class="e-block">
		<div class="title">Signed</div>
		<div class="content">
			<span class="pull-left">
				<?= ($e->getSignedBy() ? $e->getSignedBy()->getFullname() . ' on ' . date(MainConfig::$dateTimeFormat, strtotime($e->getSignedOn())) : 'Not Signed Yet') ?>
			</span>
			
		</div>
		
		<div class="dropdown pull-right">
			<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
				Action
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
				 <li><a href="javascript:;" class="btn btn-mini-"  data-patient-id="<?= $e->getPatient()->getId()?>" data-encounter-id="<?= $e->getId() ?>" id="docLink"<?= (!$this_user->hasRole($protect->doctor_role) || isset($_GET['aid']) ? " disabled" : "") ?> >
						 Attach Documents</a></li>
				<?php if ($e->getSignedBy() == null) { ?>
				 <li><a href="javascript:;" class="btn btn-mini-" data-encounter-id="<?= $e->getId() ?>" id="signLink"<?= (!$this_user->hasRole($protect->doctor_role) || isset($_GET['aid']) ? " disabled" : "") ?> >
						 Sign & Close</a></li> <?php } ?>
				<?php if($e->getSignedBy() != null){ ?>
					<?php if (!isset($_GET['aid'])) { ?>
						<li><a  href="javascript:" class="btn" id="addLink" data-id="<?= $e->getId() ?>">Add Other Note</a></li>
					<?php } ?>
					<?php if ($editStyleByAdd && !isset($_GET['aid']) && !$e->getClaimed()) { ?>
						<li><a  href="javascript:"  class="btn btn-mini-" id="addDLink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Diagnosis</a></li>
					<?php } ?>
					<?php if ($editStyleByAdd && !isset($_GET['aid']) && !$e->getClaimed()) { ?>
						<li><a  href="javascript:" class="btn btn-mini-" id="addPLink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Prescription</a></li>
					<?php } ?>
					<?php if (!$editStyleByAdd && !isset($_GET['aid']) && !$e->getClaimed()) { ?>
						<li><a  href="javascript:" class="btn btn-mini-" id="editLink" data-id="<?= $e->getId() ?>">Edit</a></li>
					<?php } ?>
					<?php if($editStyleByAdd && !isset($_GET['aid']) && !$e->getClaimed()) { ?>
						<li><a  href="javascript:" class="btn " id="addLLink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Lab</a></li>
					<?php } ?>
					<?php if($editStyleByAdd && !isset($_GET['aid']) && !$e->getClaimed()) { ?>
						<li><a  href="javascript:" class="btn " id="addILink" data-id="<?= $e->getId() ?>" data-pid="<?= $e->getPatient()->getId() ?>">Add Imaging</a></li>
					<?php } ?>
				<?php } ?>
			</ul>
		</div>
	</div>
	
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
	<div class="e-block">
		<div class="title">Documents</div>
		<div class="content">
				<table class="t3 table table-striped">
					<thead>
					<tr>
						<th width="10%">Date</th>
						<th>Category</th>
						<th>Name</th>
						<th style="width: 2%">*</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($e->getDocuments()->data as $attachment) {//$attachment=new PatientAttachment();?>
						<?php if ($attachment->getPatient()) { ?>
							<tr>
								<td><?= date("Y/m/d", strtotime($attachment->getDateAdded())) ?></td>
								<td><?= $attachment->getCategory() ? $attachment->getCategory()->getName() : 'N/A' ?></td>
								<td><?= $attachment->getNote() ?></td>
								<td><a class="pdf_viewer" href="/documents/attachment.php?id=<?= $attachment->getId() ?>">View</a></td>
							</tr>
						<?php } ?>
					<?php } ?>
					</tbody>
				</table>
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
						<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
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
					<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
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
					<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
					Boxy.get($(".close")).hideAndUnload();//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	}).on('click', '#docLink', function (evt) {
		var id = $(this).data("encounter-id");
		var pid = $(this).data("patient-id");
		if (!evt.handled) {
			Boxy.load('/documents/new.php?pid=' + pid + '&enc_id=' + id, {
				title: "Encounter Document", afterHide: function () {
					<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
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
					<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
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
					<?php if(!isset($_GET['aid'])) {?>showTabs(1);<?php } else {?>showTabs(16);<?php }?>
					Boxy.get($(".close")).hideAndUnload(function () {
						setTimeout(function () {
							Boxy.get($(".close")).hideAndUnload()
						}, 1000);
					});//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	}).on('click', '._sform', function(e) {
		if (!e.handled) {
			Boxy.load($(this).data('href'), {title: $(this).data('title')});
			e.handled = true;
		}
	}).on('click', '#addLLink', function (evt) {
		var id = $(this).data("id");
		var pid = $(this).data("pid");
		if (!evt.handled) {
			Boxy.load('/labs/allLabs.php?id=' + pid + '&enc_id=' + id, {
				title: "Add Lab", afterHide: function () {
					<?php if(isset($_GET['aid'])) {?>showTabs(1);
					<?php } else {?>showTabs(6);<?php }?>
					Boxy.get($(".close")).hideAndUnload(function () {
						setTimeout(function () {
							Boxy.get($(".close")).hideAndUnload()
						}, 1000);
					});//close this dialog after closing the editor
				}
			});
			evt.handled = true;
		}
	}).on('click', '#addILink', function (evt) {
		var id = $(this).data("id");
		var pid = $(this).data("pid");
		if (!evt.handled) {
			Boxy.load('/imaging/boxy.new_scan.php?pid=' + pid + '&enc_id=' + id, {
				title: "Add Imaging", afterHide: function () {
					<?php if(!isset($_GET['aid'])) {?> showTabs(1);
					<?php } else {?>showTabs(11);<?php }?>
					Boxy.get($(".close")).hideAndUnload(function () {
						setTimeout(function () {
							Boxy.get($(".close")).hideAndUnload()
						}, 1000);
					});
				}
			});
			evt.handled = true;
		}
	})
</script>

