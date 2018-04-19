<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 11:41 PM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskChart.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskChartDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';

//$types = getTypeOptions('type', 'clinical_task_data');
$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
$types = array_col($vitalTypes, 'name');
$types[] = "Medication";
$types[] = "Others";
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
//$task_type = !is_blank(@$_POST['task_type']) ? (new VitalDAO())->getByName($_POST['task_type'])->getId() : null;
$task_type = !is_blank(@$_POST['task_type']) ? $_POST['task_type'] : null;

$pid = $_REQUEST['pid'];
$aid = @$_REQUEST['aid'];
$ctc = new ClinicalTaskChart();
$ctc->setPatient(new PatientDemograph($_REQUEST['pid']));
$ctc->setInPatient((!isset($_REQUEST['aid'])) ? null : new InPatient($_REQUEST['aid']));

$tasks = (new ClinicalTaskChartDAO())->all($ctc, $page, $pageSize, $task_type);
$totalSearch = $tasks->total;
?>
<div class="menu-head">
	<div class="row-fluid">
		<div class="span8">
			<a href="javascript:" id="link199">Active Tasks</a> |
			<a href="javascript:void(0)" onclick="taskHistory('<?= $pid ?>','<?= $aid ?>')">Previous Tasks</a>
			|
			<strong>Done Tasks</strong>
		</div>
		<label style="" class="span4">
			<select name="task_type" data-placeholder="-- Filter by Type --">
				<option></option><?php foreach ($types as $type) { ?>
					<option value="<?= $type ?>"<?php if (@$_POST['task_type'] === $type) { ?> selected="selected"<?php } ?>><?= $type ?></option><?php } ?>
			</select>
		</label>
	</div>

</div>
<div id="completed_task_list">
	<table class="table table-hover table-striped">
		<thead>
		<tr>
			<th>Task/Instruction</th>
			<th>Value</th>
			<th>Comment</th>
			<th>Nursing Service</th>
			<th>Completed By</th>
			<th>Time</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($tasks->data as $t) { //$t=new ClinicalTaskChart(); ?>
			<tr>
				<td><?= describeTask($t->getClinicalTaskData()) ?></td>
				<td><?= $t->getValue() ?></td>
				<td><?= $t->getComment()?></td>
				<td><?= ($t->getNursingService() == null) ? '' : $t->getNursingService()->getName() ?></td>
				<td><?= $t->getCollectedBy()->getShortname() ?></td>
				<td><?= date("Y M, d h:i A", strtotime($t->getCollectedDate())) ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<div class="list10 dataTables_wrapper no-footer">
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
<script type="text/javascript">
	$(document).ready(function () {
		//do the moment time stuff? no.
		$("#link199").click(function () {
			<?=(!isset($_GET['outpatient']) ? 'showTabs(1);' : 'showTabs(14);')?>
		});

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
					$('#completed_task_list').html($(s).filter('#completed_task_list').html());
				});
				e.handled = true;
			}
		});
	}).on('click', '.list10.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('<?= $_SERVER['REQUEST_URI'] ?>', {
					'page': page,
					'task_type': $('select[name="task_type"]').val()
				}, function (s) {
					$('#completed_task_list').html($(s).filter('#completed_task_list').html());
				});
			}
			e.clicked = true;
		}
	});
</script>