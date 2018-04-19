<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/14/18
 * Time: 12:58 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
//$protect = new Protect();
//if(!isset($_SESSION)){session_start();}

$pp = (new PrescriptionDAO())->getPrescriptionByCode($_GET['pcode'], true);
$clinic = (new ClinicDAO())->getClinic(1);
//echo json_encode($clinic);
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px">Prescription
		Details
	</div>
	<br/>
	<table class="table table-bordered table-striped text-capitalize">
		<tr>
			<td>Patient's Name:</td>
			<td><?= $pp->getPatient()->getFullname() ?></td>
			<td>Sex/Age:</td>
			<td><?= ucfirst($pp->getPatient()->getSex()) ?>/<?= $pp->getPatient()->getAge() ?></td>
		</tr>
		<tr>
			<td>Patient EMR:</td>
			<td><?= $pp->getPatient()->getId() ?></td>
			<td>Nationality:</td>
			<td><?= ucfirst($pp->getPatient()->getNationality()->country_name) ?></td>
		</tr>
		<tr>
			<td>Patient Phone:</td>
			<td><?= $pp->getPatient()->getPhoneNumber() ?></td>
			<td>Entered By:</td>
			<td><?= $pp->getRequestedBy()->getFullname() ?></td>
		</tr>
		<tr>
			<td>Coverage:</td>
			<td><?= $pp->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Covered" ?>
				(<?= $pp->getPatient()->getScheme()->getName() ?>)
			</td>
			<td>Request Date:</td>
			<td><?= date("d M, Y h:i A", strtotime($pp->getWhen())) ?></td>
		</tr>
		<tr>
			<td>Filled Date:</td>
			<td><?php foreach ($pp->getData() as $p) { ?>
					<?php if (!is_blank($p->getFilledOn())) { ?>
						<?= date(MainConfig::$dateTimeFormat, strtotime($p->getFilledOn())) ?>
					<?php } else { ?>
						Prescription is not Filled yet
					<?php } ?>
				<?php } ?>
			</td>
			<td>Prescription ID:</td>
			<td><?= $pp->getCode() ?></td>
		</tr>
		<tr>
			<td>Prescribed BY</td>
			<td colspan="3"><?= $pp->getPrescribedBy() ?></td>
		</tr>
	</table>
	
	
	<div class="box">
		NOTE: <br>
		<?= $pp->getNote() ?>
	</div>
	<div class="box tight">
		<div class="row-fluid">
			<div class="span3"><strong>Drug Name</strong></div>
			<div class="span3"><strong>Instruction/Dosage</strong></div>
			<div class="span3"><strong>Comment</strong></div>
			<div class="span3"><strong>Quantity</strong></div>
		</div>
	</div>
	<?php if (isset($_GET['grouped']) && $_GET['grouped'] == "false" && isset($_GET['single'])) { ?>
		<?php foreach ($pp->getData() as $presc) { //$presc=new PrescriptionData(); ?>
			<?php if ($presc->getId() == $_GET['single']) { ?>
				<div class="box tight">
					<div class="row-fluid">
						<div
							class="span3"><?= !is_null($presc->getDrug()) ?  $presc->getDrug()->getName() .' ('.$presc->getGeneric()->getWeight().' ' .$presc->getGeneric()->getForm() .')'  :  'N/A'  ?></div>
						<div class="span3">
							<?= $presc->getDose() ?> <?= pluralize(ucwords($presc->getGeneric()->getForm()), $presc->getQuantity()) ?>
							, <?= ucwords(str_replace('x', '', $presc->getFrequency())) ?>
							for <?= $presc->getDuration() ?> Day(s)
						</div>
						<div class="span3"><?= $presc->getComment() ? $presc->getComment() : 'N/A' ?></div>
						<div class="span3"><?= $presc->getQuantity() ? $presc->getQuantity() : "ND" ?></div>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
	<?php } else { ?>
		<?php foreach ($pp->getData() as $presc) { //$presc=new PrescriptionData(); ?>
			<div class="box tight">
				<div class="row-fluid">
					<div
						class="span3"><?= is_null($presc->getDrug()) ? $presc->getGeneric()->getName() : $presc->getDrug()->getGeneric()->getName() . ': ' . $presc->getDrug()->getName() ?></div>
					<div class="span3">

						<?= $presc->getDose() ?> <?= pluralize(ucwords($presc->getGeneric()->getForm()), $presc->getQuantity()) ?>
						, <?= ucwords(str_replace('x', '', $presc->getFrequency())) ?>
						for <?= $presc->getDuration() ?> Day(s)
						<?php if ($presc->getFilledBy()) { ?>
							<span class="block">
						Quantity Dispensed: <?= $presc->getQuantity() ?> <?= pluralize(ucwords($presc->getGeneric()->getForm()), $presc->getQuantity()) ?>
							</span>
						<?php } ?>
					</div>
					<div class="span3"><?= $presc->getComment() ? $presc->getComment() : 'N/A' ?></div>
					<div class="span3"><?= $presc->getQuantity() ? $presc->getQuantity() : 'ND' ?></div>
				
				</div>
			</div>
		<?php } ?>
	<?php } ?>
	<div class="pull-right no-print" style="margin: 20px 0">
		<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($pp->getCode()) ?>"
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