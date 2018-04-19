<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/27/16
 * Time: 3:28 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
$includeServices = false;
$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], TRUE);
$encounter = (new InPatientDAO())->getInPatient($_GET['aid'], TRUE);
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<meta charset="UTF-8">

	<script src="/js/jquery-2.1.1.min.js"></script>
	<script src="/js/jquery-migrate-1.2.1.min.js"></script>
	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
	<link href="/style/def.css" rel="stylesheet" type="text/css"/>
	<link href="/style/bootstrap.css" rel="stylesheet" type="text/css"/>
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
<div class="text-center text-capitalize">
	<img src="<?= $patient->getInsurance()->getScheme()->getLogoUrl() ?>">
	<h2>ADMISSION SUMMARY</h2>
</div>
<div class="container">
	<table class="table table-bordered table-striped text-capitalize">
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
			<th>Date</th>
			<td><?= date("d/M/Y", strtotime($encounter->getDateAdmitted())) ?></td>
			<th></th>
			<td></td>
		</tr>
	</table>
	<table class="table">
		<thead><tr><th>ADMISSION DETAILS</th></tr></thead>
	</table>
	<table class="table table-bordered">
		<thead>
		<tr><th>Admission Date:</th><td><?= date(MainConfig::$dateTimeFormat, strtotime($encounter->getDateAdmitted())) ?></td></tr>
		<tr><th>Admitted By:</th><td><?= $encounter->getAdmittedBy()->getFullname() ?></td></tr>
		<tr><th>Reason:</th><td><?= $encounter->getReason() ?></td></tr>
		<tr><th>Discharged On:</th><td><?= $encounter->getDateDischarged() ? date(MainConfig::$dateTimeFormat, strtotime($encounter->getDateDischarged())) : "N/A" ?></td></tr>
		<tr><th>Discharged By:</th><td><?= $encounter->getDischargedBy() ?  $encounter->getDischargedBy()->getFullname() : 'N/A' ?></td></tr>
		</thead>
	</table>
	
	<table class="table table-bordered">
		<thead><tr><th colspan="2">DIAGNOSES</th></tr></thead>
		<?php if(count($encounter->getDiagnoses()) == 0){?>
			<tr><td colspan="2" class="fadedText">No Diagnoses Available</td></tr>
		<?php }?>
		<?php foreach ($encounter->getDiagnoses() as $item) { //$item = new PatientDiagnosis();?>
			<tr><td><?= ucwords($item->getSeverity()) ?></td><td>[<?= $item->getDiagnosis()->getCode() ?>] <?= $item->getDiagnosis()->getName() ?></td></tr>
		<?php } ?>

	</table>

	<table class="table table-bordered">
		<thead><tr> <th>DISCHARGE NOTE: </th></tr></thead>
		<tr><td><?= $encounter->getDischargeNote() ? $encounter->getDischargeNote() : "N/A" ?></td></tr>
		<?php if ($encounter->getNextAppointment()) { ?>
		<tr><td>Next Appointment:</td><td>Your next appointment with <?= $encounter->getNextAppointment()[0] ? $encounter->getNextAppointment()[0]->getGroup()->getClinic()->getName() : 'N/A' ?>  is <?= $encounter->getNextAppointment() ? date(MainConfig::$dateTimeFormat, strtotime($encounter->getNextAppointment()[0]->getStartTime())) : "N/A" ?></td></tr>
		<?php } ?>
		<?php if($encounter->getNextMedication()) { ?>
		<tr><td>Discharged Medication</td><td>
				<?php foreach ($encounter->getNextMedication()->getData() as $d) { ?>
					<?= $d->getDose() . " " . $d->getGeneric()->getForm() . (($d->getDose() != 1) ? 's' : '') . " of " . ($d->getDrug() != null ? '[' . $d->getDrug()->getName() . '] ' : '') . $d->getGeneric()->getName() . " (" . $d->getGeneric()->getWeight() . ") " . $d->getFrequency() ?>
					for <?= $d->getDuration() ?> days
					<?= !is_blank($d->getComment()) ? '<div class="clear fadedText">' . $d->getComment() . '</div>' : '' ?>
			<?php } ?>
			</td>
		</tr>
		<?php } ?>
	</table>

	<?php if($includeServices){?>
	<table class="table table-bordered">
		<thead><tr><th colspan="3">SERVICES</th></tr></thead>
		<?php
		$tot = 0;
		foreach ($encounter->getBills() as $item) { //$item = new Bill();?>
			<tr>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getTransactionDate())) ?></td>
				<td><?= $item->getDescription()?></td>
				<td class="amount"><?= $item->getAmount()?></td>
			</tr>
		<?php
			$tot += (float)$item->getAmount();
		}?>
		<tfoot>
		<tr>
			<th colspan="2">TOTAL</th>
			<th class="amount"><?= number_format($tot, 2) ?></th>
		</tr>
		<tr>
			<th colspan="3" class="amount"><?= ucwords( (new toWords($tot))->words ) ?></th>
		</tr>
		</tfoot>
	</table>

	<div style="margin-bottom: 50px"></div>
	<div class="row-fluid">
		<div class="span2"></div>
		<div class="span8">
			<hr color="#000">
			<div class="text-center">Patient Signature</div>
		</div>
		<div class="span2"></div>
	</div>

	<?php }?>
	<div class="row-fluid no-print pull-right">
		<div class="span12">
			<a href="/pdf.php?page=<?= urlencode('/admissions/inpatient_summary.php?pid='.$_GET['pid'].'&aid='.$_GET['aid']) ?>"
			   class="action">PDF</a>
			<a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
		</div>
	</div>
	</div>
</body>
<script type="text/javascript">
	$(document).ready(function () {
		$('a[href^="/pdf.php"]:first').get(0).click();
	})
</script>
</html>
