<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 9:36 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 100;
$totalSearch = 0;
$revenueReport = array();
$insuranceType = isset($_REQUEST['insurance_type_id']) ? $_REQUEST['insurance_type_id'] : null;

if ($date === true) {
	$data = (new BillDAO())->getBillsByDate($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['transaction_type'], $_REQUEST['payment_method_id'], $_REQUEST['cost_centre_id'], $_REQUEST['bill_source_id'], $_REQUEST['insurance_scheme_id'], $_REQUEST['provider'], $insuranceType, true, $page, $pageSize);
	$totalSearch = $data->total;
	$revenueReport = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Transaction Line(s)</div>
<table class="table table-striped table-hover no-footer">
	<thead>
	<tr>
		<th>Date</th>
		<th>Description</th>
		<th>Patient</th>
		<th>Coverage</th>
		<th>Service</th>
		<th>Transaction Type</th>
		<th>Payment Method</th>
		<th>Amount (<?= $currency ?>)</th>
	</tr>
	</thead>
	<?php if (isset($revenueReport) && sizeof($revenueReport) > 0) {
		foreach ($revenueReport as $k => $report) { ?>
			<tr>
				<td nowrap><?= date('M jS, Y', strtotime($report->Date)) ?></td>
				<td><?= $report->Description ?></td>
				<td><?= ($report->PatientID != null) ? '<a href="/patient_profile.php?id=' . $report->PatientID . '" target="_blank">' . $report->Patient . '</a>' : 'N/A' ?></td>
				<td><?= $report->Coverage ?></td>
				<td><?= ucwords($report->Service) ?></td>
				<td><?= ucwords($report->TransactionType) ?></td>
				<td><?= ($report->PaymentMethod == null) ? '' : $report->PaymentMethod ?></td>
				<td class="amount"><?= number_format(abs($report->Amount), 2) ?></td>
			</tr>
		<?php }
	} ?>
</table>
<div class="list1 dataTables_wrapper no-footer">
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
	<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
		<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
	</div>
</div>
