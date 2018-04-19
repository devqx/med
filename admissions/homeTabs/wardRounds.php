<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/7/15
 * Time: 11:56 AM
 */

$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.admissions.php';

$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$types = array_col($vitalTypes, 'name');
$types[] = "Medication";
$types[] = "Others";
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$ward = (isset($_POST['ward_id']) && !is_blank($_POST['ward_id'])) ? $_POST['ward_id'] : null;
$patient_type = (isset($_GET['outpatient']) && $_GET['outpatient'] === "true") ? 'op' : 'ip';
$taskType = (isset($_POST['task_type']) && !is_blank($_POST['task_type'])) ? $_POST['task_type'] : null;
$pageSize = 10;
$patient = (!is_blank(@$_REQUEST['patient_id'])) ? @$_REQUEST['patient_id'] : null;
$data = (new ClinicalTaskDataDAO())->getAllClinicalTaskDatumSlim($page, $pageSize, $patient_type, ['Active'], TRUE, $ward, $patient, $taskType);
$wards = [];
if (!isset($_GET['outpatient'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
	$wards = (new WardDAO())->getWards();
}

$rounds = $data->data;
$totalSearch = $data->total;
?>
<div id="admission_"><p></p>
	<div class="row-fluid">
		<?php if (!isset($_GET['outpatient'])) { ?>
			<label class="span4">
				<select name="ward_id">
					<option value="">-- Ward Filter --</option><?php foreach ($wards as $ward) { ?>
						<option value="<?= $ward->getId() ?>"<?php if (@$_POST['ward_id'] === $ward->getId()) { ?> selected="selected"<?php } ?>><?= $ward->getName() ?></option><?php } ?>
				</select>
			</label>
		<?php } ?>
		<label class="span4">
			<input type="hidden" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
		</label>
		<label class="span4">
			<select name="task_type" data-placeholder="-- Filter by Type --">
				<option></option><?php foreach ($types as $type) { ?>
					<option value="<?= $type ?>"<?php if (@$_POST['task_type'] === $type) { ?> selected="selected"<?php } ?>><?= $type ?></option><?php } ?>
			</select>
		</label>
	</div>
	<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Clinical tasks</div>

	<table id="wardRoundsTable" class="table table-striped table-hover no-footer">
		<thead>
		<tr>
			<th>Patient</th>
			<?php if (!isset($_GET['outpatient'])) { ?>
				<th>Bed/Ward</th><?php } ?>
			<th>Task</th>
			<th nowrap>Read Count</th>
			<th nowrap>Last Read</th>
			<th nowrap>Next Due</th>
			<th>Count</th>
			<th>**</th>
			<th class="hide">*</th>
		</tr>
		</thead>
		<tbody>
		<?php
		//$round = new ClinicalTask();
		foreach ($rounds as $k => $work) {
			$frequency = convert_minutes_to_readable($work->frequency);
			?>
			<tr>
				<td>
					<?php if ($work->in_patient_id === null) { ?>
						<a href="/patient_profile.php?id=<?= $work->patient_id ?>"><?= $work->patientName ?></a>
					<?php } else { ?>
						<a href="/admissions/inpatient_profile.php?pid=<?= $work->patient_id ?>&aid=<?= $work->in_patient_id ?>"><?= $work->patientName ?></a>
					<?php } ?>
				</td>
				<?php if (!isset($_GET['outpatient'])) { ?>
					<td>
						<?= ($work->in_patient_id != null && $work->bed_id != null ? $work->bedName . " (" . $work->wardName . ")" : "N/A") ?>
					</td>
				<?php } ?>
				<td>
					<?php if ($work->type_id == null && ($work->drug_id == null && $work->drug_generic_id == null)) {//others?>
						<?= $work->description . " every " . $frequency ?>
						<?php
					} else if ($work->type_id != null && $work->drug_id == null && $work->drug_generic_id == null) {//vitals?>
						<?= "Check " . $work->type_name . " every " . $frequency ?>
						<?php
					} else if ($work->type_id == null && ($work->drug_generic_id != null || $work->drug_id != null) && $work->in_patient_id !== null) {
						// inpatient medication?>
						Give <?= $work->dose ?> <?= ($work->drug_id == null) ? $work->drugGenericForm : $work->drugGenericForm ?>
						of <?= ($work->drug_id == null) ? $work->genericName : $work->drugName ?> <?= ($work->drug_id == null) ? $work->drugGenericWeight : $work->drugGenericWeight ?>
						every <?= convert_minutes_to_readable($work->frequency) ?>
						<?php
					} else if ($work->type_id == null && ($work->drug_generic_id != null || $work->drug_id != null) && $work->in_patient_id === null) {
						//outpatient medication
						if (!$work->billed) {
							//if not taken before
							?><?php
						}   //taken before?>
						Give <?= $work->dose ?> <?= ($work->drug_id == null) ? $work->drugGenericForm : $work->drugGenericForm ?>
						of <?= ($work->drug_id == null) ? $work->genericName : $work->drugName ?> <?= ($work->drug_id == null) ? $work->drugGenericWeight : $work->drugGenericWeight ?>
						every <?= convert_minutes_to_readable($work->frequency) ?>
						<?php
					} ?>
				</td>
				<td><?= ucwords(formatCount($work->round_count)) ?></td>
				<td>
					<span></span>
					<?php if ($work->last_round_time) { ?>
						<time data-time="false" datetime="<?= $work->last_round_time ?>"></time><?php } ?>
				</td>
				<td>
					<span></span>
					<time data-time="true" datetime="<?= $work->nextRoundTime ?>"></time>
				</td>
				<td><?= $work->task_count ?></td>
				<td><?php if ($work->type_id !== null ) {//vital
						//not medication ?>
						<a class="btn" href="javascript:void(0)" onClick="Boxy.load('/admissions/vitals/newVital.php?taskId=<?= $work->id ?>&pid=<?= $work->patient_id ?>&aid=<?= $work->in_patient_id != null ? $work->in_patient_id : '' ?>', {title:'New Reading', afterHide: function(){<?= (!isset($_GET['outpatient']) ? '/*loadTab(4);*/' : 'loadTab(1);') ?>}})"
						   title="New Reading">New-Reading</a>
						<?php
					} else if ($work->type_id == null && $work->drug_id == null && $work->drug_generic_id ==null) {//others
						//not medication ?>
						<a class="btn" href="javascript:void(0)" onClick="Boxy.load('/admissions/vitals/newVital.php?taskId=<?= $work->id ?>&pid=<?= $work->patient_id ?>&aid=<?= $work->in_patient_id != null ? $work->in_patient_id : '' ?>', {title:'New Reading', afterHide: function(){<?= (!isset($_GET['outpatient']) ? '/*loadTab(4);*/' : 'loadTab(1);') ?>}})"
						   title="New Reading">New-Reading</a>
						<?php
					} else if ($work->type_id == null && ($work->drug_id != null || $work->drug_generic_id !=null) && $work->in_patient_id !== null) {
						// inpatient medication?>
						<a title="New Reading" class="btn" href="javascript:" onclick="Boxy.load('/admissions/vitals/newVital.php?taskId=<?= $work->id ?>&pid=<?= $work->patient_id ?>&aid=<?= ($work->in_patient_id != null) ? $work->in_patient_id : '' ?>&did=<?= (($work->drug_id == null) ? $work->drug_generic_id : $work->drug_id) ?>&ctdid=<?= $work->id ?>', {title:'New Reading', afterHide: function(){<?= (!isset($_GET['outpatient']) ? '/*loadTab(4);*/' : 'loadTab(1);') ?>}})">
							Give-IP-Drug
						</a><?php
					} else if ($work->type_id == null && ($work->drug_id != null || $work->drug_generic_id !=null) && $work->in_patient_id === null) {
						//outpatient medication
						if (!$work->billed && AdmissionSetting::$ipMedicationTaskRealTimeDeduct) {//if not taken before ?>
						<a title="Cost Task" class="btn" href="javascript:" onclick="Boxy.load('/outpatient_tasks/cost_task.php?id=<?= $work->id ?>', {afterHide:function(){<?= (!isset($_GET['outpatient']) ? '/*loadTab(4);*/' : 'loadTab(1);') ?>}})">
								Do-Costing</a><?php
						} else {  //taken before?>
						<a title="New Reading" class="btn" href="javascript:" onclick="Boxy.load('/admissions/vitals/newVital.php?taskId=<?= $work->id ?>&pid=<?= $work->patient_id ?>&aid=<?= ($work->in_patient_id != null) ? $work->in_patient_id : '' ?>&did=<?= (($work->drug_id == null) ? $work->drug_generic_id : $work->drug_id) ?>&ctdid=<?= $work->id ?>', {title:'New Reading', afterHide: function(){<?= (!isset($_GET['outpatient']) ? 'loadTab(4);' : 'loadTab(1);') ?>}})">
								Give-OP-Drug</a><?php }
					} ?></td>
				<td class="hide"><?= ($work->nextRoundTime) ?></td>
			</tr>
			<?php //}
			//}
		} ?>
		</tbody>

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
</div>
<!--<script type="text/javascript" src="/js/highcharts.js"></script>-->
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				<?php if(explode('/', $uri)[1] == 'outpatient_tasks'){ ?>
				$.post('/outpatient_tasks/rounds.php', {'page': page, 'patient_id': $('[name="patient_id"]').val(), 'task_type': $('[name="task_type"]').val()}, function (s) {
					$('#contentPane_').html(s);
				});
				<?php } else { ?>
				$.post('/admissions/homeTabs/wardRounds.php', {
					'page': page,
					'ward_id': $('select[name="ward_id"]').val(),
					'patient_id': $('input[name="patient_id"]').val(),
					'task_type': $('[name="task_type"]').val()
				}, function (s) {
					$('#contentPane_').html(s);
				});
				<?php } ?>
			}
			e.clicked = true;
		}
	});

	var c;
	$(document).ready(function () {
		$('select[name="ward_id"]').select2({
			placeholder: "Filter list by Ward",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				$.post('/admissions/homeTabs/wardRounds.php', {
					'page': 0,
					'ward_id': $('select[name="ward_id"]').val(),
					'patient_id': $('input[name="patient_id"]').val(),
					'task_type': $('[name="task_type"]').val()
				}, function (s) {
					$('#contentPane_').html(s);
				});
				e.handled = true;
			}
		});

		$('[name="patient_id"]').css({'font-weight': 400}).select2({
			placeholder: "Filter list by patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (( + data.patientId + " " + data.fname + " " + data.mname ? data.mname :  + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				////return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
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
				$.post('/admissions/homeTabs/wardRounds.php<?php if(isset($_GET['outpatient']) && $_GET['outpatient'] == "true"){?>?outpatient=true<?php }?>', {
					'page': 0,
					'patient_id': $(this).val(),
					'task_type': $('[name="task_type"]').val()
				}, function (s) {
					$('#contentPane_').html(s);
				});
				e.handled = true;
			}
		});

		$('[name="task_type"]').select2({
			placeholder: "Filter list by type",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				$.post('/admissions/homeTabs/wardRounds.php<?php if(isset($_GET['outpatient']) && $_GET['outpatient'] == "true"){?>?outpatient=true<?php }?>', {
					'page': 0,
					'patient_id': $('[name="patient_id"]').val(),
					'task_type': $(this).val()
				}, function (s) {
					$('#contentPane_').html(s);
				});
				e.handled = true;
			}
		});
		updateTimer();
	});

	function updateTimer() {
		$('time').each(function () {
			if (moment($(this).attr('datetime')).isBefore()) {
				var extra = ($(this).attr("data-time") === "true") ? ' <i class="fa fa-warning" style="color:red"></i> ' : '';
				$(this).prev('span').html(extra);
			}
			$(this).html(moment($(this).attr('datetime')).fromNow());
		});
	}
</script> 
