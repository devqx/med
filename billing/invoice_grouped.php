<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InvoiceLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimLinesDAO.php';

$currencyTitle = (new CurrencyDAO())->getDefault()->getTitle();
$claim = (new ClaimDAO())->get($_GET['id']);
$patient = (new PatientDemographDAO())->getPatient($claim->getPatient()->getId(), true);
$encounterDate = date(MainConfig::$mysqlDateTimeFormat);


if (!isset($_SESSION)) {
	@session_start();
}
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<?php
	
	$bill_lines = array();
	$claimLine = (new ClaimLinesDAO())->getLines($_GET['id']);
	foreach ($claimLine as $item) {
		$bill_lines[] = $item->getBillLine();
	}
	
	$bill_lines = implode(",", $bill_lines);
	
	?>
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

</head>
<body>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic();
$clinic = (new ClinicDAO())->getClinic(1);
?>

<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container" style="margin:<?= ($c::$useHeader) ? 0 : 2 ?>20px auto 0 auto">

	<div class="text-center text-capitalize">
		<h2>Invoice FORM</h2>
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

	<table class="table table-striped table-bordered">

		<tr>
			<td colspan="4"><h2 align="center">Invoice: #<?= (int)$_GET['id'] ?></h2></td>
		</tr>
		<tr>
			<td colspan="3"><strong>Item / Description</strong></td>
			<td class="amount"><strong>Amount</strong></td>
		</tr>
		<?php
		$outstanding_total = 0;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT bs.name AS bill_source, SUM(b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.bill_id IN (" . $bill_lines . ") AND b.patient_id='". $claim->getPatient()->getId() . "') AND b.transaction_type='credit' AND b.cancelled_on IS NULL GROUP BY bill_source ORDER BY transaction_date DESC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		?>
		<?php if ($stmt->rowCount() == 0) { ?>
			<tr>
				<td colspan="3"><em>No un-invoiced items</em></td>
				<td>&nbsp;</td>
			</tr>
		<?php } else {
			do { ?>
				<tr>
					<td colspan="3"><?= ucwords(str_replace("drugs", "medication", str_replace("_", " ", $row['bill_source']))); ?></td>
					<td class="amount"><?= number_format($row['amount'], 2);
						$outstanding_total += $row['amount']; ?></td>
				</tr>
			<?php } while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			?>
			<tr>
				<td colspan="4" class="amount">
					TOTAL: <?= ucwords(convert_number_to_words($outstanding_total)) ?> <?= $currencyTitle ?>
					(<?= number_format($outstanding_total, 2) ?>)
				</td>
			</tr>
		
		<?php } ?>
	</table>
	<div class="no-print">
		<a data-href="/pdf.php?page=<?= urlencode('/billing/invoice_grouped.php?id=' . (int)$_GET['id']) ?>&title=Invoice<?= (int)$_GET['id'] ?>" class="action"
		   href="javascript:"
		   onclick="printSettings(this)">Save
			as PDF</a>
		<a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
	</div>
	<script type="text/javascript">
		var printSettings = function (e) {
			Boxy.load('/print.dialog.php?url=' + encodeURIComponent($(e).data('href')), {});
			//console.log(e);
		};
	</script>
</div>
</body>
</html>