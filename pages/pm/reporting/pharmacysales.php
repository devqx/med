<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 11/17/15
 * Time: 12:32 PM
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php');
$from = $_POST['from'];
$to = $_POST['to'];
$pharmacy_id = $_POST['pharmacy_id'];

$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$totalSearch = 0;
$psReport = [];
if ($date === true) {
	$data = (new PrescriptionDataDAO())->getCompletedPrescriptionsByDateRange($page, $pageSize, $pharmacy_id, true, $from, $to);
	$totalSearch = $data->total;
	$psReport = $data->data;
}
?>

<style type="text/css">
	.filter .btn {
		float: right;
		margin-top: 24px;
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

<form id="filterForm" class="document" method="post" action="/pm/reporting/reports.pharmacysales.php">
	<h4>Pharmacy Sales Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span3">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span3">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span4">
			Filter by Pharmacy
			<select id="pharmacy_id" name="pharmacy_id" data-placeholder="Select pharmacy">
				<option></option>
				<?php foreach ($pharmacies as $k => $pharm) { ?>
					<option value="<?= $pharm->getId() ?>"<?= isset($_REQUEST['pharmacy_id']) && $_REQUEST['pharmacy_id'] == $pharm->getId() ? ' selected="selected"' : '' ?>><?= $pharm->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
		<button type="submit" class="btn span1">Show</button>
	</div>
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center">Pharmacy Sales report for
			<?php if (isset($_REQUEST['pharmacy_id']) && $_REQUEST['pharmacy_id'] != '') { ?>
				<br><?= (new ServiceCenterDAO())->get($_REQUEST['pharmacy_id'])->getName() ?>
			<?php } else { ?>All pharmacies<?php } ?>
			<br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>]</span></h3>
	<?php } ?>
</form>
<div>

	<div id="pharmsales_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no pharmacy sales reports</div>';
		} else { ?>
			<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Pharmacy Sales Requests</div>
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th>Request Date</th>
					<th>Filled Date</th>
					<th>Patient</th>
					<th>Drug</th>
					<th>Quantity</th>
					<th>Scheme</th>
					<th class="amount">Estimated Amount</th>
				</tr>
				</thead>
				<?php if (isset($psReport)) {
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
					$iItem = new InsuranceItemsCostDAO();
					foreach ($psReport as $k => $report) { ?>
						<tr>
							<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($report->when)) ?></td>
							<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($report->filled_on)) ?></td>
							<td><span class="profile" data-pid="<?= $report->patient_id ?>"><a href="javascript:"><?= $report->patientName ?></a></span></td>
							<td><?= (is_null($report->drug_id)) ? $report->generic_name : $report->drug_name ?></td>
							<td><?= $report->quantity ?></td>
							<td><?= $report->scheme_name ?></td>
							<td class="amount"><?= !is_null($report->drug_id) ? $report->quantity * $iItem->getItemPriceByCode($report->drug_code, $report->patient_id, true) : 'N/A' ?></td>
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
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('/pages/pm/reporting/pharmacysales.php', {page: page, from: '<?= $_POST['from'] ?>', to: '<?=$_POST['to'] ?>', pharmacy_id: '<?=$_POST['pharmacy_id']?>'}, function (s) {
					$('#pharmsales_report_container').html($(s).find('#pharmsales_report_container').html());
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

		$("#pharmacy_id").select2({
			allowClear: true,
			width: '100%'
		});
		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/excel.php?dataSource=pharmacy_sales&filename=Pharmacy_Sales_Report&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&pharmacy=<?=(isset($_REQUEST['pharmacy_id'])) ? $_REQUEST['pharmacy_id'] : ''?>', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>