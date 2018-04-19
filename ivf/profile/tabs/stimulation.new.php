<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/22/16
 * Time: 10:25 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/SimulationSizeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFSimulationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/SimulationSize.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFSimulation.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/SimulationData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
$cy_st_days = [1, 2, 3, 4, 5, '6 (E.C.S)', 7, 8, 9, 10, 11, 12, 13, 14, 15, 16];
$cy_st_daysMax = 31;// ? not 31?
$instance = (new IVFEnrollmentDAO())->get($_GET['id'], FALSE);
$sizes = (new SimulationSizeDAO())->all(TRUE);
$simObj = (new IVFSimulationDAO());
@session_start();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();

	if (is_blank($_POST['cy_st_day'])) {
		exit('error:CY/St Day is required');
	}

	if ($simObj->preventDayForEnrollment($_POST['cy_st_day'], $_POST['enrolment_id'], $pdo)) {
		exit('error:Data already exist for the selected CY/ST day');
	}

	foreach ($_POST['size'] as $value) {
		if (is_blank($value)) {
			exit('error:All Size values are required, enter 0 where nothing was found');
		}
	}
	//if (is_blank($_POST['endo'])) {
	//	exit('error:Endo is required');
	//}
	//if (is_blank($_POST['e2_level'])) {
	//	exit('error:E2/P4 Level is required');
	//}
	//if (is_blank($_POST['gnrha'])) {
	//	exit('error:GNRHa is required');
	//}
	//if (is_blank($_POST['ant'])) {
	//	exit('error:ANT is required');
	//}
	//if (is_blank($_POST['fsh'])) {
	//	exit('error:FSH is required');
	//}
	//if (is_blank($_POST['hmg'])) {
	//	exit('error:HMG is required');
	//}
	//if (is_blank($_POST['remarks'])) {
	//	exit('error:Remarks is required');
	//}

	$pdo->beginTransaction();
	$simul = (new IVFSimulation())->setEnrolment(new IVFEnrollment($_POST['enrolment_id']))->setRecordDate(date(MainConfig::$mysqlDateTimeFormat))->setRecordedBy(new StaffDirectory($_SESSION['staffID']))->setDay($_POST['cy_st_day'])->setEndo($_POST['endo'])->setE2Level($_POST['e2_level'])->setGnrha($_POST['gnrha'])->setAnt($_POST['ant'])->setFsh($_POST['fsh'])->setHmg($_POST['hmg'])->setRemarks($_POST['remarks'])->add($pdo);
	if ($simul == null) {
		$pdo->rollBack();
		exit('error:Action failed at first step');
	}
	foreach ($_POST['size'] as $size => $sideValue) {
		$rightSide = $sideValue['R'];
		$leftSide = $sideValue['L'];
		if (is_blank($rightSide) || is_blank($leftSide)) {
			$pdo->rollBack();
			exit('error:All Size values are required, enter 0 where nothing was found');
		}
		$simulData = (new SimulationData())->setLeftSide($leftSide)->setRightSide($rightSide)->setSimulation($simul)->setSize(new SimulationSize($size))->add($pdo);

		if ($simulData == null) {
			$pdo->rollBack();
			exit('error:Action failed at second step');
		}
	}


	$pdo->commit();
	exit('success:Simulation saved');
	//exit('error:Failed to save simulation');

}
?>
<section style="width: 900px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __wrap8e3dd93a60__, onComplete: __impl4cf1e782hg__})">
		<label>CY/ST Day <select name="cy_st_day"> <?php for ($day =1; $day <= $cy_st_daysMax; $day++){ ?>
					<option><?= $day ?></option><?php } ?> </select></label>
		<table class="table table-bordered zebra-striped">
			<thead>
			<tr>
				<th>Sizes</th><?php foreach ($sizes as $size) { ?>
					<th><?= strtoupper($size->getName()) ?></th><?php } ?></tr>
			</thead>
			<tr>
				<td>R</td><?php foreach ($sizes as $size) { ?>
					<td>
					<input required="required" name="size[<?= $size->getId() ?>][R]" title="" type="number" class="cell-number" value="0" min="0" step="1">
					</td><?php } ?></tr>
			<tr>
				<td>L</td><?php foreach ($sizes as $size) { ?>
					<td>
					<input required="required" name="size[<?= $size->getId() ?>][L]" title="" type="number" class="cell-number" value="0" min="0" step="1">
					</td><?php } ?></tr>
		</table>
		<div class="row-fluid">
			<label class="span6">Endo <span class="pull-right fadedText">(mm)</span> <input type="number" step="any" name="endo"></label>
			<label class="span6">E2/P4 Level <span class="pull-right fadedText">pg/ml</span><input type="number" step="any" name="e2_level"></label>
		</div>
		<div class="row-fluid">
			<label class="span6">GNRHa <span class="pull-right fadedText">(ml)</span>
				<input type="number" name="gnrha" step="any"></label>
			<label class="span6">ANT <span class="pull-right fadedText">(ml)</span>
				<input type="number" name="ant" step="any"></label>
		</div>
		<div class="row-fluid">
			<label class="span6">FSH <span class="pull-right fadedText">(IU)</span>
				<input type="number" name="fsh" step="any"></label>
			<label class="span6">HMG <span class="pull-right fadedText">(IU)</span>
				<input type="number" name="hmg" step="any"></label>
		</div>

		<label>Remarks <textarea class="wide" name="remarks"></textarea></label>
		<label><input type="checkbox" id="triggers_new_"> Set up new Triggers/Tasks  </label>
		<!--<div class="select2-container wide">
			<label class="select2-choice">
				Triggers/Tasks
				<span class="pull-right" style="margin-right: 3px;">
					<a href="#" id="add_new_trigger" class="action">Add New Instruction</a>
				</span>
				<span class="block" id="instructions">
					...
				</span>
			</label>
		</div>-->

		<input type="hidden" name="enrolment_id" value="<?= $_GET['id'] ?>">
		<div class="clear" style="margin-bottom: 10px;"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#triggers_new_').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
	});
	__impl4cf1e782hg__ = function (s) {
		var triggered = $('#triggers_new_').is(':checked');
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] == 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1], function(){
				if(triggered){
					Boxy.load('/admissions/dialogs/newClinicalTask.php?aid=<?= $_GET['id'] ?>&source=ivf&pid=<?= $instance->getPatient()->getId()?>', {title: 'New Trigger/Task', afterHide: function(){$('#tabbedPane').find('li.active a').click();}})
				}
			});
		}
	};

	__wrap8e3dd93a60__ = function () {
		$(document).trigger('ajaxSend');
	};


</script>
