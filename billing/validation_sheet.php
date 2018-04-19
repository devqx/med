<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/5/16
 * Time: 1:45 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';

$AmountShow =  MainConfig::$amountShow;

@session_start();
$items = [];
if (isset($_GET['items']) && isset($_SESSION['checked_items_all'])) {
	$_SESSION['checked_items_all'][] = explode(',', $_GET['items']);
	$items = (array_flatten(@$_SESSION['checked_items_all']));
} else if(isset($_GET['items']) && !isset($_SESSION['checked_items_all'])){
	$items = explode(',', $_GET['items']);
}

$_GET['items'] = $items ? implode(',', $items) : '';

$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], TRUE);
$items = array_filter(explode(",", $_GET['items']));
$bills = [];

if(sizeof($items)==0){
	exit('error:No bill lines selected');
}
foreach ($items as $item) {
	$bills[] = (new BillDAO())->getBill($item, TRUE)->setValidated(TRUE)->update();
}

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
	<link href="/assets/blockUI/growl.ui.css" rel="stylesheet" type="text/css"/>
	<meta name="viewport" content="width=device-width">
	<style>
		.hack { display: none }
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
	<img style="height:100px" src="<?= $patient->getInsurance()->getScheme()->getLogoUrl() ?>">
	<h4><?= $patient->getInsurance()->getScheme() ?></h4>
</div>
<div class="container text-capitalize">
	<table class="table table-bordered">
		<tr>
			<th class="well">NAME</th>
			<td><?= $patient->getFullname() ?></td>
			<th class="well">SEX</th>
			<td><?= $patient->getSex() ?></td>
		</tr>
		<tr>
			<th class="well">ENROLEE ID</th>
			<td><?= $patient->getInsurance()->getEnrolleeId() ?></td>
			<th class="well">DATE OF BIRTH</th>
			<td><?= date("d/M/Y", strtotime($patient->getDateOfBirth())) ?></td>
		</tr>
		<tr>
			<th class="well">HOSPITAL ID</th>
			<td><?= $patient->getId() ?></td>
			<th class="well">COMPANY</th>
			<td><?= $patient->getInsurance()->getScheme()->getInsurer()->getName() ?></td>
		</tr>
		<tr>
			<th class="well">HMO PLAN</th>
			<td><?= $patient->getInsurance()->getScheme()->getName() ?></td>
			<th class="well"></th>
			<td></td>
		</tr>
		<tr>
			<th class="well">PHONE NO.</th>
			<td><?= $patient->getPhoneNumber() ?></td>
			<th class="well">DATE</th>
			<td><?= date("d/M/Y", time()) ?></td>
		</tr>
	</table>
	<p></p>

	<table class="table table-bordered">
		<thead>
		<tr>
			<th class="well">BILL #/REF</th>
			<th>Due Date</th>
			<th class="well">SERVICE</th>
			<th>PA-CODE</th>
			<th class="well <?= $AmountShow == true ? '' : 'hack' ?> ">AMOUNT</th>
		</tr>
		</thead>
		<?php
		$tot = 0;
		foreach ($bills as $bill) {//$bill = new Bill;?>
			<tr>
				<td class="well"><?= abs($bill->getId()) ?></td>
				<td><?= date(MainConfig::$dateTimeFormat,  strtotime($bill->getDueDate())) ?></td>
				<td class="well"><?= (!is_blank($bill->getItemCode() && $bill->getItemCode() != 'MS00001' && $bill->getSource()->getId() != (new BillSourceDAO())->findSourceById(8)->getId()) ? (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($bill->getItemCode(), FALSE)->getItem()->getName() : $bill->getDescription()) ?></td>
				<td><?= $bill->getAuthCode() ?></td>
				<td class="well amount <?= $AmountShow == true ? '' : 'hack' ?>"><?= $bill->getAmount() ?></td>
			</tr>
		<?php
		$tot += floatval($bill->getAmount());
		} ?>
		<tfoot class="<?= $AmountShow === true ? '' : 'hack' ?>">
		<tr>
			<th colspan="4">TOTAL</th>
			<th class="amount"><?= number_format($tot, 2) ?></th>
		</tr>
		<tr>
			<th colspan="4" class="amount"><?= ucwords( (new toWords($tot))->words ) ?></th>
		</tr>
		</tfoot>
	</table>


	<div align="center" style="margin:150px 200px 90px 200px">
		<hr>
		<div>Client's Signature</div>
	</div>

	<div style="text-align: end; margin: 0 200px" class="no-print">
		<img src="<?= (new ClinicDAO())->getClinic(1, FALSE)->getLogoFile() ?>" width="150px">
		<?php	$staffId = !isset($_SESSION['staffID']) ? $_COOKIE['staffID']:$_SESSION['staffID'];?>
		<h4 class="fadedText">
			Validated BY: <?= (new StaffDirectoryDAO())->getStaff($staffId) ?></h4>
		<h4 class="fadedText">For: <?= (new ClinicDAO())->getClinic(1, FALSE)->getName() ?></h4>
	</div>
	<div class="row-fluid no-print pull-right">
		<div class="span12">
			<a
				data-href="/pdf.php?page=<?= urlencode('/billing/validation_sheet.php?pid=' . $_GET['pid'] . '&items=' . $_GET['items']) ?>"
				href="javascript:"
				onclick="printSettings(this)"
				class="action">PDF</a>
			<a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
		</div>
	</div>
</div>
</body>
<script type="text/javascript">
	var printSettings = function(e){
		Boxy.load('/print.dialog.php?url='+ encodeURIComponent($(e).data('href')),{});
		//console.log(e);
	};
	
	$(document).ready(function () {
		$('a[data-href^="/pdf.php"]:first').get(0).click();
	})
</script>
</html>
