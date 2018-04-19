<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/5/16
 * Time: 1:45 PM
 */
$showCoPay = false;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SignatureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimLinesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Signature.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Claim.php';
$claim = (new ClaimDAO())->get($_GET['id']);
$patient = (new PatientDemographDAO())->getPatient($claim->getPatient()->getId(), true);

$encounterDate = date(MainConfig::$mysqlDateTimeFormat);
$encounter = null;
if (isset($_GET['type']) && $_GET['type'] == "op") {
	$encounter_id = $claim->getEncounter()->getId();
	$encounter = (new EncounterDAO())->get($encounter_id, true);
	$encounterDate = $encounter->getStartDate();
} else if (isset($_GET['type']) && $_GET['type'] == "ip") {
	$encounter_id = $claim->getEncounter()->getId();
	$encounter = (new InPatientDAO())->getInPatient($encounter_id, true);
	$encounterDate = $encounter->getDateAdmitted();
}
//get claim active signature.
$signature_id = $claim->getSignature() ? $claim->getSignature()->getId() : null;

$signature = (new SignatureDAO())->get($signature_id);
$bills = [];

$claimLine = (new ClaimLinesDAO())->getLines($_GET['id']);
foreach ($claimLine as $item) {
	$bills[] = (new BillDAO())->getBill($item->getBillLine(), true);
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
	<link href="/assets/blockUI/growl.ui.css" rel="stylesheet" type="text/css"/>
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
<body style="margin: 50px 20px;font-size: 12pt;">


<div class="container">

	<div class="text-center text-capitalize">
		<img align="left" style="height:100px" src="<?= $patient->getInsurance()->getScheme()->getLogoUrl() ?>">

		<img align="right" style="height:100px" src="<?= (new ClinicDAO())->getClinic(1)->getLogoFile() ?>">

		<h2>CLAIM FORM</h2>
	</div>
	<table class="table table-bordered table-striped text-capitalize">
		<tr>
			<th>Patient Name</th>
			<td><?= $patient->getFullname() ?></td>
			<th>Gender</th>
			<td><?= $patient->getSex() ?></td>
		</tr>
		<tr>
			<th>Hospital No.</th>
			<td><?= $patient->getId() ?></td>
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
			<td><?= $claim->getScheme()->getName() ?></td>
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
			<td><?= date("d/M/Y", strtotime($encounterDate)) ?></td>
			<th></th>
			<td></td>
		</tr>
	</table>
	<table class="table table-bordered table-striped">
		<tr><th colspan="2">Consultation Details</th></tr>
		<tr><td>Clinic:</td><td><?= (isset($_GET['type']) && $_GET['type'] == "op") ? $encounter->getSpecialization()->getName() : (!is_blank($_GET['type']) ? "In-Patient" : "- -") ?></td></tr>
		<tr><td>Doctor:</td><td><?= (isset($_GET['type']) && $_GET['type'] == "op") ?
					($encounter->getSignedBy() ? $encounter->getSignedBy()->getFullname() .($encounter->getSignedBy()->getFolioNumber() ? '('. $encounter->getSignedBy()->getFolioNumber().')': '') :'- -') :
					(!is_blank($_GET['type']) ? $encounter->getAdmittedBy()->getFullname() . ( $encounter->getAdmittedBy()->getFolioNumber() ? '('. $encounter->getAdmittedBy()->getFolioNumber() . ')':'') : '- -') ?></td></tr>
		<tr><td>Duration:</td><td><?= (isset($_GET['type']) && $_GET['type'] == "ip") ? date(MainConfig::$dateTimeFormat, strtotime($encounter->getDateAdmitted())) . " &mdash; " . date(MainConfig::$dateTimeFormat, strtotime($encounter->getDateDischarged())) : (!is_blank($_GET['type']) ? date(MainConfig::$dateTimeFormat, strtotime($encounterDate)): ' - -')?></td></tr>
		<?php if(isset($_GET['type']) && !is_blank($_GET['type'])){?>
			<tr><td>Reason:</td><td><?= $claim->getReason()?></td></tr>
		<?php }?>
	</table>

	<table class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>Ass/Working Diagnosis</th>
			<th>Investigations</th>
			<th>Treatment/Plan</th>
		</tr>
		</thead>
		<?php if ($_GET['type'] == "op") { ?>
			<?php
			$investigations = $diagnoses = $plans = $medications = [];
			foreach ($encounter->getDiagnoses() as $pc) {
				$diagnoses[] = $pc->description;
			}
			foreach ($encounter->getPlan() as $pc) {
				$plans[] = $pc->description;
			}
			foreach ($encounter->getPrescriptions() as $pc) {
				$medications[] = $pc->description;
			}
			foreach ($encounter->getInvestigations() as $pc) {
				$investigations[] = $pc->description;
			}
			?>
			<tr>
				<td width="34%"><?= (count($diagnoses) > 0) ? "<ul><li>" . implode("</li><li>", $diagnoses) . "</li></ul>" : 'N/A' ?></td>
				<td width="33%"><?= (count($investigations) > 0) ? "<ul><li>" . implode("</li><li>", $investigations) . "</li></ul>" : 'N/A' ?></td>
				<td width="33%">
					<?= (count($plans) > 0) ? "<ul><li>" . implode("</li><li>", $plans) . "</li></ul>" : 'N/A' ?>
					<?= (count($medications) > 0) ? "<u>Medications</u><ul><li>" . implode("</li><li>", $medications) . "</li></ul>" : 'N/A' ?>
				</td>
			</tr>
		<?php } else if ($_GET['type'] == "ip") { ?>
			<tr>
				<td><?= $encounter->getReason() ?></td>
				<td><?= '' ?></td>
				<td><?= '' ?></td>
			</tr>
		<?php } ?>
	</table>
	
	<?php if (isset($_GET['tabular'])) { ?>
		<table class="table table-bordered">
			<thead>
			<tr><th colspan="5">SERVICES</th></tr>
			<tr>
				<th>BILL #/REF</th>
				<th>SERVICE</th>
				<th>TYPE</th>
				<th>PA-CODE</th>
				<th class="amount">QTY</th>
				<th class="amount">AMOUNT</th>
				<?php if($showCoPay){?><th class="amount">COPAY</th><?php }?>
			</tr>
			</thead>
			<?php
			$tot = 0;
			foreach ($bills as $bill) { //$bill = new Bill;
				if ($bill) {
					?>
					<tr>
						<td><?= $bill->getId() ?></td>
						<td><?= !is_blank($bill->getItemCode()) && $bill->getItemCode() != 'MS00001' && $bill->getSource()->getId() != (new BillSourceDAO())->findSourceById(2)->getId() && $bill->getSource()->getId() != (new BillSourceDAO())->findSourceById(8)->getId() ? (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($bill->getItemCode(), false)->getItem()->getName() : $bill->getDescription() ?></td>
						<td><?= ucwords(str_replace('_', ' ', $bill->getSource()->getName())) ?></td>
						<td><?= wordwrap($bill->getAuthCode(), 20, ' ', true) ?></td>
						<td class="amount"><?= $bill->getQuantity() ?></td>
						<td class="amount"><?= number_format($bill->getAmount(), 2) ?></td>
					<?php if($showCoPay){?><td class="amount"><?= number_format($bill->getCopay(), 2) ?></td><?php }?>
					</tr>
					<?php
					$tot += floatval($bill->getAmount());
				}
			}
			?>
			<tr>
				<td colspan="<?php if($showCoPay){?>5<?php } else {?>4<?php }?>"><b>TOTAL</b></td>
				<td class="amount"><b><?= number_format($tot, 2) ?></b></td>
				<td class="amount">&nbsp;</td>
			</tr>

			<tr>
				<td colspan="<?php if($showCoPay){?>7<?php } else {?>6<?php }?>" class="amount"><b><?= ucwords((new toWords($tot))->words) ?></b></td>
			</tr>
		</table>
	
	<?php } else { ?>
		<div class="row-fluid box tight">
			<div class="span12 bold">SERVICES</div>
		</div>
		<div class="row-fluid box tight bold">
				<div class="span2">BILL #/REF</div>
				<div class="span2">SERVICE</div>
				<div class="span2">TYPE</div>
				<div class="span2">PA-CODE</div>
				<div class="span1 amount">QTY</div>
				<div class="span2 amount">AMOUNT</div>
			<?php if($showCoPay){?><div class="span1 amount">COPAY</div><?php }?>
		</div>
		<?php
		$tot = 0;
		foreach ($bills as $bill) { //$bill = new Bill;
			if ($bill) {
				?>
				<div class="row-fluid box tight">
					<div class="span2"><?= $bill->getId() ?></div>
					<div class="span2"><?= !is_blank($bill->getItemCode()) && $bill->getItemCode() != 'MS00001' && $bill->getSource()->getId() != (new BillSourceDAO())->findSourceById(2)->getId() && $bill->getSource()->getId() != (new BillSourceDAO())->findSourceById(8)->getId() ? (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($bill->getItemCode(), false)->getItem()->getName() : $bill->getDescription() ?></div>
					<div class="span2"><?= ucwords(str_replace('_', ' ', $bill->getSource()->getName())) ?></div>
					<div class="span2"><?= wordwrap($bill->getAuthCode(), 20, ' ', true) ?></div>
					<div class="span1 amount"><?= $bill->getQuantity() ?></div>
					<div class="span2 amount"><?= number_format($bill->getAmount(), 2) ?></div>
					<?php if($showCoPay){?><div class="span1 amount"><?= number_format($bill->getCopay(), 2) ?></div><?php }?>
				</div>
				<?php
				$tot += floatval($bill->getAmount());
			}
		}
		?>
		<div class="row-fluid box tight">
			<div class="span8"><b>TOTAL</b></div>
			<div class="span2 amount"><b><?= number_format($tot, 2) ?></b></div>
			<div class="span2 amount">&nbsp;</div>
		</div>

		<div class="row-fluid">
			<div class="span12 amount"><b><?= ucwords((new toWords($tot))->words) ?></b></div>
		</div>
	<?php } ?>
	<div style="margin-bottom: 50px"></div>
	<div class="row-fluid">
		<div class="span6">
			<hr color="#000" style="margin-top: <?= ($signature !== null) ? '110px' : '60px;' ?>">
			<div class="text-center">Provider Signature</div>
		</div>
		<div class="span6">
			<?php if ($signature !== null) { ?><img style="height:100px; padding-left: 200px;" src="data:image/png;base64,<?= base64_encode($signature->getBlob()) ?>"><?php } ?>
			<hr color="#000" style="margin-top: 60px;">
			<div class="text-center">Patient Signature</div>
		</div>
	</div>

	<div style="margin:200px 0 25px">
		<?php if (isset($_GET['reprint'])) { ?>Disclaimer: The insurance details of the patient might have changed and will not be represented accurately at this moment of reprint<?php } ?>
	</div>
	<div class="row-fluid no-print pull-right">
		<div class="span12">
			<a href="/pdf.php?page=<?= urlencode('/billing/claims_sheet.php?id=' . $_GET['id'] . '&type=' . $_GET['type']. (isset($_GET['tabular']) ? '&tabular' :'')) ?>"
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

