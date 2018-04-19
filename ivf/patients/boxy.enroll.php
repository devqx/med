<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 12:48 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryTemplateDataDAO.php';
//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PackageDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistoryData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PackageSubscription.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Package.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/HistoryTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFProtocolDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BloodTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
$bloodTypes = (new BloodTypeDAO())->all();
$packages = (new PackageDAO())->all('ivf');
$HISTORY = (new HistoryDAO())->all();
$protocols = (new IVFProtocolDAO())->all();

$PATIENT = (new PatientDemographDAO())->getPatient($_GET['pid'], FALSE);
$l_lab_result = (new PatientLabDAO())->getLastLabResut($_GET['pid']);
error_log("lab results::::".json_encode($l_lab_result));

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	if (is_blank($_POST['indication'])) {
		exit('error:Indication required');
	}
	$hormone = array('fsh' => $_POST['hormone']['fsh'], 'lh' => $_POST['hormone']['lh'], 'prol' => $_POST['hormone']['prol'], 'amh' => $_POST['hormone']['amh']);
	$husbandHormone = array('fsh' => $_POST['husband_hormone']['fsh'], 'lh' => $_POST['husband_hormone']['lh'], 'prol' => $_POST['husband_hormone']['prol'], 'testosterone' => $_POST['husband_hormone']['testosterone']);
	$sfa = array('count' => $_POST['sfa']['count'], 'motility' => $_POST['sfa']['motility'], 'morphology' => $_POST['sfa']['morphology']);
	$serology = array('hiv' => $_POST['serology']['hiv'], 'hep_b' => $_POST['serology']['hep_b'], 'hep_c' => $_POST['serology']['hep_c'], 'vdrl' => $_POST['serology']['vdrl'], 'chlamydia' => $_POST['serology']['chlamydia']);
	$husbandSerology = array('hiv' => $_POST['husband_serology']['hiv'], 'hep_b' => $_POST['husband_serology']['hep_b'], 'hep_c' => $_POST['husband_serology']['hep_c'], 'vdrl' => $_POST['husband_serology']['vdrl'], 'rbs' => $_POST['husband_serology']['rbs'], 'fbs' => $_POST['husband_serology']['fbs']);
	$response = [];
	if (!is_blank(@$_POST['history_data'])) {
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
			$resp = (new PatientHistoryDAO())->add($pHistory, null, 'ivf', $pdo);
			if ($resp != null) {
				$response[] = $resp;
			}
		}
	}
	$package = (new PackageDAO())->get($_POST['package_id'], true, $pdo);
	$patientFull = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo);
	$husband = (new PatientDemographDAO())->getPatient($_POST['husband_id'], FALSE, $pdo);
	$stimulation = $_POST['stimulation']; //this already returns as array
	$ivfEnrollment = (new IVFEnrollment())->setPatient($patientFull)->setHusband($husband)->setEnrolledBy(new StaffDirectory($_SESSION['staffID']))->setHormone($hormone)->setHusbandHormone($husbandHormone)->setSfa($sfa)->setSerology($serology)->setHusbandSerology($husbandSerology)->setAndrologyDetails($_POST['andrology_details'])->setStimulation($stimulation)->setPackage($package)->setIndication($_POST['indication'])->add($pdo);
	
	$sub = (new PackageSubscription())->setPackage( new Package($_POST['package_id']) )->setPatient( new PatientDemograph($_POST['patient_id']) )->setDateSubscribed(date(MainConfig::$mysqlDateTimeFormat))->setActive(TRUE)->add($pdo);
	
	if ($sub && $ivfEnrollment != null && count($response) === count(@$_POST['history_data'])) {
		$pdo->commit();
		exit("ok:" . $ivfEnrollment->getPatient()->getId() . ":" . $ivfEnrollment->getId());
	}
	$pdo->rollBack();
	exit('error:Enrollment failed');

}
?>
<section style="width: 750px">
	<form id="enrollForm" method="post" name="enrollForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: pot1, onComplete: saved})">
		<fieldset>
			<legend>Wife's Lab Details</legend>
			<div class="paper-card">
				<div class="heading">Hormonals</div>
				<div class="card-content">
					<div class="row-fluid">
						<label class="span3">FSH:<input type="text" name="hormone[fsh]"> </label>
						<label class="span3">LH: <input type="text" name="hormone[lh]"> </label>
						<label class="span3">PROL: <input type="text" name="hormone[prol]"> </label>
						<label class="span3">AMH: <input type="text" name="hormone[amh]"> </label>
					</div>
				</div>
			</div>
			
			<div class="paper-card">
				<div class="heading">Serology</div>
				<div class="card-content">
					<div class="row-fluid">
						<label class="span6">HIV <input type="text" name="serology[hiv]"> </label>
						<label class="span6">Hep B <input type="text" name="serology[hep_b]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span6">Hep C <input type="text" name="serology[hep_c]"> </label>
						<label class="span6">VDRL <input type="text" name="serology[vdrl]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span6">Chlamydia <input type="text" name="serology[chlamydia]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span6">Genotype
							<select name="serology[genotype]" data-placeholder="Select ..." disabled>
								<option></option>
								<?php
								require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BloodTypeDAO.php';
								$groups = (new BloodTypeDAO())->all();
								for ($r = 0; $r < count($groups); $r++) {?>
									<option<?= $fPat && $fPat->getGenotype() && $fPat->getGenotype() == $groups[$r]->getName() ? ' selected="selected"' : '' ?>><?= $groups[$r]->getName() ?></option>
								<?php } ?>
							</select>
						</label>
						<label class="span6">Blood Group
							<select name="serology[blood_group]" data-placeholder="Select ..." disabled>
								<option></option>
								<?php $bloodGroups = PatientDemograph::$bloodGroups;
								foreach ($bloodGroups as $bloodGroup) { ?>
									<option<?= ($bloodGroup == $PATIENT->getBloodGroup() ? ' selected="selected"' : '') ?>><?= $bloodGroup ?></option>
								<?php } ?>
							</select>
						</label>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Husband's Lab Details</legend>
			<label>Husband (Secondary Patient)<input type="hidden" name="husband_id"> </label>
			<div class="paper-card">
				<div class="heading">SFA/Andrology</div>
				<div class="card-content">
					<div class="row-fluid">
						<label class="span4">Count 10^6 ml<input type="text" name="sfa[count]"> </label>
						<label class="span4">Motility %<input type="text" name="sfa[motility]"> </label>
						<label class="span4">Morphology %<input type="text" name="sfa[morphology]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span12">Andrology Summary
							<textarea name="andrology_details"></textarea></label>
					</div>
				</div>
			</div>

			<div class="paper-card">
				<div class="heading">Serology</div>
				<div class="card-content">
					<div class="row-fluid">
						<label class="span6">HIV <input type="text" name="husband_serology[hiv]"> </label>
						<label class="span6">Hep B <input type="text" name="husband_serology[hep_b]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span6">Hep C <input type="text" name="husband_serology[hep_c]"> </label>
						<label class="span6">VDRL <input type="text" name="husband_serology[vdrl]"> </label>
					</div>
					<div class="row-fluid">
						<label class="span6">Genotype
							<select name="husband_serology[genotype]" data-placeholder="Select ..." disabled>
								<option></option>
								<?php
								require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/BloodTypeDAO.php';
								$groups = (new BloodTypeDAO())->all();
								for ($r = 0; $r < count($groups); $r++) {?>
									<option<?= $fPat && $fPat->getGenotype() && $fPat->getGenotype() == $groups[$r]->getName() ? ' selected="selected"' : '' ?>><?= $groups[$r]->getName() ?></option>
								<?php } ?>
							</select>
						</label>
						<label class="span6">Blood Group
							<select name="husband_serology[blood_group]" data-placeholder="Select ..." disabled>
								<option></option>
								<?php $bloodGroups = PatientDemograph::$bloodGroups;
								foreach ($bloodGroups as $bloodGroup) { ?>
									<option><?= $bloodGroup ?></option>
								<?php } ?>
							</select>
						</label>
					</div>
					<div class="row-fluid">
						<label class="span6">Random Blood Sugar <input type="text" name="husband_serology[rbs]"> </label>
						<label class="span6">Fasten Blood Sugar <input type="text" name="husband_serology[fbs]"> </label>
					</div>
				</div>
			</div>

			<div class="paper-card">
				<div class="heading">Hormonals</div>
				<div class="card-content">
					<div class="row-fluid">
						<label class="span3">FSH:<input type="text" name="husband_hormone[fsh]"> </label>
						<label class="span3">LH: <input type="text" name="husband_hormone[lh]"> </label>
						<label class="span3">PROL: <input type="text" name="husband_hormone[prol]"> </label>
						<label class="span3">Testosterone: <input type="text" name="husband_hormone[testosterone]"> </label>
					</div>
				</div>
			</div>
		</fieldset>
		<fieldset>
			<legend>Assessment/Package</legend>

			<label>
				Assessment
				<textarea name="indication" class="wide" rows="5"></textarea>
			</label>
			<label>Select Enrollment Package
				<select name="package_id" data-placeholder="-- Select Enrollment Package --" required>
					<option></option>
					<?php foreach ($packages as $package) { ?>
						<option value="<?= $package->getId() ?>"><?= $package->getName() ?></option>
					<?php } ?>
				</select>
			</label>
		</fieldset>
		<fieldset>
			<legend>Treatment Plan</legend>
			<div class="row-fluid">
				<label class="span6">Menstrual Cycle<input type="text"  name="stimulation[cycle]"></label>
				<!-- todo: how do i deal with firefox? for the month and date inputs?-->
				<label class="span6">LMP <input type="date" name="stimulation[lmp_date]"></label>
			</div>
			<div class="row-fluid">
				<label class="span12">Treatment Plan
					<select data-placeholder="Treatment plan" name="stimulation[method]" required>
						<option></option>
						<?php foreach ($protocols as $protocol) { ?>
							<option value="<?= $protocol->getId() ?>"><?= $protocol->getName() ?></option>
						<?php } ?>
					</select> </label>
			</div>

			<!--<div class="row-fluid">
				<label class="span6">Buserelin <span class="pull-right fadedText">ml</span> <input type="text" name="stimulation[buserelin]"> </label>
				<label class="span6">Goserelin <span class="pull-right fadedText">ml</span>  <input type="text" name="stimulation[goserelin]"> </label>

			</div>
			<div class="row-fluid">
				<label class="span6">FSH <span class="pull-right fadedText">/IU</span>
					<input type="text" name="stimulation[fsh]"> </label>
				<label class="span6">HMG <span class="pull-right fadedText">/IU</span>
					<input type="text" name="stimulation[hmg]"></label>
			</div>-->
		</fieldset>
		<input type="hidden" name="patient_id" value="<?= $_GET['pid'] ?>">
		<button id="save_enrollment_btn" type="submit" class="btn">Save</button>
	</form>

</section>
<script type="text/javascript">
	$(document).ready(function (e) {
		var $Form = $('#enrollForm');
		$Form.formToWizard({
			submitButton: 'save_enrollment_btn',
			showProgress: true, //default value for showProgress is also true
			nextBtnName: 'Next',
			prevBtnName: 'Previous',
			showStepNo: true
		});
	});

	$(document).on('change', '#view', function (e) {
		var id = $(this).val();
		if (!e.handled) {
			$("dl.history_data_item").removeClass("hide").addClass("hide");
			$("dl.history_data_item.template" + id).removeClass("hide");
			//Boxy.get($(".close")).center();
			e.handled = true;
		}
	});

	var pot1 = function () {
		jQuery('input[name*="history_data"]').filter(function () {
			return !this.value;
		}).attr('disabled', 'disabled');
	};

	function saved(s) {
		var returnData = s.split(":");
		if (returnData[0] === "error") {
			jQuery('input[name*="history_data"]').filter(function () {
				return !this.value;
			}).removeAttr('disabled');
			Boxy.alert(returnData[1]);
		} else if (returnData[0] === 'ok') {
			location.href = "/ivf/patients/patient_ivf_profile.php?id=" + returnData[1] + "&aid=" + returnData[2];
		} else {
			Boxy.alert('Unknown error');
		}
	}
	$(document).ready(function () {
		$('[name="indication"]').summernote(SUMMERNOTE_MINI_CONFIG);
		$('.boxy-content [name="husband_id"]').select2({
			placeholder: "Search and select patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, sex: 'male'
					};
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
			}
		}).change(function (e) {
			if (e.added) {
				var patient = e.added;
				$('[name="husband_serology[blood_group]"]').select2("val", patient.bloodgroup);
				$('[name="husband_serology[genotype]"]').select2("val", patient.bloodtype);
			} else {
				$('[name="husband_serology[blood_group]"]').select2("val", "");
				$('[name="husband_serology[genotype]"]').select2("val", "");
			}
		});
	});
</script>
