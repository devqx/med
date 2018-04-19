<?php
@session_start();
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NursingService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.admissions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ClinicalTaskData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ClinicalTaskChart.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskChartDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

$bills = new Bills();
$pid = $_REQUEST['pid'];
$pat = (new PatientDemographDAO())->getPatientMin($pid);

$patient = (new PatientDemographDAO())->getPatient($pid, false);
$_ = $bills->_getPatientPaymentsTotals($pid) + $bills->_getPatientCreditTotals($pid);
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pid)->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

if(!isset($_GET['taskId']) && !isset($_POST)){exit('error:Failed to determine task');}

$task = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_GET['taskId']);

$serviceCenters = (new ServiceCenterDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$task = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_POST['taskId'], ['Active'], true, $pdo);
	
	if (!is_blank($_POST['nursing_service_id'])) {
		if(is_blank($_POST['service_centre_id'])){
			$pdo->rollBack();
			exit('error:Service Center is Required when Nursing service is billed');
		}
		$instance = ($_POST['aid']) ? (new InPatientDAO())->getInPatient($_POST['aid'], true, $pdo) : null;
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
		$service = (new NursingServiceDAO())->get($_POST['nursing_service_id'], $pdo);
		$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($service->getCode(), $_POST['pid'], true, $pdo);
		$bil = new Bill();
		$bil->setPatient($patient);
		$bil->setDescription($service->getName() . " [Used in Tasks]");
		$bil->setItem($service);
		$bil->setSource((new BillSourceDAO())->findSourceById(16, $pdo));
		$bil->setTransactionType("credit");
		$bil->setAmount($_POST['nursing_service_quantity'] * $price);
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setCostCentre(!is_blank($_POST['service_centre_id']) ? (new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo)->getCostCentre() : null);
		//$bil->setCostCentre($instance && $instance->getWard() ? $instance->getWard()->getCostCentre() : null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($patient->getScheme());
		
		$bill = (new BillDAO())->addBill($bil, $_POST['nursing_service_quantity'], $pdo, (isset($_POST['aid']) && trim($_POST['aid']) !== "") ? ($_POST['aid']) : null);
	}
	
	if (isset($_POST['p_value'])) {
		$value = $_POST['p_value'];
		$comment = $_POST['comment'];
		if (is_blank($comment)) {
			$pdo->rollBack();
			exit('error:Enter the comment');
		}
		$type = $task->getType();
		if (trim($value) == '') {
			$pdo->rollBack();
			exit('error:What of value?');
		}
		
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
		$ip = (isset($_POST['aid']) && trim($_POST['aid']) !== "") ? new InPatient($_POST['aid']) : null;
		$new = (new VitalSign())->setType($type)->setPatient($pat)->setInPatient($ip)->setEncounter(null)->setHospital(new Clinic(1))->setReadBy($staff)->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($value)->add($pdo);
		
		$chart_data = (object)null;
		$chart_data->Staff = $staff;
		$chart_data->Comment = !is_blank($_POST['comment']) ? $_POST['comment'] : 'N/A';
		$chart_data->Value = $value;
		$chart_data->NursingService = (isset($_POST['nursing_service_id']) && trim($_POST['nursing_service_id']) !== "") ? new NursingService($_POST['nursing_service_id']) : null;
		
		if ((new ClinicalTaskDataDAO())->updateTask($type, $ip, null, $pdo, $_POST['taskId'], $chart_data)) {
			$pdo->commit();
			exit("ok:Vital reading saved successfully");
		}
		$pdo->rollBack();
		exit("error:Sorry something went wrong and we are unable to complete your request");
	} else if (isset($_POST['dose'])) {
		if (trim($_POST['dose']) === "") {
			$pdo->rollBack();
			exit("error:Please enter the dose");
		}
		if ($selfOwe - $creditLimit > 0 && !isset($_POST['aid'])) {
			$pdo->rollBack();
			exit("error:Patient has outstanding credit");
		}
		
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		
		$admission = new InPatient($_POST['aid']);
		$ctDatum = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_POST['ctdid'], ["Active"], true, $pdo);
		$pres = (new PrescriptionDAO())->getPrescriptionByAdmission($admission->getId(), false, $pdo);
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
		$isNew = false;
		
		$chart_data = (object)null;
		$chart_data->Staff = $staff;
		$chart_data->Comment = !is_blank($_POST['comment']) ? $_POST['comment'] : 'N/A';
		$chart_data->Value = null;
		$chart_data->NursingService = (isset($_POST['nursing_service_id']) && trim($_POST['nursing_service_id']) !== "") ? new NursingService($_POST['nursing_service_id']) : null;
		
		//$pdo = (new MyDBConnector())->getPDO();
		//$pdo->beginTransaction();
		
		$did = ($ctDatum->getDrug() == null) ? $ctDatum->getGeneric()->getId() : $ctDatum->getDrug()->getId();
		if (/*$bill !== NULL && */
		(new ClinicalTaskDataDAO())->updateTask("Medication", !is_blank($_POST['aid']) ? $admission->getId() : null, $did, $pdo, $_POST['taskId'], $chart_data)
		) {
			$pdo->commit();
			exit("ok:Action completed");
		} else {
			$pdo->rollBack();
			exit("error:Something went wrong ");
		}
	} else if (isset($_POST['others_Task_done'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		
		$admission = new InPatient($_POST['aid']);
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
		
		$chart_data = (object)null;
		$chart_data->Staff = $staff;
		$chart_data->Comment = ($_POST['others_Task_done'] == "off" ? '<span class="required">Not Done</span>: ' : "") . $_POST['comment'];
		$chart_data->Value = null;
		$chart_data->NursingService = (isset($_POST['nursing_service_id']) && trim($_POST['nursing_service_id']) !== "") ? new NursingService($_POST['nursing_service_id']) : null;
		
		//todo do we still mark this task as done when it's not checked?
		if ((new ClinicalTaskDataDAO())->updateTask("Others", $admission->getId(), null, $pdo, $_POST['taskId'], $chart_data)) {
			$pdo->commit();
			exit("ok:Action completed");
		}
		$pdo->rollBack();
		exit("error:Action failed");
	} else if (isset($_POST['w_value'], $_POST['h_value'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
		
		$type = $_POST['type'];
		if (isset($_POST['aid']) && trim($_POST['aid']) !== "") {
			$ip = new InPatient($_POST['aid']);
		} else {
			$ip = null;
		}
		$pid = $_POST['pid'];
		
		$weight = $_POST['w_value'];
		$height = $_POST['h_value'];
		//todo any individual validation?
		
		$value = $_POST['type']=='BMI' ? number_format(($weight / ($height * $height)), 1):
			//else it has to be BSA
			number_format(parseNumber(($weight ^ 0.425 * ($height/100) ^ 0.725) * 0.007184), 2);
		$type = (new VitalDAO())->getByName($_POST['type'], $pdo);
		$new1 = (new VitalSign())->setType((new VitalDAO())->getByName('Weight', $pdo))->setPatient(new PatientDemograph($_POST['pid']))->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_SESSION['staffID']) )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($weight)->add($pdo);
		
		$new2 = (new VitalSign())->setType((new VitalDAO())->getByName('Height', $pdo))->setPatient(new PatientDemograph($_POST['pid']))->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_SESSION['staffID']) )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($height)->add($pdo);
		
		$new = (new VitalSign())->setType($type)->setPatient(new PatientDemograph($_POST['pid']))->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_SESSION['staffID']) )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
		if($new == null || $new1 == null || $new2 == null){
			$pdo->rollBack();
			exit('error:Failed to save one or more vital sign component');
		}
		
		//$p = new Manager();
		//$VitalSignDAO = (new VitalSignDAO());
		//$height = parseNumber($_POST['h_value']) / 100;
		//$weight = parseNumber($_POST['w_value']);
		//$value = number_format(parseNumber($weight / ($height * $height)), 2);
		//$vs = (new VitalSign())->setValue($value)->setInPatient($ip)->setHospital(new Clinic(1))->setPatient(new PatientDemograph($_POST['pid']))->setReadBy($this_user)->setEncounter(null)->setType('BMI');
		//if (!$VitalSignDAO->addVitalSign($vs, $pdo)) {
		//	$pdo->rollBack();
		//	exit("error:Failed to save BMI Reading");
		//}
		
		//$bmi = $p->saveBMIVitalSign(strtolower($type), $pid, $_POST['w_value'], $_POST['h_value'], $_POST['aid']);
		//if (strstr($bmi, ':', true) == 'error') {
		//	$pdo->commit();
		//	exit($bmi);
		//}
		
		$chart_data = (object)null;
		$chart_data->Staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
		$chart_data->Comment = !is_blank($_POST['comment']) ? $_POST['comment'] : 'N/A';
		$chart_data->Value = $value;
		$chart_data->NursingService = (isset($_POST['nursing_service_id']) && trim($_POST['nursing_service_id']) !== "") ? new NursingService($_POST['nursing_service_id']) : null;
		
		if ((new ClinicalTaskDataDAO())->updateTask($type, $ip, null, $pdo, $_POST['taskId'], $chart_data)) {
			$pdo->commit();
			exit("ok:Vital reading saved successfully");
		}
		$pdo->rollBack();
		exit("error:Sorry something went wrong and we are unable to complete your request");
	}
	$pdo->rollBack();
	exit("error:Action failed");
}

?>
<div style="width: 500px">
	<?php
	if ($task->getType() && !in_array( $task->getType()->getName(), ['BMI', 'BSA'])) { ?>

		<form method="post" id="vitalForm" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, saveFormHandler)">
			<span id="message">&nbsp;</span>
			<label>Take <span style="text-decoration:underline"><?= $task->getType()->getName(); ?></span> reading for <?= $pat->getFullname() ?>
			</label>

			<div class="row-fluid">
				<label class="span9"><input type="text" name="p_value" id="p_value" required pattern="<?= $task->getType()->getPattern() ?>" placeholder="<?= $task->getType()->getName(); ?>"/></label>
				<label class="span3 border"><span class=""><?= $task->getType()->getUnit() ?></span></label>
			</div>

			<div class="row-fluid">
				<label class="span12">Service Center <select name="service_centre_id" data-placeholder="Service Center">
						<option></option>
						<?php foreach ($serviceCenters as $center){?>
						<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
					</select></label>
			</div>

			<div class="row-fluid">
				<label class="span8">
					Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
						<option value=""></option>
						<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
							<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span4">Quantity
					<input type="number" min="1" step="1" required value="1" name="nursing_service_quantity">
				</label>
			</div>
			<label>
				Comments <textarea name="comment" cols="5" rows="3"></textarea>
			</label>
			<div class="btn-block">
				<button type="submit" id="saveVital" class="btn">Save &raquo;</button>
				<button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>"/>
				<input type="hidden" name="taskId" value="<?= $_GET['taskId'] ?>"/>
				<input type="hidden" name="aid" value="<?= @$_GET['aid'] ?>"/>
			</div>
		</form>
	
	<?php } else if ($task->getType() && in_array($task->getType()->getName(), ["BMI", "BSA"])) { ?>

		<form method="post" id="bmiVitalForm" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, saveFormHandler)">
			<span id="message">&nbsp;</span>
			<p><?=$task->getType()->getName()?> Reading for <?= $pat->getFullname() ?></p>
			<label><span style="text-decoration:underline">Weight</span></label>

			<div class="row-fluid">
				<label class="span9"><input type="text" pattern="<?= (new VitalDAO())->getByName('Weight')->getPattern() ?>" name="w_value" id="p_value" placeholder="Example: 56.9"/></label>
				<label class="span3"><span class="fadedText">KiloGramme (kg)</span></label>
			</div>
			<label><span style="text-decoration:underline">Height</span> </label>

			<div class="row-fluid">
				<label class="span9"><input type="text" pattern="<?= (new VitalDAO())->getByName('Height')->getPattern() ?>" name="h_value" id="p_value" placeholder="Example: 2.3"/></label>
				<label class="span3 border"><span class="fadedText">Meter (m)</span></label>
			</div>
			<div class="row-fluid">
				<label class="span12">Service Center <select name="service_centre_id" data-placeholder="Service Center">
						<option></option>
						<?php foreach ($serviceCenters as $center){?>
							<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
					</select></label>
			</div>
			<div class="row-fluid">
				<label class="span8">
					Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
						<option value=""></option>
						<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
							<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span4">Quantity
					<input type="number" min="1" step="1" required value="1" name="nursing_service_quantity">
				</label>
			</div>
			<label>
				Comments <textarea name="comment" cols="5" rows="3"></textarea>
			</label>
			<div class="btn-block">
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>"/>
				<input type="hidden" name="taskId" value="<?= $_GET['taskId'] ?>"/>
				<input type="hidden" name="aid" value="<?= @$_GET['aid'] ?>"/>
				<input type="hidden" name="type" value="<?=$task->getType()->getName()?>">
				<button type="submit" class="btn">Save &raquo;</button>
				<button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
			</div>
		</form>
	
	<?php } else if ($task->getType() == null && ($task->getDrug() != null || $task->getGeneric() != null)) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
		$ctDatum = (new ClinicalTaskDataDAO())->getClinicalTaskDatum($_REQUEST['ctdid'], ["Active"], true);
		?>

		<h4>Medication Clinical Task</h4>
		<span id="message">&nbsp;</span>
		<form method="post" id="medicationForm" action="/admissions/vitals/newVital.php">
			<div class="well">
				Give
				<em><?= $ctDatum->getDose() ?> <?= ($ctDatum->getDrug() == null) ? $ctDatum->getGeneric()->getForm() : $ctDatum->getDrug()->getGeneric()->getForm() ?>
					of
					<strong><?= ($ctDatum->getDrug() == null) ? $ctDatum->getGeneric()->getName() : $ctDatum->getDrug()->getName() ?></strong>
					every <?= convert_minutes_to_readable($ctDatum->getFrequency()) ?></em>
			</div>
			<div class="row-fluid">
				<label class="span12">Service Center <select name="service_centre_id" data-placeholder="Service Center">
						<option></option>
						<?php foreach ($serviceCenters as $center){?>
							<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
					</select></label>
			</div>
			
			<div class="row-fluid">
				<label class="span8">
					Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
						<option value=""></option>
						<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
							<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span4">Quantity
					<input type="number" min="1" step="1" required value="1" name="nursing_service_quantity">
				</label>
			</div>

			<label>Last time administered:
				<code><?= $ctDatum->getLastRoundTime() === null ? "N/A" : date("d M, Y h:iA", strtotime($ctDatum->getLastRoundTime())) ?></code></label>
			<label>Next due administration time: <code>
					<time datetime="<?= $ctDatum->getNextRoundTime() ?>" class="nextRoundTime"></time>
				</code></label>
			<label<?= (!AdmissionSetting::$ipMedicationTaskRealTimeDeduct) ? ' class="hide"' : '' ?>>
				Dose:
				<input type="text" name="dose" id="dose" placeholder="Type a quantity" value="<?= $ctDatum->getDose() ?>"/>
			</label>
			<label>
				Comments <textarea name="comment" cols="5" rows="3"></textarea>
			</label>

			<div class="btn-block">
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>"/>
				<input type="hidden" name="aid" value="<?= @$_GET['aid'] ?>"/>
				<input type="hidden" name="taskId" value="<?= $_GET['taskId'] ?>"/>
				<input type="hidden" name="ctdid" value="<?= $ctDatum->getId() ?>"/>

				<button type="button" id="saveMedication" class="btn" title="Outstanding: <?= $selfOwe ?>" <?= ($selfOwe - $creditLimit > 0 && $ctDatum->getClinicalTask()->getInPatient()->getId() === null ? ' disabled_' : '') ?>>
					Save &raquo;
				</button>
				<button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
			</div>
		</form>
	
	<?php } else { ?>

		<form method="post" id="othersForm" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, saveFormHandler)">
			<span id="message">&nbsp;</span>

			<label>
				<input type="hidden" name="others_Task_done" value="off"> <!-- this one gets submitted if the checkbox is not ticked -->
				<input type="checkbox" checked="checked" name="others_Task_done"> Task completed
			</label>
			<div class="row-fluid">
				<label class="span12">Service Center <select name="service_centre_id" data-placeholder="Service Center">
						<option></option>
						<?php foreach ($serviceCenters as $center){?>
							<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
					</select></label>
			</div>
			<div class="row-fluid">
				<label class="span8">
					Nursing Service <select name="nursing_service_id" data-placeholder=" - - Nursing Service applicable - -">
						<option value=""></option>
						<?php foreach ((new NursingServiceDAO())->all() as $service) { ?>
							<option value="<?= $service->getId() ?>"><?= $service->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span4">Quantity
					<input type="number" min="1" step="1" required value="1" name="nursing_service_quantity">
				</label>
			</div>
			<label>
				Comments <textarea name="comment" cols="5" rows="3"></textarea>
			</label>
			<div class="btn-block">
				<button type="submit" id="saveVital" class="btn">Save &raquo;</button>
				<button type="button" onclick="Boxy.get(this).hideAndUnload();" class="btn-link">Cancel</button>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>"/>
				<input type="hidden" name="taskId" value="<?= $_GET['taskId'] ?>"/>
				<input type="hidden" name="aid" value="<?= @$_GET['aid'] ?>"/>
			</div>
		</form>
	
	<?php } ?>
</div>
<script type="text/javascript">
	var saveFormHandler = {
		onComplete: function (data) {
			$(document).trigger('ajaxStop');
			data = data.trim();
			if (data.split(":")[0] === "error") {
				$('span#message').html(data.split(":")[1]).attr('class', 'warning-bar');
			} else {
				Boxy.get($('.close')).hideAndUnload();
				Boxy.info(data.split(":")[1]);
			}
		}, onStart: function () {
			$(document).trigger('ajaxSend');
		}
	};
	$(document).ready(function () {
		var tag = $("time.nextRoundTime");
		tag.html(moment(tag.attr('datetime')).fromNow());
		$('input[name="others_Task_done"]:checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'});
		/*$("#saveVital").click(function () {
		 $.ajax({
		 url: "<= $_SERVER['PHP_SELF'] ?>",
		 type: "post",
		 data: $("#vitalForm").serialize(),
		 success:,
		 error: function (data) {
		 $('span#message').html("Oops! Something went wrong with the server").attr('class', 'warning-bar');
		 }
		 })
		 });*/

		$("#saveBMIVital").click(function () {
			$.ajax({
				url: "<?= $_SERVER['PHP_SELF'] ?>",
				type: "post",
				data: $("#bmiVitalForm").serialize(),
				success: function (data) {
					if (data.split(":")[0] === "error") {
						$('span#message').html(data.split(":")[1]).attr('class', 'warning-bar');
					} else {
						Boxy.info(data.split(":")[1]);
						Boxy.get($('.close')).hideAndUnload();
					}
				},
				error: function (data) {
					$('span#message').html("Oops! Something went wrong with the server").attr('class', 'warning-bar');
				}
			})
		});

		$("#saveMedication").click(function () {
			$.ajax({
				url: "<?= $_SERVER['PHP_SELF'] ?>",
				type: "post",
				data: $("#medicationForm").serialize(),
				success: function (data) {
					if (data.split(":")[0] === "error") {
						$('span#message').html(data.split(":")[1]).attr('class', 'warning-bar');
					} else {
						Boxy.info(data.split(":")[1]);
						Boxy.get($('.close')).hideAndUnload();
					}
				},
				error: function (data) {
					$('span#message').html("Oops! Something went wrong with the server").attr('class', 'warning-bar');
				}
			})
		});
	});
</script>