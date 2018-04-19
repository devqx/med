<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/23/16
 * Time: 1:29 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$time = microtime(true);

require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';

$pageSize = 10;

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$task_type = !is_blank(@$_POST['task_type']) ? $_POST['task_type'] : null;
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$types = array_col($vitalTypes, 'name');
$types[] = "Medication";
$types[] = "Others";
$instance = (new IVFEnrollmentDAO())->get($_GET['aid']);
$aid = $_GET['aid'];
$pid = $instance->getPatient()->getId();
$DirectTasks = (new ClinicalTaskDataDAO())->getPatientIvfClinicalTaskDataSlim($pid, $_GET['aid'], ['Active'], FALSE, $page, $pageSize, $task_type);

$totalSearch = $DirectTasks->total;
$tasks = $DirectTasks->data;
?>
<div class="menu-head">
	<div class="row-fluid">
		<div class="span8">
			<?php if ($instance->getActive()) { ?>
				<a href="javascript:void(0)" onclick="Boxy.load('/admissions/dialogs/newClinicalTask.php?aid=<?= $_GET['aid'] ?>&source=ivf&pid=<?= $instance->getPatient()->getId()?>', {title: 'New Trigger/Task', afterHide: function(){reloadCurrentTab();}})">New
					Task</a><?php } ?>
		</div>
		<label style="" class="span4">
			<select name="task_type" data-placeholder="-- Filter by Type --">
				<option></option><?php foreach ($types as $type) { ?>
					<option value="<?= $type ?>"<?php if (@$_POST['task_type'] === $type) { ?> selected="selected"<?php } ?>><?= $type ?></option><?php } ?>
			</select>
		</label>
	</div>
</div>
<div id="tasks_list">
	<table class="table table-hover table-striped">
		<thead>
		<tr>
			<th>Task</th>
			<th>Set By</th>
			<th>Last Finding</th>
			<th>Last time attended</th>
			<?php if ($instance !== null || $instance->getActive()) { ?>
				<th>Next due time</th>
				<th>Fulfill Task</th>
				<th>Task Count</th>
				<th>Cancel</th><?php } ?>
		</tr>
		</thead>
		<tbody><?php
		if (count($tasks) == 0) { ?>
			<tr>
				<td colspan="8">
					<div class="warning-bar">No Clinical Tasks exist for this Patient.</div>
				</td>
			</tr>
		<?php } else {
			foreach ($tasks as $d) {
				if ($d->type_id == NULL && ($d->drug_id!=NULL || $d->drug_generic_id != null)) { //Medication Task
					$drug_form = $d->generic_form;// ($d->drug_id == null) ? $d->getGeneric()->getForm() : $d->getDrug()->getGeneric()->getForm();
					//$drug_name = $d->drug_name; // ($d->drug_id == null) ? $d->getGeneric()->getName() : $d->getDrug()->getName();
					$drug_name = ($d->drug_id == null) ? $d->generic_name : $d->drug_name;
					$drug_weight = $d->generic_weight;// ($d->drug_id == null) ? $d->getGeneric()->getWeight() : $d->getDrug()->getGeneric()->getWeight();
				}
				?>
				<tr>
				<td>
					<a href="javascript:void(0)" title="<?= $d->type_name ?>"> <?= ($d->type_id == NULL && ($d->drug_id!=NULL || $d->drug_generic_id != null) ? "Give " . $d->dose . " " . $drug_form . " of " . $drug_name . " " . $drug_weight . " every " . convert_minutes_to_readable($d->frequency) : ($d->type_id == NULL && ($d->drug_id==NULL && $d->drug_generic_id == null) ? $d->description . " every " . convert_minutes_to_readable($d->frequency) : "Check " . $d->type_name . " every " . convert_minutes_to_readable($d->frequency))) ?></a>
				</td>
				<td><?= $d->created_by_name ?></td>
				<td nowrap><?= ($d->last_reading == null ? "N/A" : $d->last_reading->getValue()) ?></td>
				<td nowrap data-date="true">
					<time datetime="<?= $d->last_round_time ?>"></time>
				</td>
				<?php if ($instance !== null || $instance->getActive()) { ?>
					<td nowrap data-date="true_">
						<time datetime="<?= $d->next_round_time ?>"></time>
					</td>
					<td nowrap>
						<?php if ($d->status == "Active") { ?><i>
							<a href="javascript:void(0)" <?php if (true /*time() > strtotime($d->next_round_time)*/) { ?> onClick="Boxy.load('/admissions/vitals/newVital.php?taskId=<?= $d->id ?>&pid=<?= $pid ?>&aid=<?= $aid ?><?= (($d->type_id == NULL && ($d->drug_id!=NULL || $d->drug_generic_id != null)) ? "&did=" . (($d->drug_id == null) ? $d->drug_generic_id : $d->drug_id) . "&ctdid=" . $d->id : "") ?>', {title:'New Reading', afterHide: function(){reloadCurrentTab()}})" <?php } ?> title="<?= $d->type_name ?>">New
								Reading</a></i><?php } ?></td>
					<td nowrap><?= $d->round_count ?>/<?= $d->task_count ?></td>
					<td nowrap><?php if ($d->status == "Active") { ?><i>
						<a href="javascript:void(0)" onClick="Boxy.confirm('Are you sure you want to cancel this task', function(val){cancelTask(<?= $d->id ?>)})" title="Cancel <?= $d->type_name ?>" class="text-danger">Cancel</a>
						</i>
					<?php } ?>
					</td><?php } ?>
				</tr><?php //} ?>
			<?php }
		} ?>
		</tbody>
	</table>

	<div class="triggersList dataTables_wrapper no-footer">
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
	<div class="pull-right fadedText">Page processed in <?= sprintf('%0.2f', microtime(true)- $time) ?>s</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('select[name="task_type"]').select2({width: '100%'});
	});
</script>
<script type="text/javascript">
	$(document).on('click', '.triggersList.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('<?= $_SERVER['REQUEST_URI'] ?>', {
					'page': page,
					'task_type': $('select[name="task_type"]').val()
				}, function (s) {
					$('#tasks_list').html($(s).filter('#tasks_list').html());
					updateTimes();
				});
			}
			e.clicked = true;
		}
	});
 var reloadCurrentTab=function () {
	 $('#tabbedPane').find('li.active a').click();
 };
	var taskCount = 0;
	$(document).ready(function () {
		updateTimes();
		setInterval(updateTimes, 60000);

		$('[name="task_type"]').select2({
			placeholder: "Filter list by type",
			width: '100%',
			allowClear: true
		}).change(function (e) {
			if (!e.handled) {
				$.post('<?= $_SERVER['REQUEST_URI'] ?>', {
					'page': 0,
					'task_type': $(this).val()
				}, function (s) {
					$('#tasks_list').html($(s).filter('#tasks_list').html());
				});
				e.handled = true;
			}
		});
	});

	function updateTimes() {
		$('time').each(function () {
			if ($(this).attr("datetime").trim() !== "") {
				var html = (moment($(this).attr("datetime")).isBefore() && $(this).parent().attr('data-date') == "true_") ? "<i class='fa fa-warning' style='color:red'></i> " : "";
				$(this).html(html + moment($(this).attr('datetime')).fromNow());
			}
		});
	}

	function cancelTask(tid) {
		vex.dialog.prompt({
			message: 'Reason for cancellation: ',
			placeholder: 'Enter here',
			value: null,
			overlayClosesOnClick: false,
			beforeClose: function (e) {
				e.preventDefault();
			},
			callback: function (value) {
				if (value !== false && value !== '') {
					//do the auth and proceed the action
					$.ajax({
						url: "<?= $_SERVER['PHP_SELF'] ?>",
						type: 'post',
						data: {ctdid: tid, cancelTask: true, reason: value},
						success: function (d) {
							showTabs(1);
							if (d.trim() === "ok") {
								showTabs(1);
							} else {
								Boxy.alert(d);
							}
						},
						error: function () {
							Boxy.alert("Oops! Something went wrong");
						}
					});
				} else {

				}
			}, afterOpen: function ($vexContent) {
				$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
				$submit = $($vexContent).find('[type="submit"]');
				$submit.attr('disabled', true);
				$vexContent.find('input').on('input', function () {
					if ($(this).val()) {
						$submit.removeAttr('disabled');
					} else {
						$submit.attr('disabled', true);
					}
				});
			}
		});
	}

	function taskHistory(pid, aid) {
		if (aid === "") {
			showTabs(14, 2);
		} else {
			showTabs(1, 2);
		}
	}

	function taskCompleted(pid, aid) {
		if (aid === "") {
			showTabs(14, 4);
		} else {
			showTabs(1, 4);
		}
	}
</script>