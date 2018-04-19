<?php
$_GET['suppress'] = true;
require_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staff.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';

require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';

$pid = $_REQUEST['pid'];
$aid = $_REQUEST['aid'];
$tasks = (new ClinicalTaskDataDAO())->getPatientTaskData($pid, ['Discharged', 'Ended', 'Cancelled'], true, $aid);

//error_log(json_encode($cTask->getClinicalTaskData()[0]));
?>
<div class="menu-head">
	<a href="javascript:" id="link199">Active Tasks</a> |
	<strong>Previous Tasks</strong>
	|
	<a href="javascript:void(0)" onclick="taskCompleted('<?= $pid ?>','<?= $aid ?>')">Done Tasks</a>
</div>

<div>
	<table class="table table-hover table-striped">
		<thead>
		<tr>
			<th>Task/Instruction</th>
			<th>Date Created</th>
			<th>Created by</th>
			<th>Status</th>
			<th>Last Findings</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($tasks as $t) {//$t=new ClinicalTaskData();?>
			<tr>
				<td><?= describeTask($t) ?></td>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($t->getEntryTime())) ?></td>
				<td><?= $t->getCreatedBy()->getFullname() ?></td>
				<td><?= $t->getStatus() ?><?=strtolower($t->getStatus())=='cancelled'? ' by <span class="fadedText_a" title="REASON: '.htmlentities($t->getCancelReason()).'">'.$t->getCancelledBy()->getUsername() : '</span>' ?>
				</td>
				<td><?= ($t->getLastReading() == null ? "Medication: " . date("Y M, d h:i A", strtotime($t->getLastRoundTime())) : '<code>' . $t->getLastReading()->getValue() . '</code>' . " at " . date("Y M, d h:i A", strtotime($t->getLastRoundTime()))) ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		//do the moment time stuff? no.
		$("#link199").click(function () {
			<?=(!isset($_GET['outpatient']) ? 'showTabs(1);' : 'showTabs(14);')?>
		});
	});
</script>