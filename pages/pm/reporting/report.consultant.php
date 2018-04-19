<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/6/16
 * Time: 8:24 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';

$specializations = (new StaffSpecializationDAO())->getSpecializations();
$staff = (new StaffDirectoryDAO())->getDoctors();
$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? true : false);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$consultants = array();
if ($date === true) {
	$data = (new StaffManager())->getDoctorWhoSawWho($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['specialty_id'], $_REQUEST['staff_id'], $page, $pageSize);
	$totalSearch = $data->total;
	$consultants = $data->data;
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

<form id="filterForm" class="document" method="post" action="/pm/reporting/report.consultant.php">
	<h4>Consultant Report</h4>
	<div class="clearfix filter row-fluid">
		<label class="span2">From<input type="text" name="from" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" id="from" placeholder="Select start date"/></label>
		<label class="span2">To:<input type="text" name="to" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" id="to" placeholder="Select end date" disabled="disabled"/></label>
		<label class="span3">
			Filter by Specialization
			<select id="specialty_id" name="specialty_id" data-placeholder="Select specialty">
				<option></option>
				<?php foreach ($specializations as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['specialty_id']) && $_REQUEST['specialty_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="span3">
			Filter by Doctor
			<select id="staff_id" name="staff_id" data-placeholder="Select staff">
				<option></option>
				<?php foreach ($staff as $k => $refs) { ?>
					<option value="<?= $refs->getId() ?>"<?= isset($_REQUEST['staff_id']) && $_REQUEST['staff_id'] == $refs->getId() ? ' selected="selected"' : '' ?>><?= $refs->getFullname() ?></option>
				<?php } ?>
			</select>
		</label>
		<button class="btn span" id="exportIT" type="button"><i class="fa fa-file-excel-o"></i> Export</button>
		<button type="submit" class="btn span1">Show</button>
	</div>
</form>
<div class="document">
	<?php if (isset($_REQUEST['from']) && isset($_REQUEST['to']) && $_REQUEST['from'] != '') { ?>
		<h3 style="text-align: center">Consultant report for
			<?php if (isset($_REQUEST['specialty_id']) && $_REQUEST['specialty_id'] != '') { ?>
				<br>Specialization: <?= (new StaffSpecializationDAO())->get($_REQUEST['specialty_id'])->getName() ?>
			<?php } ?>
			<?php if (isset($_REQUEST['staff_id']) && $_REQUEST['staff_id'] != '') { ?>
				<br>Doctor: <?= (new StaffDirectoryDAO())->getStaff($_REQUEST['staff_id'])->getFullname() ?>
			<?php } ?>
			<br>PERIOD: <span> [<?php echo date("Y M d", strtotime($_REQUEST['from'])) . ' - ' . (($_REQUEST['to'] == '') ? date('Y M d') : date("Y M d", strtotime($_REQUEST['to']))) ?>]</span></h3>
	<?php } ?>
	<div id="dwsw_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no consultant reports</div>';
		} else { ?>
			<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Consultant Requests</div>
			<table class="table table-striped table-hover no-footer">
				<thead>
				<tr>
					<th>Date</th>
					<th>Doctor</th>
					<th>Specialization</th>
					<th>Department</th>
					<th>Patient</th>
					<th>Scheme</th>
					<th>Amount</th>
				</tr>
				</thead>
				<?php if (isset($consultants) && sizeof($consultants) > 0) {
					for ($i = 0; $i < count($consultants); $i++) { ?>
						<tr>
							<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($consultants[$i]->Date)) ?></td>
							<td><?= $consultants[$i]->Doctor ?></td>
							<td><?= $consultants[$i]->Specialization ?></td>
							<td><?= $consultants[$i]->Department ?></td>
							<td><a href="/patient_profile.php?id=<?= $consultants[$i]->PatientID ?>" target="_blank"><?= $consultants[$i]->Patient ?> (<?= strtoupper($consultants[$i]->Sex{0}) ?>)</a></td>
							<td><?= $consultants[$i]->Scheme ?></td>
							<td><?= $consultants[$i]->Amount ?></td>
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
				$.post('/api/find_consultant.php?from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&specialty_id=<?=(isset($_REQUEST['specialty_id'])) ? $_REQUEST['specialty_id'] : ''?>&staff_id=<?=(isset($_REQUEST['staff_id'])) ? $_REQUEST['staff_id'] : ''?>', {'page': page}, function (s) {
					$('#dwsw_report_container').html(s);
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

		if ($("#from").val().trim() != "") {
			$("#to").removeAttr('disabled');
		}

		$("#specialty_id").select2({
			allowClear: true,
			width: '100%'
		});

		$("#staff_id").select2({
			allowClear: true,
			width: '100%'
		});

		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/excel.php?dataSource=consultant&filename=Consultant_Report&from=<?=(isset($_REQUEST['from'])) ? $_REQUEST['from'] : ''?>&to=<?=(isset($_REQUEST['to'])) ? $_REQUEST['to'] : ''?>&specialty_id=<?=(isset($_REQUEST['specialty_id'])) ? $_REQUEST['specialty_id'] : ''?>&staff_id=<?=(isset($_REQUEST['staff_id'])) ? $_REQUEST['staff_id'] : ''?>', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>
