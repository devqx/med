<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/16/15
 * Time: 9:53 AM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$lab_categories = (new LabCategoryDAO())->getLabCategories();

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';

$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
$page = isset($_POST['page']) ? $_POST['page'] : 0;
$from = $_POST['from'];
$to = $_POST['to'];
$category_id = $_POST['category_id'];
$pageSize = 15;
$totalSearch = 0;
$labReport = [];
if ($date === true) {
	$data = (new PatientLabDAO())->findLabRequestsByDateCategory($from, $to, $category_id, $page, $pageSize, true);
	$totalSearch = $data->total;
	$labReport = $data->data;
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


<form id="filterForm" class="document" method="post" action="/pm/reporting/reports.labs.php">
	<h4>Labs Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span3">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span3">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span4">
			Filter by Category
			<select id="category_id" name="category_id" data-placeholder="Select category">
				<option></option>
				<?php foreach ($lab_categories as $k => $cats) { ?>
					<option value="<?= $cats->getId() ?>"<?= isset($_REQUEST['category_id']) && $_REQUEST['category_id'] == $cats->getId() ? ' selected="selected"' : '' ?>><?= $cats->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
		<button type="submit" class="btn span1">Show</button>
	</div>
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center">Lab report for
			<?php if (isset($_REQUEST['category_id']) && $_REQUEST['category_id'] != '') { ?>
				<br><?= (new LabCategoryDAO())->getLabCategory($_REQUEST['category_id'])->getName() ?>
			<?php } else { ?>All categories<?php } ?>
			<br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>]</span></h3>
	<?php } ?>
</form>

<div>

	<div id="lab_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no lab reports</div>';
		} else { ?>
			<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Lab Requests</div>
			<table class="table table-striped table-hover">
				<thead>
				<tr>
					<th>Request Date</th>
					<th>Specimen Date</th>
					<th>Approved Date</th>
					<th>Lab</th>
					<th>Staff</th>
					<th>Patient</th>
					<th>Referral</th>
					<th>Scheme</th>
					<th class="amount">Amount</th>
				</tr>
				</thead>
				<?php if (isset($labReport)) {
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
					$iItem = new InsuranceItemsCostDAO();
					foreach ($labReport as $k => $report) { ?>
						<tr>
							<td><?= date(MainConfig::$dateTimeFormat, strtotime($report->getLabGroup()->getRequestTime())) ?></td>
							<td><?= $report->getSpecimenDate() ? date(MainConfig::$dateTimeFormat, strtotime($report->getSpecimenDate())) : '' ?></td>
							<td><?= $report->getLabResult() && $report->getLabResult()->getApprovedDate() ? date(MainConfig::$dateTimeFormat, strtotime($report->getLabResult()->getApprovedDate())) : '' ?> </td>
							<td><?= $report->getTest()->getName() ?></td>
							<td><?= $report->getLabGroup()->getRequestedBy()->getFullname() ?></td>
							<td><?= $report->getPatient()->getFullname() ?></td>
							<td><?= $report->getLabGroup()->getReferral() ? $report->getLabGroup()->getReferral()->getName() : '' ?></td>
							<td><?= $report->getPatient()->getScheme()->getName() ?></td>
							<td class="amount"><?= $iItem->getItemPriceByCode($report->getTest()->getCode(), $report->getPatient()->getId(), true) ?></td>
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
				$.post('/pages/pm/reporting/reports.labs.php', {from: '<?= $_POST['from'] ?>', to: '<?=$_POST['to'] ?>', category_id: '<?= $_POST['category_id'] ?>', page: page}, function (s) {
					$('#lab_report_container').html($(s).find('#lab_report_container').html());
				});
			}
//        e.clicked=true;
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

		$("#category_id").select2({
			allowClear: true,
			width: '100%'
		});
		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/excel.php?dataSource=labs&filename=Lab_Report&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&category=<?=(isset($_REQUEST['category_id'])) ? $_REQUEST['category_id'] : ''?>', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>