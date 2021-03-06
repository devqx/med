<?php
if (!isset ($_SESSION)) {
	session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDemographDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/ClinicDAO.php";
$hosp = (new ClinicDAO())->getClinic(1);
$server = ($_SERVER['HTTPS'] ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . '/patient_profile.php?id=' . $_GET['pid'];
$img = shell_exec("qrencode --output=- -m 1 -s 4 $server");

$imgData = "data:image/png;base64," . base64_encode($img);
$pat = (new PatientDemographDAO())->getPatient($_GET['pid'], false, null, null);
?>
<script type="text/javascript" src="/assets/jquery-print/jQuery.print.js"></script>
<script>
	$(document).ready(function () {
		$("#idc").live("click", function (e) {
			if (!e.handled) {
				$("#idCard").print({
					addGlobalStyles: true,
					stylesheet: null,
					rejectWindow: true,
					noPrintSelector: ".no-print",
					iframe: true,
					append: "Generated by MedicPlus"
				});
				e.handled = true;
			}
		});
	});
</script>
<div id="idCard">
	<table class="table table-borderless">
		<tr>
			<td colspan="2"><h5 class="center">PATIENT IDENTIFICATION CARD</h5></td>
		</tr>
		<tr>
			<td style="width: 80%" rowspan="2">
				<table class="table">
					<tr>
						<td><h5>ID #:</h5><?= $pat->getId() ?></td>
					</tr>
					<tr>
						<td><h5>Name:</h5><?= $pat->getFullname() ?></td>
					</tr>
					<tr>
						<td><h5>Sex:</h5><?= $pat->getSex() ?></td>
					</tr>
					<tr>
						<td><h5>Date of Birth:</h5><?= date("Y-d-M", strtotime($pat->getDateOfBirth())) ?></td>
					</tr>
				</table>

			</td>
			<td style="width: 20%"><img src="<?= $pat->getPassportPath() ?>" width="124"></td>
		</tr>
		<tr>
			<td><img id="emrQRCode" src="<?= $imgData ?>"></td>
		</tr>
		<tr>
			<td colspan="2"><em>Issued At: <?= $hosp->getName() ?></em></td>
		</tr>
	</table>
	<a href="javascript:void(0)" class="pull-right print no-print action" id="idc"><i class="icon-print"></i> Print</a>
	<a class="pull-right print no-print action" href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>"><i class="icon-book"></i> PDF</a>
</div>

