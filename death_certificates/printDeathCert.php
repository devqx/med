<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 7/12/16
 * Time: 12:28 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
if (!isset($_SESSION)) {
	@session_start();
}
$death = (new DeathDAO())->get($_GET['id']);
$this_user = (new StaffDirectoryDAO())->getStaff($death->getValidatedBy()->getId());
$clinic = (new ClinicDAO())->getClinic(1);

if ($death !== null) { ?>
	<!DOCTYPE html>
	<html>
	<head>

		<meta charset="utf-8">
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

	<div class="container" style="width:990px; overflow: hidden; height:auto; padding:5px;  border: 10px solid #787878; margin-top: 50px;">
		<div class="row-fluid">
			<div style="width:auto; height:auto; padding:10px; text-align:center; border: 5px solid #787878">
				<?= ($c::$useHeader) ? $clinic->getHeader() : ''; ?>
					<span style="float: right;">#: <strong><?= $death->getCertNumber() ?></strong></span>
				<span style="font-size: 28px; margin-top: 10px; margin-left: 18%;">Death Certificate</span>

				<div class="content" style="width: auto; line-height: 200%; height: auto; padding: 50px; font-size: medium; font-family: Helvetica Neue Light, HelveticaNeue-Light, Helvetica Neue, Calibri, Helvetica, Arial, sans-serif ">
					<p>
						That I have medically attended to
						<?= $death->getPatient()->getFullname() ?> who was born <?= date('d/m/Y', strtotime($death->getPatient()->getDateOfBirth())) ?> 
						at <?= $death->getPatient()->getState()->getName() ?> in <?= $death->getPatient()->getNationality()->country_name ?>.<br>
						Who was apparently be aged <?= $death->getPatient()->getAge() ?>, that I last saw at <?= date('g:i A', strtotime($death->getTimeOfDeath())) ?> on the <?= date('d/m/Y', strtotime($death->getTimeOfDeath())) ?>
						was then suffering<br> from <?= $death->getDeathCausePrimary() ?>
						died as I am aware, or informed on <?= date('dS', strtotime($death->getTimeOfDeath())) ?>
						day of <?= date('F', strtotime($death->getTimeOfDeath())) ?>,
						at <?= date('g:i A', strtotime($death->getTimeOfDeath())) ?>
						and that the cause of death was to the best of my knowledge and believe as herein stated viz
						<?= $death->getDeathCausePrimary() ?>.
						<?php if ($death->getDeathCausePrimary() !== null) { ?>
					<p>Primary cause: <?= $death->getDeathCausePrimary() ?></p><?php } ?>,
					<?php if ($death->getDeathCauseSecondary() !== null) { ?>
						<p>Secondary cause: <?= $death->getDeathCauseSecondary() ?>.</p><?php } ?>
					<hr>
					<?php if($death->getValidatedBy() !== null){?>
					<p>Witness under my hand this <?= date('D ', strtotime($death->getTimeOfDeath())) . ' ' . date(' dS ', strtotime($death->getTimeOfDeath())) . ' day ' . date('F', strtotime($death->getTimeOfDeath())) . ',' . date(' Y', strtotime($death->getTimeOfDeath())) ?> </p>

					<p>Name: <?= $this_user->getFullname() ?> </p>
					<p>Qualification: <?= $this_user->getProfession() ?></p>
					<p>Address: <?= $clinic->getAddress() ?></p>

					<?php } else {?>
						<div class="warning-bar">Not Validated Yet</div>
					<?php }?>

				</div>
			</div>
		</div>
        <div class="pull-right no-print" style="margin: 20px 0">
            <a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=<?= urlencode($death->getId()) ?>"
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
<?php } ?>
