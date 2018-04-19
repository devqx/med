<?php
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/ReferralsQueueDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';


$refer = (new ReferralsQueueDAO())->filterall($_GET['id']);
$clinic = (new ClinicDAO())->getClinic(1);
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
<body>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$patient = (new PatientDemographDAO())->getPatient($refer->getPatient()->getId(), TRUE);
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<br/>
	<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px"></div>

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
			<th>Referring Doctor</th>
			<td><?= $refer->getDoctor()->getFullName() ?></td>
			<th>Specialization</th>
			<td><?= $refer->getSpecialization() ? $refer->getSpecialization()->getName() : '--' ?></td>
		</tr>
		<tr>
			<th>Date Of Notification</th>
			<td><?= date("d/M/Y", time()) ?></td>
			<th>Referral Date</th>
			<td><?= date("d M, Y h:i A", strtotime($refer->getWhen())) ?></td>
		</tr>
	</table>
	<div style="margin-bottom:30px">&nbsp;</div>
	<div style="margin-bottom:30px; text-align: center;font-weight: bold;">REFERRAL NOTE</div>

	<div class="box" style="border-color: #ddd;">
		<?= $refer->getNote() ?>
	</div>
	
	<div class="pull-right no-print" style="margin: 5px 0">
		<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($refer->getId()) ?>"
		   class="action"><i class="icon-book"></i> PDF</a>
	</div>
</div>

<script>
	function Print() {
		$('.container').print({
			addGlobalStyles: true,
			stylesheet: null,
			rejectWindow: true,
			noPrintSelector: ".no-print",
			iframe: true,
			append: "Generated by MedicPlus"
		});
	}
	$(document).ready(function () {
	});
	$(document).on('keydown', function (e) {
		if (e.ctrlKey && (e.key == "p" || e.charCode == 16 || e.charCode == 112 || e.keyCode == 80)) {
			alert("Please use the Print PDF button below for a better rendering on the document");
			e.cancelBubble = true;
			e.preventDefault();

			e.stopImmediatePropagation();
		}
	});

	$(document).ready(function () {
		$('a[href^="/pdf.php"]:first').get(0).click();
	})
</script>
</body>
</html>