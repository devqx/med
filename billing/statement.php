<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$clinic = (new ClinicDAO())->getClinic(1);

$sid = $_GET['sid'];
$from = !is_blank($_GET['date_from']) ? $_GET['date_from'] : null;
$to = !is_blank($_GET['date_to']) ? $_GET['date_to'] : null;
$tType = @$_GET['type'] != 'null' ? array_filter(explode(",", @$_GET['type'])) : [];
$mode = $_GET['mode'];
$sources = !is_blank($_GET['bill_source_ids'] && $_GET['bill_source_ids'] != "null") ? " AND bill_source_id IN (" . $_GET['bill_source_ids'] . ")" : "";
$patientId = !is_blank(@$_GET['patient_id']) ? " AND b.patient_id = " . @$_GET['patient_id'] : "";
$unclaimed = (!is_blank($_GET['claimed_state']) && $_GET['claimed_state'] == "unclaimed") ?  "AND b.claimed=FALSE" : "";
switch ($mode) {
	case 'insurance':
		$sql = "SELECT b.*, s.name AS bill_source_name, CONCAT_WS('', i.policy_number, i.enrollee_number) AS enrolled_id FROM bills b LEFT JOIN bills_source s ON b.bill_source_id=s.id LEFT JOIN insurance i ON i.patient_id = b.patient_id WHERE b.billed_to = $sid /*AND cancelled_on IS NULL*/ {$patientId}{$sources}{$unclaimed}" . (isset($_GET['grouped']) ? ' GROUP BY bill_source_id' : '');
		break;
	case 'patient':
		$sql = "SELECT b.*, s.name AS bill_source_name, CONCAT_WS('', i.policy_number, i.enrollee_number) AS enrolled_id FROM bills b LEFT JOIN bills_source s ON b.bill_source_id=s.id LEFT JOIN insurance_schemes ON insurance_schemes.id = b.billed_to LEFT JOIN insurance i ON i.patient_id=b.patient_id WHERE b.patient_id = $sid AND insurance_schemes.pay_type = 'self' /*AND cancelled_on IS NULL*/ {$patientId}{$sources}{$unclaimed}" . (isset($_GET['grouped']) ? ' GROUP BY b.bill_source_id' : '');
		break;
}
/*if ($tType != "---" && $tType != "") {
	//$sql .= " AND transaction_type ='$tType'";
}*/

if (count($tType) !== 0) {
	$sql .= " AND transaction_type IN ('" . implode("','", $tType) . "')";
}

if ($from != null && $to == null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE('$from') AND Date(NOW())";
} else if ($from == null && $to != null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE(NOW()) AND DATE('$to')";
} else if ($from != null && $to != null) {
	$sql .= " AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE('$to')";
}
$sql .= "$sources ORDER BY transaction_date DESC, bill_id DESC";

$pdo = (new MyDBConnector())->getPDO();
$sid = trim(escape($sid));
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

$tot_amount = $tot_amount_filtered = [];
$outstanding_total = 0;
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

		hr {
			margin: 5px 0 !important;
		}
	</style>
</head>
<body>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
$c = new Clinic(); ?>
<?= ($c::$useHeader) ? $clinic->getHeader() : '' ?>
<div class="container">
	<div class="row-fluid">

		<div class="span12">
			<?php if ($_REQUEST['mode'] == "insurance") { ?>
				<h3 style="text-align: center">Insurance Statement
					For <?= (new InsuranceSchemeDAO())->get($sid)->getName(); ?>
					<br>PERIOD:
					<span> [<?php if ($from == null && $to == null) {
							echo 'All';
						} else { ?><?= ($from == null) ? date(MainConfig::$dateFormat) : date(MainConfig::$dateFormat, strtotime($from)); ?>
							- <?= ($to == null) ? date(MainConfig::$dateFormat) : date(MainConfig::$dateFormat, strtotime($to));
						} ?>]
            </span>
					<br>Filters: <?= (count($tType) !== 0) ? ucwords(implode("s, ", $tType) . "s") : 'None' ?>
				</h3>
			<?php } elseif ($_REQUEST['mode'] == "patient") {
				$patient = (new PatientDemographDAO())->getPatient($sid); ?>
				<h3 style="text-align: center">Bill Statement
					For <?= $patient->getFullname(); ?> (EMR #: <?= (int)$patient->getId() ?>)<br>PERIOD:
					<span> [<?php if ($from == null && $to == null) {
							echo 'All';
						} else { ?><?= ($from == null) ? date(MainConfig::$dateFormat) : date(MainConfig::$dateFormat, strtotime($from)); ?> - <?= ($to == null) ? date(MainConfig::$dateFormat) : date(MainConfig::$dateFormat, strtotime($to));
						} ?>]</span>
					<br>Filters: <?= (count($tType) !== 0) ? ucwords(implode("s, ", $tType) . "s") : 'None' ?>
				</h3>
			<?php } ?>
		</div>
	</div>
	<hr>
	<?php if ($_REQUEST['mode'] == "insurance") { ?>
		<?php if (isset($_GET['grouped'])) { ?>
			<div class="">
				<div class="row-fluid">
					<div class="span4">Patient</div>
					<div class="span1">EnrolleeID</div>
					<div class="span4">Item</div>
					<div class="span3">Amount(<?= $currency->getSymbolLeft() ?>)</div>
				</div>
				<!--if num_rows == 0-->
				<?php if ($stmt->rowCount() == 0) { ?>
					<div class="row-fluid">
						<div class="span12"><em>No bill items matching the filter </em></div>
					</div>
					<!--end if-->
				<?php } else {
					do { ?>
						<!-- else start repeat-->
						<div class="row-fluid">
							<div class="span4"><?= $row['patient_id'] != null ? (new PatientDemographDAO())->getPatient($row['patient_id'], false, null, true)->getShortName() : '' ?></div>
							<div class="span1"><?= $row['enrolled_id'] != null ? $row['enrolled_id'] : '' ?></div>
							<div class="span4"><?= $row['bill_source_name'] ?></div>
							<div class="span3 amount"><?= number_format(abs($row['amount']), 2); ?></div>
						</div>
						<!--end repeat-->
						<?php $tot_amount[] = $row['amount'];
					} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
				} ?>
				<div class="row-fluid">
					<h4 class="span12 amount">
						TOTAL: <?= ucwords((new toWords(array_sum($tot_amount)))->words) ?>
						(<?= number_format(array_sum($tot_amount), 2); ?>)
					</h4>
				</div>
			</div>
		
		<?php } else { ?>
			<div class="">
				<div class="row-fluid">
					<!-- <div class="span1 hide">Bill Item Code</div>
					<div class="span1">Reference</div>-->
					<div class="span3">Patient</div>
					<div class="span2">EnrolleeId</div>
					<div class="span3">Item</div>
					<div class="span2">Date</div>
					<!-- <div class="span2">Transaction Type</div>-->
					<div class="span1 nowrap">Amount(<?= $currency ?>)</div>
					<div class="span1">Auth Code</div>
				</div>
				<!--if num_rows == 0-->
				<?php if ($stmt->rowCount() == 0) { ?>
					<div class="row-fluid">
						<div class="span12"><em>No bill items matching the filter </em></div>
					</div>
					<!--end if-->
				<?php } else {
					do { ?>
						<!-- else start repeat-->
						<div class="row-fluid">
							<!-- <div class="span1"><?= $row['bill_id']; ?></div>
							<div class="span1"><?= $row['payment_reference']; ?></div>-->
							<div class="span3"><?= $row['patient_id'] != null ? (new PatientDemographDAO())->getPatient($row['patient_id'], false, null, true)->getFullName() : '' ?></div>
							<div class="span2"><?= $row['enrolled_id'] != null ? wordwrap($row['enrolled_id'], 20, ' ', true) : '' ?></div>
							<div class="span3"><?= $row['description'] ?></div>
							<div class="span2"><?= date(MainConfig::$dateTimeFormat, strtotime($row['transaction_date'])); ?></div>
							<!-- <div class="span2"><?= strtoupper($row['transaction_type']); ?></div> -->
							<div class="span1">
								<div class="amount"><?= number_format(abs($row['amount']), 2); ?></div>
							</div>
							<div class="span1"><?= wordwrap($row['auth_code'], 20, ' ', true) ?></div>
						</div>
						<!--end repeat-->
						<?php $tot_amount[] = $row['amount'];
					} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
				} ?>
				<div class="row-fluid">
					<h4 class="span12 amount">
						TOTAL: <?= ucwords((new toWords(array_sum($tot_amount)))->words) ?>
						(<?= number_format(array_sum($tot_amount), 2); ?>)
					</h4>
				</div>
			</div>
		<?php } ?>
	
	
	<?php } elseif ($_REQUEST['mode'] == "patient") { ?>
		<?php if (isset($_GET['grouped'])) { ?>
			<div class="row-fluid">
				<div class="span6">Item</div>
				<div class="span6" style="white-space: nowrap;">Amount(<?= $currency ?>)</div>
			</div>
			<hr>
			<!--if num_rows == 0-->
			<?php if ($stmt->rowCount() == 0) { ?>
				<div class="row-fluid">
					<div class="span12"><em class="warning-bar">No bill items matching the filter</em></div>
				</div>
				<!--end if-->
			<?php } else {
				do { ?>
					<!-- else start repeat-->
					<div class="row-fluid">
						<div class="span6"><?= $row['bill_source_name']; ?></div>
						<div class="span6"><span class="amount pull-right"><?= number_format(abs($row['amount']), 2); ?></span>
						</div>
					</div>
					<!--end repeat-->
					<?php $tot_amount[] = ($row['amount']);
				} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			} ?>
		<?php } else { ?>
			<div class="row-fluid">
				<!-- <div class="span1">Bill Item Code</div>-->
				<!--<div class="span1">Reference</div>-->
				<div class="span4"></div>
				<div class="span2"></div>
				<div class="span1"></div>
				<div class="span2" style="white-space: nowrap;">Amount (<?= $currency ?>)</div>
				<div class="span1"></div>
				<div class="span2"></div>
			</div>
			<div class="row-fluid">
				<!-- <div class="span1">Bill Item Code</div>-->
				<!--<div class="span1">Reference</div>-->
				<div class="span4">Item</div>
				<div class="span2">Date</div>
				<div class="span1 small">Type</div>
				<div class="span1 small" style="white-space: nowrap;">DR (<?= $currency ?>)</div>
				<div class="span1 small" style="white-space: nowrap;">CR (<?= $currency ?>)</div>
				<div class="span1">Balance</div>
				<div class="span2">Auth Code</div>
			</div>
			<hr>
			<!--if num_rows == 0-->
			<?php if ($stmt->rowCount() == 0) {
				?>
				<div class="row-fluid">
					<div class="span12"><em class="warning-bar">No bill items matching the filter</em></div>
				</div>
				<!--end if-->
			<?php } else {
				$patient = (new PatientDemographDAO())->getPatient($sid, false, $pdo);
				$real_balance = $patient->getOutstanding();
				$credit = 0;
				$debit = 0;
				do {
					$real_balance = $real_balance - $row['amount'];
					$debit += $row['amount'] < 0 ? abs($row['amount']) : 0;
					$credit += $row['amount'] >= 0 ? abs($row['amount']) : 0;
					?>
					<!-- else start repeat-->
					<div class="row-fluid<?php if (count($tType) !== 0 && !in_array($row['transaction_type'], $tType)) { ?> hide<?php } ?>">
						<!--<div class="span1"><?= (int)$row['bill_id']; ?></div>
						<div class="span1"><?= $row['payment_reference']; ?></div>-->
						<div class="span4"><?= $row['description']; ?></div>
						<div class="span2"><?= date(MainConfig::$dateTimeFormat, strtotime($row['transaction_date'])); ?></div>
						<div class="span1 small"><?= explode("-", strtoupper($row['transaction_type']))[0]; ?></div>

						<div class="span1 small"><span class="amount pull-right"><?= $row['amount'] < 0 ? number_format(abs($row['amount']), 2) : '--'; ?></span></div>
						<div class="span1 small"><span class="amount pull-right"><?= $row['amount'] >= 0 ? number_format(abs($row['amount']), 2) : '--'; ?></span></div>
						<div class="span1 small"><span class="amount pull-right" style="white-space: nowrap"><?= number_format(($real_balance + $row['amount']), 2); ?></span></div>
						<div style="text-wrap: normal" class="span2"><?= wordwrap($row['auth_code'], 20, ' ', true) ?></div>
					</div>
					<!--end repeat-->
					<?php if (count($tType) !== 0 && !in_array($row['transaction_type'], $tType)) {
						$tot_amount_filtered[] = ($row['amount']);
					} else {
						$tot_amount[] = ($row['amount']);
					}
				} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
				?>
				<hr>
				<div class="row-fluid">
					<div class="span4">TOTALS</div>
					<div class="span2"></div>
					<div class="span1 small"></div>

					<div class="span1 small"><span class="amount pull-right"><strong><?= number_format(abs($debit), 2) ?></strong></span></div>
					<div class="span1 small"><span class="amount pull-right"><strong><?= number_format(abs($credit), 2) ?></strong></span></div>
					<div class="span1"><span class="amount pull-right"></span></div>
					<div style="text-wrap: normal" class="span2"></div>
				</div>
				<?php
			} ?>
		<?php } ?>
		<hr>
		<?php if (count($tType) !== 0) { ?>
			<div class="row-fluid">
				<span class="pull-left">F. STATEMENT TOTAL:</span>
				<div class="pull-right amount">
					<?= ucwords((new toWords(array_sum($tot_amount_filtered)))->words) ?>
					(<?= number_format(array_sum($tot_amount_filtered), 2); ?>)
				</div>
			</div>
		<?php } else { ?>
			<div class="row-fluid">
				<span class="pull-left">STATEMENT TOTAL: </span>
				<div class="pull-right amount">
					<?= ucwords((new toWords(array_sum($tot_amount)))->words) ?>
					(<?= number_format(array_sum($tot_amount), 2); ?>)
				</div>
			</div>
		<?php } ?>
		<hr>
		<?php $patient = (new PatientDemographDAO())->getPatient($sid); ?>
		<div class="row-fluid">
			<span class="pull-left">PATIENT OUTSTANDING BALANCE: </span>
			<div class="pull-right amount">
				<?= ucwords((new toWords($patient->getOutstanding()))->words) ?>
				(<?= number_format($patient->getOutstanding(), 2); ?>)
			</div>
		</div>
		<hr>
	
	<?php } ?>
	<div class="row-fluid no-print">
		<div class="span12">
			<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>"
			   class="action">PDF</a>
			<a href="javascript:;" onclick="window.print()" class="action"><i class="icon-print"></i> Print</a>
		</div>
	</div>
</div>
</body>
</html>
