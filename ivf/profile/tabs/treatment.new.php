<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/18/16
 * Time: 12:51 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFDrug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFDrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$cy_st_days = [1, 2, 3, 4, 5, '6 (E.C.S)', 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, ];
$cy_st_daysMax = 31;
if ($_POST) {
	@session_start();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/IVFEnrollment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/IVFTreatment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
	
	$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
	
	if (!isset($_SESSION['staffID'])) {
		exit('error:Please login again. Your session has expired');
	}
	if (is_blank($_POST['day_of_cycle'])) {
		exit('error:Day of Cycle is required');
	}
	if (is_blank($_POST['drug_id'])) {
		exit('error:Select the drug used');
	}
	if (is_blank($_POST['dose'])) {
		exit('error:Dose is required');
	}
	if (is_blank($_POST['duration'])) {
		exit('error:duration is required');
	}
	if (is_blank($_POST['remarks'])) {
		exit('error:Remarks is required');
	}
  $pds = array();
	
	// create regimen request object
	// get the patient enrolment instance in order to get the patient id
	$enrol_ins = (new IVFEnrollmentDAO())->get($_POST['data_id']);
	$pat = new PatientDemograph($enrol_ins->getPatient()->getId());
	$pres = new Prescription();
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);
	$pd = new PrescriptionData();
	$g = new DrugGeneric();
	$d = new Drug();
	$pres->setRequestedBy($staff);
	$pres->setPatient($pat);
	$pres->setHospital($staff->getClinic());
	$pres->setPrescribedBy($staff);
	$pres->setServiceCentre((new ServiceCenter($pharmacies[0]->getId())));
	
	$d = (new IVFDrugDAO())->get($_POST['drug_id']);
	$g->setId($d->getGeneric()->getId());
	$pd->setGeneric($g);
	//$pd->setDrug($d);
	$pd->setDose($_POST['dose']);
	$pd->setDuration($_POST['duration']);
	$pd->setComment($_POST['remarks']);
	$pd->setRequestedBy($staff);
	$pd->setHospital($staff->getClinic());
	$pds[] = $pd;
	
	$pres->setData($pds);
	
	$p = (new PrescriptionDAO())->addPrescription($pres);
	$treatment = (new IVFTreatment())->setEnrolment(new IVFEnrollment($_POST['data_id']))->setDayOfCycle($_POST['day_of_cycle'])->setDrug( new IVFDrug($_POST['drug_id']) )->setValue($_POST['dose'])->setDuration($_POST['duration'])->setComment($_POST['remarks'])->setUser(new StaffDirectory($_SESSION['staffID']))->add();
	
	if ($treatment != null && $p != null) {
		exit('success:Saved Note');
	}
	exit('error:Failed to save note');
}
?>
<section style="width: 600px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: __010, onStart: __009})">
		<div class="row-fluid">

			<label class="span8">Date
				<input type="datetime" value="<?= date(MainConfig::$mysqlDateTimeFormat) ?>" readonly="readonly" disabled></label>
			<label class="span4">
				Day of Cycle
				<select name="day_of_cycle"> <?php for ($day =1; $day <= $cy_st_daysMax; $day++) { ?>
						<option><?= $day ?></option><?php } ?> </select>

				<!--<input type="number" step="1" min="1" name="day_of_cycle"> -->
			</label>
		</div>
		<div class="row-fluid">
			<label class="span4">Drug <input type="text" name="drug_id"> </label>
			<label class="span4">Dose <input type="text" name="dose"> </label>
			<label class="span4">Duration<input type="text" name="duration" placeholder="duration in days only"></label>

		</div>
		<label class="hide">Scan Findings <textarea name="finding" class="hide"></textarea></label>
		<label>Remarks <textarea name="remarks"></textarea></label>
		<input type="hidden" name="data_id" value="<?= $_GET['id'] ?>">
		<div class="clear" style="margin-bottom: 10px"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	__009 = function () {
		$(document).trigger('ajaxSend');
	};
	__010 = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.alert(data[1]);
		} else if (data[0] === 'success') {
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(data[1]);
		}
	};
	
	var drugs = <?= json_encode( (new IVFDrugDAO())->all() , JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	
	$(document).ready(function () {
		$('[name="drug_id"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select drug",
			data: function () {
				return {results: drugs, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		});
	})

</script>
