<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/21/15
 * Time: 12:39 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
$pageSize = 10;
$patientId = (isset($_POST['pid'])) ? $_POST['pid'] : null;
$dateStart = (isset($_POST['start'])) ? $_POST['start'] : null;
$dateEnd = (isset($_POST['end'])) ? $_POST['end'] : null;
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$data = (new InPatientDAO())->getInactiveInPatients(TRUE, $page, $pageSize, $patientId, $dateStart, $dateEnd);
$totalSearch = $data->total;
?>
<div id="admission_container" class="dataTables_wrapper">
	<div class="row-fluid">
		<label class="span6"><input type="text" name="patient_id" value="<?= (isset($_POST['pid']) ? $_POST['pid'] : '') ?>"></label>
		<div class="span6">
			<div class="input-prepend">
				<span class="add-on">From</span>
				<input class="span4" type="text" name="date_start" id="date_start" value="<?= isset($_POST['start']) ? $_POST['start'] : '' ?>" placeholder="Start Date">
				<span class="add-on">To</span>
				<input class="span4" type="text" name="date_end" id="date_end" value="<?= isset($_POST['end']) ? $_POST['end'] : '' ?>" placeholder="End Date">
				<button class="btn" type="button" id="date_filter">Apply</button>
			</div>

		</div>
	</div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Patient</th>
			<th>Admitted</th>
			<th>Discharged</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($data->data as $history) {//$history = new InPatient();?>
			<tr>
				<td><span class="profile" data-pid="<?= $history->patient_id ?>"><?= $history->patientName ?></span></td>
				<td><?= date("D d M, Y h:ia", strtotime($history->date_admitted)) ?></td>
				<td><?= date("D d M, Y h:ia", strtotime($history->date_discharged)) ?></td>
				<td>
					<a href="/admissions/inpatient_profile.php?pid=<?= $history->patient_id ?>&aid=<?= $history->id ?>">Open
						Instance</a> |
					<a target="_blank" href="/admissions/inpatient_summary.php?pid=<?= $history->patient_id ?>&aid=<?= $history->id ?>">Discharge Summary</a>
				</td>
			</tr>
		<?php } ?>
	</table>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
		results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
	</div>
	<div class="resultsPagerOpenHistory no-footer dataTables_paginate">
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
</div>

<script>
	var setPatient = function () {
		$('[name="patient_id"]').select2({
			placeholder: "Filter List by Patient EMR or Name",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					}
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		}).change(function (e) {
			if (!e.handled) {
				e.handled = true;
				goTo(0);
			}
		});
	};
	var setDates = function () {
		jQuery('#date_start').datetimepicker({
			format: 'Y/m/d',
			onShow: function (ct) {
				this.setOptions({
					maxDate: jQuery('#date_end').val() ? jQuery('#date_end').val() : false
				})
			},
			timepicker: false
		});
		jQuery('#date_end').datetimepicker({
			format: 'Y/m/d',
			onShow: function (ct) {
				this.setOptions({
					minDate: jQuery('#date_start').val() ? jQuery('#date_start').val() : false
				})
			},
			timepicker: false
		});
	};
	var goTo = function (page) {
		var data = {};
		var pid = $('[name="patient_id"]').select2("val");
		var dates = {start: $('#date_start').val(), end: $('#date_end').val()};
		data.page = page;
		data.ward_id = $('select[name="ward_id"]').val();
		if (pid != "") {
			data.pid = pid;
		}
		if (dates.start != "" && dates.end != "") {
			data.start = dates.start;
			data.end = dates.end;
		}
		$.post('/admissions/homeTabs/admissionHistory.php', data, function (s) {
			$('#admission_container').html(s);
			//setPatient();
			setDates();
		});
	};
	$(document).ready(function () {
		setPatient();
		setDates();
		$('select[name="ward_id"]').select2({
			placeholder: "Filter list by Ward",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				goTo(0);
				e.handled = true;
			}
		});
	}).on('click', '.resultsPagerOpenHistory.dataTables_paginate a.paginate_button', function (e) {
		if (!$(this).hasClass("disabled") && !e.handled) {
			var page = $(this).data("page");
			goTo(page);
			e.handled = true;
		}
	}).on('click', '#date_filter', function (e) {
		if (!e.handled) {
			goTo(0);
			e.handled = true;
		}
	});
</script>