<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/9/15
 * Time: 1:37 PM
 */

if (!isset($_SESSION)) {
	session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalEnrollment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/History.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistoryData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

include_once $_SERVER['DOCUMENT_ROOT'] . "/api/antenatal_vars.php";
$response = [];
$packages = (new AntenatalPackagesDAO())->getPackages();
$HISTORY = (new HistoryDAO())->all();
$service_centers = (new ServiceCenterDAO())->all('Antenatal');
if ($_POST) {
	$ae = new AntenatalEnrollment();

	if (!isset($_SESSION ['staffID'])) {
		exit('error:Sorry, restart the application. Your session has timed out');
	}
	if (is_blank($_POST['patient_id'])) {
		exit("error:Patient info not found");
	}

	if((new AntenatalEnrollmentDAO())->getActiveInstance($_POST['patient_id'])){
		exit('error:Sorry, patient is already enrolled');
	}

	if (is_blank($_POST['booking_indication'])) {
		exit("error:Please tell the indication for booking");
	}
	$p = new Manager();
	$hospid = $p->getStaffHospitalID($_SESSION['staffID']);

	$ae->setPatient(new PatientDemograph($_POST['patient_id']));
	$ae->setBookingIndication(escape($_POST['booking_indication']));

	if (is_blank($_POST['complication_note'])) {
		if ($_POST['booking_indication'] !== "routine") {
			exit("error:Please tell the complication for this enrollment");
		}
	} else {
		$ae->setComplicationNote(escape($_POST['complication_note']));
	}
	if (is_blank($_POST['lmp_date'])) {
		//exit("error:Please give us the L.M.P Date");
	}
	if (is_blank($_POST['baby_father_name'])) {
		//exit("error:Baby's Father Name required");
	}
	if (is_blank($_POST['baby_father_phone'])) {
		//exit("error:Baby's Father Contact Phone required");
	}

	$validate = validatePregnancies($_POST['gravida'], $_POST['para'], $_POST['alive'], $_POST['abortions']);
	if($validate !== true){
		exit($validate);
	}
	//if (is_blank($_POST['gravida'])) {
	//	//exit("error:Gravida is blank");
	//}
	//if (is_blank($_POST['para'])) {
	//	//exit("error:Para is blank");
	//}
	//if (!is_blank($_POST['para']) && !is_blank($_POST['gravida']) && (int)$_POST['para'] >= (int)$_POST['gravida']) {
	//	exit("error:Para cannot be greater than or equal to Gravida");
	//}
	//if (is_blank($_POST['alive'])) {
	//	//exit("error:Alive is blank");
	//}
	//if (!is_blank($_POST['alive']) && !is_blank($_POST['gravida']) && (int)$_POST['alive'] > (int)$_POST['gravida']) {
	//	exit("error:Alive cannot be greater than Gravida");
	//}
	//if (is_blank($_POST['abortions'])) {
	//	//exit("error:Miscarriages is blank");
	//}
	//if (!is_blank($_POST['abortions']) && !is_blank($_POST['gravida']) && (int)$_POST['abortions'] >= (int)$_POST['gravida']) {
	//	exit("error:Wrong Miscarriages <-> Gravida relationship");
	//}
	//
	//if((int)$_POST['para'] != (int)$_POST['alive']+(int)$_POST['abortions']){
	//	if((int)$_POST['gravida'] > (int)$_POST['para'] + (int)$_POST['alive']+(int)$_POST['abortions']){
	//
	//	} else {
	//		exit("error:Gravida, Para, Miscarriages mismatch");
	//	}
	//}

	if (is_blank($_POST['package_id'])) {
		exit("error:Select package");
	}
 if(is_blank($_POST['service_center_id'])) {
		exit("error:Select Service Center");
 }
	
	$ae->setEnrolledAt(new Clinic($hospid));
	$ae->setEnrolledBy(new StaffDirectory($_SESSION['staffID']));

	if (!is_blank($_POST['ob_gyn_id'])) {
		$ae->setObgyn(new StaffDirectory($_POST['ob_gyn_id']));
	}

	$ae->setLmpDate($_POST['lmp_date']);
	$ae->setLmpSource($_POST['lmp_source']);
	$ae->setEdDate($_POST['ed_date']);
	$ae->setBabyFatherName(escape($_POST['baby_father_name']));
	$ae->setBabyFatherPhone($_POST['baby_father_phone']);
	$ae->setBabyFatherBloodGroup($_POST['baby_father_blood_group']);
	$ae->setGravida($_POST['gravida']);
	$ae->setPara($_POST['para']);
	$ae->setAlive($_POST['alive']);
	$ae->setAbortions($_POST['abortions']);
	$ae->setServiceCenter((new ServiceCenterDAO())->get($_POST['service_center_id']));

	$ae->setPackage((new AntenatalPackages($_POST['package_id'])));

	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$enroll = (new AntenatalEnrollmentDAO())->create($ae, $pdo);

	if(!is_blank(@$_POST['history_data'])){
		foreach ($_POST['history_data'] as $historyId => $Value) {
			$pHistory = new PatientHistory();
			$pHistory->setPatient(new PatientDemograph($_POST['patient_id']));
			$pHistory->setCreator(new StaffDirectory($_SESSION['staffID']));
			$pHistDATA = [];
			$history = (new HistoryDAO())->get($historyId, $pdo);

			$pHistory->setHistory($history);

			foreach ($Value as $tplId => $comment) {
				$pHistDatum = new PatientHistoryData();
				$pHistDatum->setHistoryTemplateData(new HistoryTemplate($tplId));
				$pHistDatum->setValue($comment);

				$pHistDATA[] = $pHistDatum;
			}
			$pHistory->setData($pHistDATA);
			$resp = (new PatientHistoryDAO())->add($pHistory, null, 'antenatal', $pdo);
			if ($resp != null) {
				$response[] = $resp;
			}
		}

	}

	if ($enroll != null && count($response) === count($_POST['history_data'])) {
		$pdo->commit();
		exit("ok:" . $enroll->getPatient()->getId() . ":" . $enroll->getId());
	}
	$pdo->rollBack();
	exit("error:Something Went Wrong and we couldn't save the enrollment data.");
}
?>
<div style="width: 700px">
	<form method="post" id="enrollForm" name="enrollForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: pot1, onComplete: saved})">
		<fieldset>
			<legend>General</legend>
			<input type="hidden" name="patient_id" value="<?= $_REQUEST['pid'] ?>">
			<label>Indication for booking <select name="booking_indication">
					<option value="">- - - -</option>
					<option value="routine">Routine</option>
					<option value="complication">Complication</option>
				</select></label>
			<label id="complication_note" style="display: none">Please say complication
				<textarea class="wide" name="complication_note"></textarea></label>
			<label>Care Obstetrics/Gynaecologist <input name="ob_gyn_id" type="text"></label>
			<div class="row-fluid">
				<label class="span4">L.M.P. <input type="text" name="lmp_date" readonly="readonly"></label>
				<label class="span4">L.M.P Source <select name="lmp_source">
						<option value="">Unknown</option>
						<option value="patient">Patient</option>
						<option value="scan">Scan</option>
					</select></label>
				<label class="span4">E. D. D. <input name="ed_date" type="text" readonly="readonly"></label>
			</div>
			<div class="row-fluid pregnancy_details hide">
				<div class="gestation">Gestational age: <span></span></div>
				<div class="delivery">Number of days to delivery: <span></span></div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Baby's Father</legend>
			<label>Father's Name<input type="text" name="baby_father_name"></label>
			<label>Contact Phone<input type="text" name="baby_father_phone"></label>
			<label>Blood Group <span class="required-text">*</span>
				<select name="baby_father_blood_group">
					<?php $groups = PatientDemograph::$bloodGroups;
					for ($r = 0; $r < count($groups); $r++) {
						?>
						<option><?= $groups[$r] ?></option>
					<?php } ?></select></label>
		</fieldset>
		<fieldset>
			<legend>Obstetrics History</legend>
			<label>Previous Obstetrics History:</label>

			<select id="view" data-placeholder="-- Select a history category --">
				<option></option>
				<?php foreach ($HISTORY as $H) { ?>
					<option value="<?= $H->getId() ?>"><?= $H->getTemplate()->getLabel() ?></option><?php } ?>
			</select>
			<?php foreach ($HISTORY as $H) {//$tpl = new History();?>
				<dl class="history_data_item hide template<?= $H->getId() ?>"
				    style="overflow:auto;height:200px;float:none;padding-right:10px">
					<dt class=""><?= $H->getTemplate()->getLabel() ?></dt>
					<?php foreach ((new HistoryTemplateDataDAO())->byTemplate($H->getTemplate()->getId()) as $item) {//$item=new HistoryTemplateData();?>
						<dd><label><?= $item->getLabel() ?>
							<input name="history_data[<?= $H->getId() ?>][<?= $item->getId() ?>]" <?= $item->renderType() ?>></label>
						</dd><?php } ?>
				</dl>
			<?php } ?>
			<div class="clear"></div>
		</fieldset>
		<fieldset>
			<legend>Previous Pregnancies</legend>
			<label>Gravida <span class="help-block pull-right">Number of Pregnancies (including current)</span>
				<Select name="gravida" data-placeholder="--  Select gravida  --">
					<option></option>
					<?php foreach ($gravida as $k => $G) { ?>
						<option value="<?= $k ?>"><?= $G ?></option><?php } ?>
				</Select>
			</label>
			<label>Para <span class="help-block pull-right">Number of deliveries</span>
				<Select name="para" data-placeholder="--  Select para  --">
					<option></option>
					<?php foreach ($parity as $k => $P) { ?>
						<option value="<?= $k ?>"><?= $P ?></option><?php } ?>
				</Select>
			</label>

			<div class="row-fluid">
				<label class="span6">Alive <Select name="alive" data-placeholder="--  Select live births  --">
						<?php foreach ($general_ as $k => $A) { ?>
							<option value="<?= $k ?>"><?= $A ?></option><?php } ?>
					</Select>
				</label>
				<label class="span6">Miscarriages <Select name="abortions" data-placeholder="--  Select abortions  --">
						<?php foreach ($general_ as $k => $A) { ?>
							<option value="<?= $k ?>"><?= $A ?></option><?php } ?>
					</Select>
				</label>
			</div>
		</fieldset>

		<fieldset>
			<legend>Enrollment Package</legend>
			<label>Business Unit/service Center
				<select name="service_center_id">
					<?php foreach ($service_centers as $s_c) { ?>
						<option value="<?= $s_c->getId() ?>"><?= $s_c->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			
			<label>Antenatal Package
				<select name="package_id">
					<?php foreach ($packages as $pkg) { //$pkg=new AntenatalPackages();?>
						<option value="<?= $pkg->getId() ?>"><?= $pkg->getName() ?></option>
					<?php } ?>
				</select>
			</label>
		</fieldset>

		<button id="save_enrollment_btn" type="submit" class="btn">Save</button>

	</form>
</div>
<script>
	$(document).ready(function () {
		var $Form = $('#enrollForm');
		$Form.formToWizard({
			submitButton: 'save_enrollment_btn',
			showProgress: true, //default value for showProgress is also true
			nextBtnName: 'Next',
			prevBtnName: 'Previous',
			showStepNo: true
		});

		$('select[name="booking_indication"]').on('change', '', function (e) {
			if ($(this).val() == "complication") {
				$('#complication_note').slideDown();
			} else {
				$('#complication_note').slideUp();
				$('#complication_note .wide').val('');
			}
			e.preventDefault();
		});

		$('input[name="lmp_date"]').change(function (e) {
			var $newDate = moment($(this).val(), "YYYY-MM-DD").add(40, 'weeks').format('YYYY-MM-DD');
			$('input[name="ed_date"]').val($newDate);
			var now = moment(new Date());
			var edd = moment($newDate);
			$('.pregnancy_details .gestation span').html(now.diff(moment($(this).val(), "YYYY-MM-DD"), 'week') + ' week(s)');
			$('.pregnancy_details .delivery span').html(edd.diff(now, 'days') + ' day(s)');
			$('.pregnancy_details').show();
		}).datetimepicker({timepicker: false, format: 'Y-m-d'});
		$('input[name="ed_date"]').datetimepicker({timepicker: false, format: 'Y-m-d'});

		$(document).on('change', '#view', function (e) {
			var id = $(this).val();
			if (!e.handled) {
				$("dl.history_data_item").removeClass("hide").addClass("hide");
				$("dl.history_data_item.template" + id).removeClass("hide");
				//Boxy.get($(".close")).center();
				e.handled = true;
			}
		});

		$('input[name="ob_gyn_id"]').select2({
			placeholder: "Search and select Care Obstetrics/Gynaecologist",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/get_doctors.php",
				dataType: 'json',
				results: function (data, page) {
					return {results: data};
				},
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100
					}
				}
			},
			formatResult: function (data) {
				return ((data.firstName + " " + data.lastName));
			},
			formatSelection: function (data) {
				return ((data.firstName + " " + data.lastName));
			},
			id: function (data) {
				return data.id;
			}
		});
	});

	var pot1 = function () {
		jQuery('input[name*="history_data"]').filter(function () {
			return !this.value;
		}).attr('disabled', 'disabled');
	};

	function saved(s) {
		var returnData = s.split(":");
		//console.log(returnData);
		if (returnData[0] == "error") {
			jQuery('input[name*="history_data"]').filter(function () {
				return !this.value;
			}).removeAttr('disabled');
			Boxy.alert(returnData[1]);
		} else {
			location.href = "/antenatal/patient_antenatal_profile.php?id=" + returnData[1] + "&aid=" + returnData[2];
		}
	}
	function add_medical_history() {
		$('div.medical_history_data:last').after('<div class="medical_history_data row-fluid"><label class="span9"><input type="hidden" name="medical_history[]"></label><label class="span3"><input type="text" name="diagnosis_date[]" readonly="" placeholder="Date Diagnosed"></label></div>');
		$(".boxy-content div.medical_history_data:last input[name='medical_history[]']").select2({
			placeholder: "Please enter the diagnosis name or ICD-10/ICPC code",
			allowClear: true,
			minimumInputLength: 3,
			width: '100%',
			formatResult: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			}, formatSelection: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the diagnosis name or ICD 10 code";
			},
			ajax: {
				url: '/api/get_diagnoses.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term // search term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		});
		var now = new Date().toISOString().split('T')[0];
		$(".boxy-content div.medical_history_data:last input[name='diagnosis_date[]']").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now
				});
			}
		});
	}

	function remove_medical_history() {
		if ($('div.medical_history_data').length > 1) {
			$('div.medical_history_data:last').remove();
		}
	}
</script>

