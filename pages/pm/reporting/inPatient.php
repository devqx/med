<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$ward = (isset($_REQUEST['ward_id']) && !is_blank($_REQUEST['ward_id'])) ? $_REQUEST['ward_id'] : null;
$wards = [];
if (!isset($_GET['outpatient'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
	$wards = (new WardDAO())->getWards();
}
$inPatients = [];
$dates = [date('Y-m-d'), date('Y-m-d')];
if (isset($_REQUEST['from'], $_REQUEST['to'])) {
	$dates = [$_REQUEST['from'], $_REQUEST['to']];
} else {
	$_REQUEST['from'] = $_REQUEST['to'] = date('Y-m-d');
}

$pageSize = 10;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;

if (isset($_GET['view']) && $_GET['view'] == "current") {
	$inPatients = (new InPatientDAO())->getInPatientReport(true, $ward, $dates, $page, $pageSize);
} else if (isset($_GET['view']) && $_GET['view'] == "discharged") {
	$inPatients = (new InPatientDAO())->getInPatientReport($filter = "discharged", $ward, $dates, $page, $pageSize);
} else if (isset($_GET['view']) && $_GET['view'] == "admissions") {
	$inPatients = (new InPatientDAO())->getInPatientReport($filter = "admissions", $ward, $dates, $page, $pageSize);
}
$totalSearch = $inPatients->total;

?>
<div class="mini-tab">
	<a class="tab<?= $_GET['view'] == "current" ? " on" : "" ?>" href="/pm/reporting/inPatient.php?view=current">Current Admission</a>
	<a class="tab<?= $_GET['view'] == "discharged" ? " on" : "" ?>" href="/pm/reporting/inPatient.php?view=discharged">Discharged Patients</a>
	<a class="tab<?= $_GET['view'] == "admissions" ? " on" : "" ?>" href="/pm/reporting/inPatient.php?view=admissions">Admitted Patients</a>
</div>

<div id="admission_container" class="document dataTables_wrapper">
	<div class="row-fluid">
		<div class="span3 input-prepend" style="margin-left: 0;">
			<span class="add-on">From</span>
			<input class="span10" type="text" placeholder="Start Date" name="from" value="<?= $_REQUEST['from'] ?>" id="from">
		</div>
		<div class="span3 input-prepend">
			<span class="add-on">To</span>
			<input class="span10" type="text" placeholder="End Date" name="to" value="<?= $_REQUEST['to'] ?>" id="to" disabled="disabled">
		</div>
		<label class="span4">
			<select name="ward_id">
				<option value="">-- Ward Filter --</option><?php foreach ($wards as $ward) { ?>
					<option value="<?= $ward->getId() ?>"<?php if (@$_REQUEST['ward_id'] === $ward->getId()) { ?> selected="selected"<?php } ?>><?= $ward->getName() ?></option><?php } ?>
			</select>
		</label>
		<button class="btn span2 wide" type="button" id="export">Export</button>
	</div>
	<div id="mainData">
		<table class="table table-hover table-striped">
			<thead>
			<tr>
				<th>Name</th>
				<th>Reason</th>
				<th>On Admission</th>
				<th nowrap>Admitted By</th>
				<th>Discharged</th>
				<th>Ward</th>
				<th nowrap>Bed (Room)</th>
				<th>Scheme</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($inPatients->data as $ip) {
				if ($ip->patient_id) {//$ip = new InPatient();?>
					<tr>
						<td>
							<a class="profile" data-pid="<?= $ip->patient_id ?>" href="/admissions/inpatient_profile.php?pid=<?= $ip->patient_id ?>&aid=<?= $ip->id ?>"><?= $ip->patientName ?> [<?= $ip->sex ?>]</a>
						</td>
						<td><a href="javascript:" title="<?= $ip->reason ?>">Details</a></td>
						<td nowrap><?= date("D d M, Y h:ia", strtotime($ip->date_admitted)) ?> <span am-time-ago="<?= date("c", strtotime($ip->date_admitted)) ?>"></span></td>
						<td nowrap><a href="javascript:" title="<?= $ip->staffName ?>"><?= $ip->username ?></a></td>
						<td nowrap><?= $ip->status == 'Discharged' ? date(MainConfig::$dateTimeFormat, strtotime($ip->date_discharged))  : 'Not [Fully] Discharged' ?></td>
						<td><?= $ip->ward_id ? $ip->wardName : 'N/A' ?></td>
						<td><?= ($ip->bed_id != null ? $ip->bedName . " (" . $ip->roomName . ")" : "") ?>
							<?php if ($ip->bed_id == null) { ?>N/A<?php } ?></td>
						<td><?= $ip->schemeName ?></td>
					</tr>
				<?php }
			} ?>
			</tbody>

		</table>

		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
		<div class="resultsPagerOpen no-footer dataTables_paginate">
			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
			</div>
		</div>
	</div>
</div>
<script>
	var reload = function () {
		url = '/pages/pm/reporting/inPatient.php?' + $.param(postData);
		$("#mainData").load(url + " #mainData>*", "");
	};
	$(document).ready(function () {
		var from = $("#from");
		var to = $("#to");
		from.datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				postData["from"] = $("#from").val();
				if ($input.val().trim() != "") {
					to.val('').removeAttr('disabled');
				}
				else {
					to.val('').attr({'disabled': 'disabled'});
				}
			}
		});
		to.datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: from.val() ? from.val() : false});
			},
			onSelectDate: function (ct, $i) {
				postData["to"] = $("#to").val();
				if (from.val() && to.val()) {
					reload();
				}
			}
		});

		if (from.val().trim() != "") {
			to.removeAttr('disabled');
		}
		$('select[name="ward_id"]').select2({
			placeholder: "Filter list by Ward",
			width: '100%',
			allowClear: true
		}).change(function () {
			postData["ward_id"] = $('select[name="ward_id"]').val();
			postData["page"] = 0;
			reload();
		});
	})
</script>
<script>
	var postData = {
		'page': 0,
		'ward_id': $('select[name="ward_id"]').val(),
		'patient_id': $('input[name="patient_id"]').val(),
		'from': $("#from").val(),
		'to': $("#to").val(),
		'view': '<?=$_GET['view']?>'
	};

	$(document).on('click', '.resultsPagerOpen.dataTables_paginate a.paginate_button', function (e) {
		if (!$(this).hasClass("disabled") && !e.handled) {
			postData["page"] = $(this).data("page");
			reload();
			e.handled = true;
		}
	});

	$(document).on('click', '#export', function (e) {
		if (!e.handled) {
			window.open('/excel.php?dataSource=admissions&filename=Admissions_Report_(<?=$_GET['view']?>)&' + $.param(postData), '_blank');
			e.handled = true;
		}
	});
</script>