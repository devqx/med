<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/15/18
 * Time: 8:52 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';


$currency = (new CurrencyDAO())->getDefault();

$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$sources = (new BillSourceDAO())->getBillSources();
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$patientId = !is_blank(@$_POST['patient_id']) ? @$_POST['patient_id'] : null;
$staffId = !is_blank(@$_POST['staff_id']) ? @$_POST['staff_id'] : null;
$bill_source_ids = !is_blank(@$_POST['bill_source_ids']) ? @$_POST['bill_source_ids'] : null;
//$sources = !is_blank($_POST['bill_source_ids']) ? " AND bill_source_id IN (". implode(", ", $_POST['bill_source_ids']) .")" : "";

$date_from = !is_blank(@$_POST['date_from']) ? @$_POST['date_from'] : null;
$date_to = !is_blank(@$_POST['date_to']) ? @$_POST['date_to'] : null;

$data = (new BillDAO())->getUnReviewedBills(TRUE, $page, $pageSize, $patientId, $staffId, $bill_source_ids, $date_from, $date_to);
$totalSearch = $data->total;
?>


	<script src="/assets/jquery-print/jQuery.print.js" type="text/javascript"></script>
<link href="/style/def.css" rel="stylesheet" type="text/css"/>
<link href="/style/google-font.css" rel="stylesheet" type="text/css"/>

<style media="print">
	.table-condensed > thead > tr > th, .table-condensed > tbody > tr > th, .table-condensed > tfoot > tr > th, .table-condensed > thead > tr > td, .table-condensed > tbody > tr > td, .table-condensed > tfoot > tr > td {
		padding: 2px !important;
	}

	.table {
		color: #f00 !important;
	}
</style>
<script>

function showDoc(xg) {
		$(".blockElem.demograph").hide();
		if (xg === "statement") {
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").show();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").show();
			$(".blockElem .filters").show();
			$(".blockElem.unclaimedencountershmo").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
		}
		else if (xg === "payment") {
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").show();
			$(".blockElem.balance").show();
			$(".blockElem.voucher").show();
			$(".blockElem.unclaimedencountershmo").hide();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
		}
		else if (xg === "invoice") {
			$(".blockElem.unclaimedBills").show();
			$(".blockElem.discount").show();
			$(".blockElem.total").show();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").show();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.unclaimedencounters").hide();
			$(".blockElem.claims").hide();
			// $(".blockElem.invoice table").dataTable();
			
		}  else if (xg === "claims") {
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").show();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.unclaimedencounters").hide();
			$(".blockElem.claims").show();
			//$.get('/billing/claims.php?sid=<?//= $_GET['id']?>//&mode=insurance', function (data) {
			//	$('.blockElem.claims').html(data);
			//});
		} else if (xg === "unclaimedencounters") {
			$(".blockElem.unclaimedBills").hide();
			$(".blockElem.discount").hide();
			$(".blockElem.total").hide();
			$(".blockElem.payment").hide();
			$(".blockElem.voucher").hide();
			$(".blockElem.balance").hide();
			$(".blockElem .filters").hide();
			$(".blockElem.statements").hide();
			$(".blockElem.claims").hide();
			$(".blockElem.unclaimedencounters").show();
			//$.get('/billing/unclaimed_encounters_hmo.php?sid=<?//= $_GET['id']?>//&mode=insurance', function (data) {
			//	$('.blockElem.unclaimedencountershmo').html(data);
			//});
		}
	}
	
</script>

<?php if ($patientId == null) { ?>
	<div class="row-fluid">
		<label class="span6">
			Filter by Payer
			<select id="provider" name="provider" data-placeholder="Select provider">
				<option></option>
				<?php foreach ($provider as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['provider']) && $_REQUEST['provider'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
	<label class="span6">
		Filter by Insurance Scheme
		<select id="insurance_scheme_id" name="insurance_scheme_id" data-placeholder="Select insurance scheme">
			<option></option>
			<?php foreach ($insurance_id as $k => $refs) { ?>
				<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['insurance_scheme_id']) && $_REQUEST['insurance_scheme_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
			<?php } ?>
		
		</select>	</label>
	</div>
<?php } ?>
<link href="/style/patient.bill.css" rel="stylesheet">
<div id="insuranceUnclaimedLink" class="blockElem">
 <span class="iTabs">
        <a href="#unclaimedBills" class="active">unclaimed bills</a>
        <a href="#payment">Payments/Vouchers</a>
        <a href="#statement">Statement</a>
        <a href="#claims">Claims</a>
        <a href="#unclaimedencounters">Unclaimed Encounters</a>
    </span>

</div>

<?php
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$patientId = (isset($_GET['pid'])) ? $_GET['pid'] : null;
$dateStart = (isset($_GET['start'])) ? $_GET['start'] : null;
$dateEnd = (isset($_GET['end'])) ? $_GET['end'] : null;

$pageSize = isset($_REQUEST['pageSize']) ? $_REQUEST['pageSize'] : 10;
$outstanding_total = 0;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
$data = (new BillDAO())->getInsuranceUnInvoicedBills($_REQUEST['id'], $page, $pageSize, $patientId, $dateStart, $dateEnd);
$totalSearch = $data->total;
?>

<!-- start unclaimed bills -->

<div id="area11" class="blockElem invoice">
	<div class="row-fluid">
		<h6 class="no-label pull-left span6">Unclaimed Bills: </h6>
		<div class="text-right span4 no-label">Page Size</div>
		<label class="pull-right span2 no-label">
			<select data-placeholder="Page Size" name="pageSize">
				<option>10</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize'] == 20 ? ' selected' : '' ?>>20</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize'] == 50 ? ' selected' : '' ?>>50</option>
				<option<?= isset($_REQUEST['pageSize']) && $_REQUEST['pageSize'] == 100 ? ' selected' : '' ?>>100</option>
			</select>
		</label>
	</div>
	<!--
		<div class="row-fluid">
			<label class="span7"><input type="text" name="patient_id" value="--><?//= (isset($_GET['pid']) ? $_GET['pid'] : '') ?><!--"></label>
		<div class="span5 right">
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span4" type="text" name="date_start" id="date_start" value="<?= isset($_GET['start']) ? $_GET['start'] : '' ?>" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span4" type="text" name="date_end" id="date_end" value="<?= isset($_GET['end']) ? $_GET['end'] : '' ?>" placeholder="End Date">
				<button class="btn" type="button" id="date_filter">Apply</button>
			</div>

		</div>
	</div>
-->
	<p></p>
	<table class="data table table-hover table-striped">
		<thead>
		<tr>
			<th><label><input type="checkbox" id="invoiceAll_Sel"> Bill #</label></th>
			<th>Patient</th>
			<th>Item</th>
			<th>Transaction Date</th>
			<th>Amount</th>
			<th>Responsible</th>
		</tr>
		</thead>
		<?php foreach ($data->data as $row) { ?>
			<!-- else start repeat-->
			<tr>
				<td>
					<label class="nowrap"><input type="checkbox" name="bills[]" value="<?= $row->getId() ?>"> <?= $row->getId() ?>
					</label></td>
				<td><?= ($row->getPatient() != null ? ($row->getPatient()->isActive() ? '<a target="_blank" href="/patient_profile.php?id=' . $row->getPatient()->getId() . '">' . $row->getPatient()->getFullname() . '</a>' : $row->getPatient()->getFullname()) : 'N/A'); ?></td>
				<td><?= $row->getDescription(); ?></td>
				<td><?= date("Y/m/d h:ia", strtotime($row->getTransactionDate())) ?></td>
				<td class="amount"><?= number_format($row->getAmount()); ?></td>
				<td><?= ($row->getReceiver() !== null) ? $row->getReceiver()->getShortname() : '- -'; ?></td>
			</tr>
			<!--end repeat-->
		<?php } ?>
	</table>
	<div class="list11 dataTables_wrapper no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
			results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>
		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0"
			   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
			   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_last"
			   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_next"
			   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>
</div>


<!--  End unclaimed bills-->



<script>
	$(document).ready(function () {
	
	});
	$('select[name="provider"]').select2({
		placeholder: $(this).attr('placeholder'),
		allowClear: true,
		//minimumInputLength: 3,
		width: '100%',
	});
	
	$('select[name="insurance_scheme_id"]').select2({
		placeholder: $(this).attr('placeholder'),
		allowClear: true,
		//minimumInputLength: 3,
		width: '100%',
	});
</script>
