<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/22/16
 * Time: 4:13 PM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Admission.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClinicalTaskData.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDAO.php';
$options = getTypeOptions('type', 'vital_sign');
$options[] = "Others";
?>
<section>
	Add new Trigger

	<div style="width: 750px">
		<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" id="clinicalTaskForm">
			<?php foreach ($options as $key => $option) { ?>
				<div class="menu-head">
					<label><input type="checkbox" name="type[]" data-index="<?= $key ?>" onclick="toggleChild(this)" value="<?= str_replace(" ", "_", $option) ?>"> <?= $option ?>
					</label>
					<?php if ($option === "Others") { ?>
						<div class="row-fluid" style="display: none" data-index="<?= $key ?>">
							<input type="text" placeholder="Task description" class="span12" name="others_task">
						</div>
					<?php } ?>
					<div class="row-fluid" style="display: none" data-index="<?= $key ?>">
						<div class="span3">
							<label>
								<span class="">Every</span>
								<input type="number" name="<?= str_replace(" ", "_", $option) ?>_freq" value="" placeholder="eg: 3" title="periodic interval">
							</label>
						</div>
						<div class="span3">
							<label>
								<span class="">Interval</span>
								<select name="<?= str_replace(" ", "_", $option) ?>_interval">
									<option value="1">mins</option>
									<option value="60">hours</option>
									<option value="<?= 60 * 24 ?>">days</option>
									<option value="<?= intval(60 * 24 * 7) ?>">weeks</option>
									<option value="<?= intval(60 * 24 * 7 * 4.3453) ?>">months</option><!-- // should we use 30 or 31  -->
								</select>
							</label>
						</div>
						<div class="span3">
							<label>
								<span class="">Task Count</span>
								<input type="number" name="<?= str_replace(" ", "_", $option) ?>_taskcount" value="" placeholder="eg: 4" title="number of times to run task">
							</label>
						</div>
						<div class="span3">
							<label>
								<span class="">Start Time</span>
								<input type="text" class="start_time" name="<?= str_replace(" ", "_", $option) ?>_start_time" value="" placeholder="Time to start task" title="">
							</label>
						</div>
					</div>
				</div>
			<?php } ?>
			<label class="menu-head"><input type="checkbox" name="medication" id="medication" value="Medication"> Add Medication</label>

			<input type="hidden" name="aid" value="<?= $_REQUEST['aid'] ?>">

			<button type="button" id="saveCT" class="btn btn-primary">Save</button>
			<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</form>
	</div>
</section>
