<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/28/16
 * Time: 5:15 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";

$editStyleByAdd = Clinic::$editStyleByAdd;

$protect = new Protect();
//$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
$e = (new EncounterDAO())->get($_GET['id'], true);
$vitals = (new VitalSignDAO())->getEncounterVitalSigns($_GET['id'], false);
$patient = (new PatientDemographDAO())->getPatient($e->getPatient()->getId(), true);
$complaints = $allergies = $socialHistory = $diagnoses = $plans = $medications = $investigations = $systems_reviews = $examinations = $examNotes = $addenda = $medicalHistory = $drugHistory = [];
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
foreach ($e->getPrescriptions() as $pc) {
	$medications[] = $pc->description;
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
		$drugHistory[]
			= /*$data->getDose() . $data->getDuration() . $data->getFrequency() . */
			'<div class="row-fluid"><div class="span5">' . $data->getGeneric()->getName() . ' [' . $data->getGeneric()->getWeight() . ' ' . $data->getGeneric()->getForm() . ']</div><div class="fadedText span7">' . $data->getComment() . '</div></div>';
	}
	unset($data);
}
unset($pc);
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<meta charset="UTF-8">

	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
	<link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
	<link href="/style/def.css" rel="stylesheet" type="text/css"/>
	<link href="/style/font-awesome.css" rel="stylesheet" type="text/css"/>
	<meta name="viewport" content="width=device-width">
	<style>
		.table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
			padding: 2px !important;
		}

		.table {
			color: #000;
		}

		table, tr, td, th, tbody, thead, tfoot {
			page-break-inside: avoid !important;
		}
	</style>
</head>
<body style="margin: 50px 20px;">
<?= (new ClinicDAO())->getClinic(1)->getHeader() ?>
<div class="text-center text-capitalize">
	<h2>ENCOUNTER SUMMARY</h2>
</div>
<div class="container">
	<table class="table table-bordered table-striped text-capitalize text-uppercase">
		<tr>
			<th>Patient Name</th>
			<td><?= $patient->getFullname() ?></td>
			<th>Gender</th>
			<td><?= $patient->getSex() ?></td>
		</tr>
		<tr>
			<th>Hospital No.</th>
			<td><?= $patient->getLegacyId() ?></td>
			<th>NHIS/HMO ID No*:</th>
			<td><?= $patient->getInsurance()->getEnrolleeId() ?></td>
		</tr>
		<tr>
			<th>D.o.B</th>
			<td><?= date("d/M/Y", strtotime($patient->getDateOfBirth())) ?></td>
			<th>Age</th>
			<td><?= $patient->getAge() ?></td>
		</tr>
		<tr>
			<th>HMO</th>
			<td><?= $patient->getScheme()->getName() ?></td>
			<th>Payer No.*</th>
			<td><?= $patient->getInsurance()->getScheme()->getInsurer()->getName() ?></td>
		</tr>
		<tr>
			<th>Company/Office</th>
			<td><?= $patient->getWorkAddress() ?></td>
			<th>Enrollee Type</th>
			<td></td>
		</tr>
		<tr>
			<th>Occupation</th>
			<td><?= $patient->getOccupation() ?></td>
			<th>Email</th>
			<td style="text-transform: initial;"><?= $patient->getEmail() ?></td>
		</tr>
		<tr>
			<th>Address</th>
			<td><?= $patient->getAddress() ?></td>
			<th>Telephone</th>
			<td><?= $patient->getPhoneNumber() ?></td>
		</tr>
		<tr>
			<th>Date Of Notification</th>
			<td><?= date("d/M/Y", strtotime($e->getStartDate())) ?></td>
			<th></th>
			<td></td>
		</tr>
	</table>

	<section>
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
					<div class="content"><?= $e->getDepartment() ? $e->getDepartment()->getName() : '<ul><li>N/A</li></ul>' ?></div>
				</div>
			</div>
			<div class="span4">
				<div class="e-block">
					<div class="title">Specialization</div>
					<div class="content"><?= $e->getSpecialization() ? $e->getSpecialization()->getName() : '<ul><li>N/A</li></ul>' ?></div>
				</div>
			</div>
		</div>
		<div class="e-block">
			<div class="title">Vital Signs</div>
			<div class="content">
				<?php if (count($vitals) == 0) { ?>
					<ul><li>Not available</li></ul>
				<?php } else { ?>
                    <div class="row">
						<?php foreach ($vitals as $vital) { ?>
                           <div class="span2 icon-border"><strong><?= $vital->getType()->getName() ?></strong>: <?= $vital->getValue() ?><?=  utf8_encode($vital->getType()->getUnit())?></div>
                        <?php } ?>
                    </div>
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


		<div class="e-block">
			<div class="title">Doctor</div>
			<div class="content"><?= (count($e->getPresentingComplaints()) > 0 && $e->getPresentingComplaints()[0]) ? $e->getPresentingComplaints()[0]->doctorName : '<ul><li>N/A</li></ul>' ?></div>
		</div>

		<div class="e-block">
			<div class="title">Signed</div>
			<div class="content">
			<span class="pull-left">
				<?= ($e->getSignedBy() ? $e->getSignedBy()->getFullname() . ' on ' . date(MainConfig::$dateTimeFormat, strtotime($e->getSignedOn())) : 'Not Signed Yet') ?>
			</span>
				<span class="pull-right">
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
	<div class="row-fluid no-print pull-right">
		<div class="span12">
			<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
			   class="action">PDF</a>
			<a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
		</div>
	</div>
</div>
</body>
<script type="text/javascript">
	$(document).ready(function () {
		$('a[href^="/pdf.php"]:first').get(0).click();
		$.blockUI({
			message: '<div class="ball"></div><br><h6 class="fadedText" style="font-size:200%">Generating PDF. Please wait...</h6>',
			css: {
				borderWidth: '0',
				backgroundColor: 'transparent'
			}
		})
	})
</script>
</html>
