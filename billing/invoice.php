<?php
$grouped = isset($_GET['grouped']);

if ($grouped) {
	include_once 'invoice_grouped.php';
	exit;
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InvoiceLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';

$currencyTitle = (new CurrencyDAO())->getDefault()->getTitle();


//$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], TRUE);
if (!isset($_SESSION)) {
	@session_start();
}
?>
<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<?php
	if (!isset($_REQUEST['id'])) {
		$bill_lines = $_REQUEST['bills'];
		// create the invoice
		$invoice_ = new Invoice();
		
		$lines = array();
		foreach (explode(",", $_REQUEST['bills']) as $bill) {
			$line = new InvoiceLine();
			$line->setBill(new Bill($bill));
			
			$lines[] = $line;
		}
		$invoice_->setLines($lines);
		if (isset($_GET['mode']) && $_GET['mode'] == "patient") {
			$invoice_->setPatient(new PatientDemograph($_GET['pid']));
		} else {
			$invoice_->setPatient(null);
		}
		
		if (isset($_GET['mode']) && $_GET['mode'] == "insurance") {
			$invoice_->setScheme((new InsuranceScheme($_GET['sid'])));
		} else {
			$invoice_->setScheme(null);
		}
		
		$invoice_->setCashier(new StaffDirectory($_SESSION['staffID']));
		
		$invoice = (new InvoiceDAO())->create($invoice_);
		if ($invoice === null) {
			exit("Error creating Invoice");
		}
	} else {
		//we are to get the invoice
		$invoice = (new InvoiceDAO())->get($_REQUEST['id']);
		$bill_lines = array();
		
		foreach ($invoice->getLines() as $line) {
			if ($line->getBill()) {
				$bill_lines[] = $line->getBill()->getId();
			}
		}
		$bill_lines = implode(",", $bill_lines);
	}
	//continue to show the invoice
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
	
	<?php if ($invoice->getPatient() != null/* && $_REQUEST['mode']!="insurance"*/) { ?>
		<table class="table-bordered table text-capitalize">
			<tr class="">
				<th class="well">Patient:</th>
				<td><?= $invoice->getPatient()->getFullname() ?></td>
				<th class="well">Sex/Age:</th>
				<td><?= ucfirst($invoice->getPatient()->getSex()) ?>/<?= $invoice->getPatient()->getAge() ?>
				</td>
			</tr>
			<tr class="">
				<th class="well">EMR:</th>
				<td><?= $invoice->getPatient()->getId() ?></td>
				<th class="well">Nationality:</th>
				<td>
					<?= $invoice->getPatient()->getNationality() ? ucfirst($invoice->getPatient()->getNationality()->country_name) : 'N/A' ?>
				</td>
			</tr>
			<tr class="">
				<th class="well">Patient Phone:</th>
				<td><?= $invoice->getPatient()->getPhoneNumber() ?></td>
				<th class="well">DOB:</th>
				<td><?= date("jS M, Y", strtotime($invoice->getPatient()->getDateOfBirth())) ?></td>
			</tr>
			<tr class="">
				<th class="well">Coverage:</th>
				<td>
					<?= $invoice->getPatient()->getScheme()->getType() == 'self' ? "Self Pay" : "Insured" ?>
				</td>
				<th class="well"></th>
				<td></td>
			</tr>
		</table>
	<?php } else { ?>
		<table class="demograph table">
			<tr class="">
				<td>Invoice For:</td>
				<td><?= $invoice->getScheme()->getName(); ?></td>
			</tr>
		</table>
	
	<?php } ?>

	<table class="table table-striped table-bordered">

		<tr>
			<td colspan="4"><h2 align="center">Invoice: #<?= (int)$invoice->getId() ?></h2></td>
		</tr>
		<tr>
			<td colspan="3"><strong>Item/ Description</strong></td>
			<td class="amount"><strong>Amount</strong></td>
		</tr>
		<?php
		$outstanding_total = 0;
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		//                    $sql = "SELECT bs.name AS bill_source, SUM(b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.bill_id IN (".$bill_lines.") /*b.invoiced <> 'yes' OR b.invoiced IS NULL*/) /*AND b.patient_id='" . mysql_real_escape_string($_GET['pid']) . "'*/ AND b.transaction_type='credit' /*AND s.pay_type = 'self'*/ GROUP BY bill_source ORDER BY transaction_date DESC";
		$sql = "SELECT b.description, bs.name AS bill_source, (b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON bs.id=b.bill_source_id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE (b.bill_id IN (" . $bill_lines . ") ) AND (b.transaction_type='credit' OR b.transaction_type='transfer-credit') AND b.cancelled_on IS NULL ORDER BY transaction_date DESC";
		//error_log($sql);
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
					<td colspan="2"><?= ucwords($row['description']); ?></td>
					<td><?= ucwords(str_replace("_", " ", $row['bill_source'])); ?></td>
					<td class="amount"><?= number_format($row['amount'], 2, '.', ',');
						$outstanding_total += $row['amount']; ?></td>
				</tr>
			<?php } while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			?>
			<tr>
				<td colspan="4" class="amount">
					TOTAL: <?= ucwords(convert_number_to_words($outstanding_total)) ?> <?= $currencyTitle ?>
					(<?= number_format($outstanding_total, 2, '.', ',') ?>)
				</td>
			</tr>
		
		<?php } ?>
	</table>

	<div class="no-print">
		<a data-href="/pdf.php?page=<?= urlencode('/billing/invoice' . ($grouped ? '_grouped' : '') . '.php?id=' . (int)$invoice->getId()) ?>&title=Invoice<?= (int)$invoice->getId() ?>" class="action"
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