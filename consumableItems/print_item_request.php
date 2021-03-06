<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/16/17
 * Time: 9:32 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$pp = (new PatientItemRequestDAO())->getItemsByCode($_GET['pcode'], TRUE);
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
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<div style="text-align: center; font-size: 28px; margin-top: <?= ($c::$useHeader) ? 0 : 2 ?>30px">
		Details
	</div>
	<br/>
	<div class="row-fluid">
		<div class="span3">Patient's Name:</div>
		<div class="span3"><?= $pp[0]->getPatient()->getFullname() ?></div>
		<div class="span3">Sex/Age:</div>
		<div class="span3"><?= ucfirst($pp[0]->getPatient()->getSex()) ?>/<?= $pp[0]->getPatient()->getAge() ?></div>
	</div>
	<div class="row-fluid">
		<div class="span3">Patient EMR:</div>
		<div class="span3"><?= $pp[0]->getPatient()->getId() ?></div>
		<div class="span3">Nationality:</div>
		<div class="span3"><?= ucfirst($pp[0]->getPatient()->getNationality()->country_name) ?></div>
	</div>
	<div class="row-fluid">
		<div class="span3">Patient Phone:</div>
		<div class="span3"><?= $pp[0]->getPatient()->getPhoneNumber() ?></div>
		<div class="span3">Prescribed By:</div>
		<div class="span3"><?= $pp[0]->getRequestedBy()->getFullname() ?></div>
	</div>
	<div class="row-fluid">
		<div class="span3">Coverage:</div>
		<div class="span3"><?= $pp[0]->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Covered" ?><br>
			(<?= $pp[0]->getPatient()->getScheme()->getName() ?>)
		</div>
		<div class="span3">Request Date:</div>
		<div class="span3"><?= date("d M, Y h:i A", strtotime($pp[0]->getRequestDate())) ?></div>
	</div>
	<div class="row-fluid">
	<div class="span3">Request ID:</div>
	<div class="span3"><?= $pp[0]->getCode() ?></div>
</div>
	<div class="row-fluid">

		<div class="box">
			NOTE: <br>
			<?= $pp[0]->getRequestNote() ?>
		</div>
	</div>

	<div class="box tight">
		<div class="row-fluid">
			<div class="span4"><strong>Item</strong></div>
			<div class="span4"><strong>Generic Name</strong></div>
			<div class="span4"><strong>Filled Date</strong></div>
		</div>
	</div>
	<?php foreach ($pp[0]->getData() as $p){ ?>
				<div class="box tight">
					<div class="row-fluid">
						<div class="span4"><?= is_null($p->getItem()) ? $p->getItem()->getName() : $p->getItem()->getName() ?></div>
						<div class="span4">
								<?= $p->getGeneric() ? $p->getGeneric()->getName() : '' ?>
						</div>
							<div class="span4"><?= $p->getFilledDate() ? date("d M, Y H:i A", strtotime($p->getFilledDate()) ) : 'Nil' ?></div>

					</div>
				</div>
	<?php } ?>

	<div class="pull-right no-print" style="margin: 20px 0">
		<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($pp[0]->getCode()) ?>"
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