<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .  '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .  '/classes/DAOs/InsuranceTypeDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$transaction_types = getTypeOptions('transaction_type', 'bills');
$payment_method = (new PaymentMethodDAO())->all();
$cost_centres = (new CostCenterDAO())->all();
$bill_source = (new BillSourceDAO())->getBillSources();
$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$insTypes = (new InsuranceTypeDAO())->all();

$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? TRUE : FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$insuranceType = isset($_REQUEST['insurance_type_id']) ? $_REQUEST['insurance_type_id'] : NULL;
$pageSize = 100;
$totalSearch = 0;
$revenueReport = array();

if ($date === TRUE) {
	$data = (new BillDAO())->getBillsByDate($_REQUEST['from'], $_REQUEST['to'], @$_REQUEST['transaction_type'], @$_REQUEST['payment_method_id'], @$_REQUEST['cost_centre_id'], @$_REQUEST['bill_source_id'], @$_REQUEST['insurance_scheme_id'], @$_REQUEST['provider'], $insuranceType, TRUE, $page, $pageSize);
	$totalSearch = $data->total;
	$revenueReport = $data->data;
}
?>
<style type="text/css">
	.filter .btn {
		float: right;
		margin-top: 24px;
		white-space: nowrap;
	}

	.filter .span1 {
		margin-left: 0;
	}

	#exportIT {
		margin-left: 1%;
		width: 8%;
	}
</style>
<div><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>
<form id="filterForm" class="document" method="post" action="/pm/reporting/bill.php">
	<h4>Revenue Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span2">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span2">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span2">
			Filter by Payer
			<select id="provider" name="provider" data-placeholder="Select provider">
				<option></option>
				<?php foreach ($provider as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['provider']) && $_REQUEST['provider'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span3">
			Filter by Transaction Type
			<select id="transaction_type" multiple name="transaction_type[]" data-placeholder="Transaction Type">
				<option></option>
				<?php
				$types = !is_blank(@$_REQUEST['transaction_type']) ? array_filter(@$_REQUEST['transaction_type']) : [];
				foreach ($transaction_types as $type) {
					echo '<option value="'.$type.'"';
					foreach ($types as $selected){
						if(in_array($type, $types)){ echo ' selected';}
					}
					echo ">".ucwords($type)."</option>";
				} ?>
			</select>
		</label>
		<label class="span3">
			Filter by Payment Method
			<select id="payment_method_id" name="payment_method_id" data-placeholder="Select payment method">
				<option></option>
				<?php foreach ($payment_method as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['payment_method_id']) && $_REQUEST['payment_method_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
	</div>
	<div class="clearfix filter row-fluid">
		<label class="span3">
			Filter by Cost Centre
			<select id="cost_centre_id" name="cost_centre_id" data-placeholder="Select cost centre">
				<option></option>
				<?php foreach ($cost_centres as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['cost_centre_id']) && $_REQUEST['cost_centre_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span2">
			Revenue Head
			<select id="bill_source_id" name="bill_source_id" data-placeholder="Select service">
				<option></option>
				<?php foreach ($bill_source as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['bill_source_id']) && $_REQUEST['bill_source_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= ucwords(str_replace('_', ' ', $refs->getName())) ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span2">
			Insurance Type
			<select id="insurance_type_id" name="insurance_type_id" data-placeholder="--Select--">
				<option></option>
				<?php foreach ($insTypes as $insType) {?>
				<option value="<?= $insType->getId()?>"<?= isset($_REQUEST['insurance_type_id']) && $_REQUEST['insurance_type_id']==$insType->getId()? ' selected':''?>><?= $insType->getName() ?></option>
				<?php }?>
			</select>
		</label>
		<label class="span3">
			Insurance Scheme
			<select id="insurance_scheme_id" name="insurance_scheme_id" data-placeholder="Select insurance scheme">
				<option></option>
				<?php foreach ($insurance_id as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['insurance_scheme_id']) && $_REQUEST['insurance_scheme_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>

			</select>
		</label>
		<button class="btn span1" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
		<button type="submit" class="btn span1">Show</button>
	</div>
</form>
<div class="document">
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center">Revenue report for

			<br>PERIOD:
			<span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>
				]</span></h3>
	<?php } ?>
	<div id="bill_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no revenue reports</div>';
		} else { ?>
			<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Bill Revenue Line(s)</div>
			<table class="table table-striped table-hover no-footer">
				<thead>
				<tr>
					<th>Date</th>
					<th>Description</th>
					<th>Patient</th>
					<th>Coverage</th>
					<th>Service</th>
					<th>Type</th>
					<th>PaymentMethod</th>
					<th>Amount (<?= $currency ?>)</th>
				</tr>
				</thead>
				<?php if (isset($revenueReport) && sizeof($revenueReport) > 0) {
					foreach ($revenueReport as $k => $report) {//$report=new Bill(); ?>
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
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
					results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
				</div>
				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
						records</a>
					<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
						records</a>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('/api/find_bills.php', {
					from:'<?=@$_REQUEST['from']?>',
					to:'<?=@$_REQUEST['to']?>',
					transaction_type:<?=json_encode(@$_REQUEST['transaction_type'])?>,
					payment_method_id:'<?=@$_REQUEST['payment_method_id']?>',
					cost_centre_id:'<?=@$_REQUEST['cost_centre_id']?>',
					bill_source_id:'<?=@$_REQUEST['bill_source_id']?>',
					insurance_scheme_id:'<?=@$_REQUEST['insurance_scheme_id']?>',
					insurance_type_id:'<?=@$_REQUEST['insurance_type_id']?>',
					provider:'<?=@$_REQUEST['provider']?>',
					page: page
				}, function (s) {
					$('#bill_report_container').html(s);
				});
			}
			e.clicked = true;
		}
	});
	$(document).ready(function () {
		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() !== "") {
					$("#to").val('').removeAttr('disabled');
				}
				else {
					$("#to").val('').attr({'disabled': 'disabled'});
				}

			}
		});
		$("#to").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
			},
			onSelectDate: function (ct, $i) {

			}
		});

		if ($("#from").val().trim() !== "") {
			$("#to").removeAttr('disabled');
		}

		$("#transaction_type").select2({
			allowClear: true,
			width: '100%'
		});
		$("#insurance_scheme_id").select2({
			allowClear: true,
			width: '100%'
		});
		$("#payment_method_id").select2({
			allowClear: true,
			width: '100%'
		});
		$("#cost_centre_id").select2({
			allowClear: true,
			width: '100%'
		});
		$("#bill_source_id").select2({
			allowClear: true,
			width: '100%'
		});
		$("#insurance_type_id").select2({
			allowClear: true,
			width: '100%'
		});
		$("#provider").select2({
			allowClear: true,
			width: '100%'
		});

		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/excel.php?dataSource=bill&filename=Bill_Report&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&transaction_type=<?=(isset($_REQUEST['transaction_type'])) ? ( implode(",", @$_REQUEST['transaction_type'])) : ''?>&payment_method_id=<?=(isset($_REQUEST['payment_method_id'])) ? $_REQUEST['payment_method_id'] : ''?>&cost_centre_id=<?=(isset($_REQUEST['cost_centre_id'])) ? $_REQUEST['cost_centre_id'] : ''?>&bill_source_id=<?=(isset($_REQUEST['bill_source_id'])) ? $_REQUEST['bill_source_id'] : ''?>&insurance_scheme_id=<?=(isset($_REQUEST['insurance_scheme_id'])) ? $_REQUEST['insurance_scheme_id'] : ''?>&provider=<?=(isset($_REQUEST['provider'])) ? $_REQUEST['provider'] : ''?>&insurance_type_id=<?=(isset($_REQUEST['insurance_type_id'])) ? $_REQUEST['insurance_type_id'] : ''?>', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>