<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/17/14
 * Time: 3:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
//$protect = new Protect();
$scans = (new PatientScanDAO())->getScan($_GET['id']);
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

	<script src="/assets/blockUI/jquery.blockUI.js"></script>
	<script src="/assets/boxy/js/jquery.boxy.js"></script>
	<link rel="stylesheet" href="/assets/boxy/css/boxy.css">
	<script src="/assets/jquery-number-master/jquery.number.js"></script>
	<script type="text/javascript" src="/assets/select2_2/select2.min.js"></script>
	<link rel="stylesheet" href="/assets/select2_2/select2.css">
	
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px">Radiology
		Investigation
	</div>
	<br/>
	<table class="table table-bordered table-striped">
		<tbody>
		<tr>
			<td>Patient's Name:</td>
			<td><?= $scans->getPatient()->getFullname() ?></td>
			<td>Sex/Age:</td>
			<td><?= ucfirst($scans->getPatient()->getSex()) ?>/<?= $scans->getPatient()->getAge() ?></td>
		</tr>
		<tr>
			<td>Patient EMR:</td>
			<td><?= $scans->getPatient()->getId() ?></td>
			<td>Nationality:</td>
			<td><?= ucfirst($scans->getPatient()->getNationality()->country_name) ?></td>
		</tr>
		<tr>
			<td>Patient Phone:</td>
			<td><?= $scans->getPatient()->getPhoneNumber() ?></td>
			<td>Request Date:</td>
			<td><?= date("dS M, Y h:i A", strtotime($scans->getRequestDate())) ?></td>
		</tr>
		<tr>
			<td>Coverage:</td>
			<td><?= $scans->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Insured" ?></td>
			<td>Approved By:</td>
			<td><?= ($scans->getApproved()) ? $scans->getApprovedBy()->getFullname() : 'Pending' ?></td>
		</tr>
		<tr>
			<td>Request ID:</td>
			<td><?= $scans->getRequestCode() ?></td>
			<td>Approved Date:</td>
			<td><?= date("dS M, Y h:i A", strtotime($scans->getApprovedDate())) ?></td>
		</tr>
		<tr>
			<td>Referred By:</td>
			<td
				colspan="3"><?= (($scans->getReferral() != null) ? $scans->getReferral()->getName() . " [" . $scans->getReferral()->getCompany()->getName() . "]" : '-') ?></td>
		</tr>
		<tr>
			<td>Examinations Requested:</td>
			<td colspan="3"><?php
				$reqs = [];
				//foreach ($scans->getScan() as $scan) {
					$reqs[] = $scans->getScan()->getName();
				//}
				echo '<strong>' . implode(", ", $reqs) . '</strong>';
				?></td>
		</tr>
		</tbody>
	</table>

	<?php foreach ($scans->getNotes()['reports'] as $note) {
//        $note = new PatientScanNote();
		?>
		<div class="box" style="font-size: 110%">
			<div class="row-fluid">
				<div class="span12"><?= $note->getNote() ?>
					<p class="clear"></p></div>
			</div>
		</div>
	<?php } ?>
	<?php if ($scans->getApproved()) { ?>
		<table class="table table-borderless" style="margin-top:100px;width:100%">
			<tr>
				<td style="border-top: none">
					<span class="pull-right">REPORTED BY: <?= $scans->getApprovedBy()->getFullname() ?></span>
				</td>
			</tr>
		</table>
	<?php } ?>
	<div class="pull-right no-print" style="">
		<!--<a href="javascript:Print();" class="action" title="Print this Lab Result">
				<i class="icon-print"></i> Print</a>-->
		<a href="javascript:"
		   onclick="printSettings(this)"
		   data-href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($scans->getRequestCode()) ?>"
		   class="action"><i class="icon-book"></i> PDF</a>
	</div>

</div>
</body>
<script>
	var printSettings = function(e){
		Boxy.load('/print.dialog.php?url='+ encodeURIComponent($(e).data('href')),{});
		//console.log(e);
	};

	$(document).on('keydown', function (e) {
		if (e.ctrlKey && (e.key === "p" || e.charCode === 16 || e.charCode === 112 || e.keyCode === 80)) {
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
</html>