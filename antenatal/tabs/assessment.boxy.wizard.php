<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/8/15
 * Time: 1:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_lab_combos.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SystemsReviewCategoryDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientSystemsReview.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SystemsReview.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysicalExamination.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientPhysicalExam.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSystemsReviewDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SystemsReviewDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysicalExaminationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientPhysicalExamDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.pharmacy.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/FormularyDAO.php";

$formulary = (new FormularyDAO())->all();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role)) exit ($protect->ACCESS_DENIED);
$specimens = (new LabSpecimenDAO())->getSpecimens();
$scanTypes = (new ScanDAO())->getScans();
$fetalLies = getTypeOptions('fetal_lie', 'antenatal_assessment');
$HISTORY = (new HistoryDAO())->all();
$apt_clinics = (new AptClinicDAO())->all();
$allLabCentres = (new ServiceCenterDAO())->all('Lab');
$patId = (new AntenatalEnrollmentDAO())->get($_GET['instance'], TRUE)->getPatient()->getId();

$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_patient_allergens.php';

$drugs = [];
$pp = new Pharmacy();
if (!$pp::$canPrescribeBrand) {
	$drugs = [];
} else {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
}

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalAssessmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalNoteDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalAssessment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientHistoryData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/HistoryTemplate.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScan.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalNote.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FetalBrainRelationship.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FetalPresentation.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Appointment.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	try {
		$return = array();
		$error = array();
		$p = new Manager;
		$pdo = (new MyDBConnector())->getPDO();

		$pdo->beginTransaction();
		$instance = (new AntenatalEnrollmentDAO())->get($_POST['instance_id'], TRUE, $pdo);

		//$pid = (new AntenatalEnrollmentDAO())->get($_GET['instance'], TRUE, $pdo)->getPatient()->getId();
		$patObj = $instance->getPatient();
		
		if (isset($_POST['prescription'])) {
			$reg = json_decode($_POST['prescription']);
			
			if ($reg->pharmacy_id !== "" && sizeof($reg->regimens)) {
				//regimen was created but was blank ? break the operation
				if (sizeof($reg->regimens) === 0) {
					$pdo->rollBack();
					exit("error:Please add one or more regimen data");
				} else {
					if (!is_blank($_REQUEST['prescription']) && is_blank($reg->pharmacy_id)) {
						$pdo->rollBack();
						exit("error:Select a pharmacy for the prescription");
					} else {
						$pres = new Prescription();
						$pres->setPatient($patObj);
						$pres->setInPatient(null);
						
						$pres->setRequestedBy($this_user);
						$pres->setNote($reg->note);
						$pres->setHospital(new Clinic(1));
						$pds = array();
						
						foreach ($reg->regimens as $i => $pre) {
							
							if ($pre->drug === "" && $pre->generic === "") {
								$pdo->rollBack();
								echo "error:Please a drug name or a generic name";
								exit;
							}
							
							$pd = new PrescriptionData();
							$g = new DrugGeneric();
							$d = new Drug();
							if ($pre->drug !== "" && $pre->drug != "null" && isset($pre->drug->id) && isset($pre->drug->name)) {
								$d->setId($pre->drug->id);
								$d->setName($pre->drug->name);
								$d->setCode($pre->drug->code);
								$d->setStockQuantity($pre->drug->stockQuantity);
								$g->setId($pre->drug->generic->id);
								$d->setGeneric($g);
								//  Set other drug properties if there is a need for it (NOTE that the complete drug properties are here on the request object)
							} else {
								$d = null;
								if ($pre->generic !== "") {
									$g->setId($pre->generic->id);
									$g->setName($pre->generic->name);
									/*if ($pre->drug === "" || $pre->drug == "null" || isset($pre->drug->id) || isset($pre->drug->name)) {
										$d = null;
									} else {
										$d->setGeneric($g);
									}*/
								}
							}
							$pd->setDrug($d);
							$pd->setGeneric($g);
							$pd->setDose($pre->dose);
							$pd->setDuration($pre->duration);
							$pd->setComment($pre->comment);
							if (!is_blank($pre->body_part)) {
								$pd->setBodyPart((new BodyPartDAO())->get($pre->body_part, $pdo));
							}
							
							$pd->setFrequency($pre->freqno . ' x ' . $pre->freqtype->id);
							$pd->setRefillable($pre->refillable);
							$pd->setRefillNumber($pre->refillable ? parseNumber($pre->refill_count): null);
							$pd->setRequestedBy($this_user);
							$pd->setHospital(new Clinic(1));
							$pds[] = $pd;
						}
						$pres->setData($pds);
						$pres->setServiceCentre((new ServiceCenter($reg->pharmacy_id)));
						$p = (new PrescriptionDAO())->addPrescription($pres, $pdo);
						if ($p === null) {
							$pdo->rollBack();
							exit("error:Unable to save prescription");
						} else {
							$pq = new PatientQueue();
							$pq->setType("Pharmacy");
							$pq->setPatient($patObj);
							(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
						}
					}
				}
			}
		}

		if (isset($_POST['lab-reqs']) && !empty($_POST['lab-reqs'])) {
			$lr = new LabGroup();
			$lr->setPatient($patObj);
			$lr->setClinic($this_user->getClinic());
			$lr->setRequestedBy($this_user);

			$pref_specimens = array();
			$sel_specimens = isset($_POST['specimen_ids']) ? $_POST['specimen_ids'] : [];
			foreach ($sel_specimens as $s) {
				if (!empty($s)) $pref_specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
			}
			$lr->setPreferredSpecimens($pref_specimens);
			$lab_data = array();
			$tests = explode(",", $_POST['lab-reqs']);
			foreach ($tests as $l) {
				$lab_data[] = (new LabDAO())->getLab($l, FALSE, $pdo);
			}
			$lr->setRequestData($lab_data);
			$lr->setServiceCentre((new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo));

			$return['lab'] = (new PatientLabDAO())->newPatientLabRequest($lr, false, $pdo);
		}
		
		if (isset($_POST['scan_request_ids']) && !empty($_POST['scan_request_ids'])) {
			if (is_blank($_POST['request_note'])) {
				$pdo->rollBack();
				exit("error:Please enter a request note");
			}

			$newScan = [];
			foreach ($_POST['scan_request_ids'] as $s) {
				$scan = new PatientScan();
				$scan->setPatient($patObj);
				$scan_ids = [];
				$scan_ids[] = (new ScanDAO())->getScan($s, $pdo);
				$scan->setScan($scan_ids);
				$scan->setRequestDate(date("Y-m-d H:i:s"));
				$scan->setRequestedBy($this_user);
				$scan->setRequestNote($_POST['request_note']);
				$newScan[] = (new PatientScanDAO())->addScan($scan, false, $pdo)->getId();
			}
			$return['imaging'] = $newScan;
		}

		if (isset($_POST['comments']) && !is_blank($_POST['comments'])) {
			$note = new AntenatalNote();
			$note->setPatient($patObj);
			$note->setAntenatalInstance($instance);
			$note->setEnteredBy($this_user);
			$note->setNote($_POST['comments']);
			$note->setType('normal');

			$return['note'] = (new AntenatalNoteDAO())->add($note, $pdo);
		} else {
			$pdo->rollBack();
			exit("error:Assessment Note is required");
		}

		$VitalSignDAO = new VitalSignDAO();
		if (isset($_POST['fundus_height']) && trim($_POST['fundus_height']) != '') {
			$type = (new VitalDAO())->getByName("Fundus Height", $pdo);
			
			$new = (new VitalSign())->setType($type)->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
				->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( $this_user )
				->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['fundus_height'])->add($pdo);
			
			if (!$new) {
				$pdo->rollBack();
				exit("error:Failed to save fundus Height");
			}
			unset($new);
		}

		if (isset($_POST['fhr']) && trim($_POST['fhr']) != '') {
			$type = (new VitalDAO())->getByName("Fetal Heart Rate", $pdo);
			$new = (new VitalSign())->setType($type)->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
				->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( $this_user )
				->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['fhr'])->add($pdo);
			if (!$new) {
				$pdo->rollBack();
				exit("error:Failed to save fetal heart rate");
			}
			unset($new);
		}

		if(is_blank($_POST['apt_clinic_id'])){
			exit("error:Please select the appointment clinic");
		}

		if (!is_blank($_POST['nextAppointmentDate'])) {
			$clinic = (new AptClinicDAO())->get($_POST['apt_clinic_id'], $pdo);

			$ag = new AppointmentGroup();
			$ag->setCreator($this_user);
			// $ag->setDepartment($this_user->getDepartment());
			$ag->setClinic($clinic);
			$ag->setType("Antenatal");
			$ag->setIsAllDay(TRUE);
			$ag->setResource(null);
			$ag->setDescription("Antenatal Appointment");
			$ag->setPatient(new PatientDemograph($patObj->getId()));
			$apps = $appInvs = [];

			$app = new Appointment();
			$app->setEditor($this_user);
			$app->setStartTime($_POST['nextAppointmentDate']);
			$app->setEndTime(null);
			$apps[] = $app;

			$staffs = [];

			$ag->setAppointments($apps);
			$ag->setInvitees($appInvs);

			$appGroup = (new AppointmentGroupDAO())->add($ag, $pdo);
			if (!$appGroup) {
				$pdo->rollBack();
				exit("error:Antenatal Appointment exists for the Selected Date");
			}
		}

		$imaging = !is_blank(@$return['imaging']) ? implode(",", @$return['imaging']) : [];
		$assessment = (new AntenatalAssessment())->setUser($this_user)->setDate(date("Y-m-d H:i:s", time()))->setAntenatalInstance($instance)->setFundusHeight($_POST['fundus_height'])->setFhr($_POST['fhr'])->setFetalPresentation(!is_blank($_POST['fetal_presentation_id']) ? new FetalPresentation($_POST['fetal_presentation_id']) : null)->setFetalBrainRelationship(!is_blank($_POST['fetal_brain_relationship_id']) ? new FetalBrainRelationship($_POST['fetal_brain_relationship_id']) : null)->setFetalLie(!is_blank($_POST['fetal_lie']) ? $_POST['fetal_lie'] : null)->setComments($_POST['comments'])->setLab(!is_blank(@$return['lab']) ? $return['lab']->getGroupName() : null)->setScan($imaging)->setNextAppointmentDate($_POST['nextAppointmentDate']);

		$return['assessment'] = (new AntenatalAssessmentDAO())->add($assessment, $pdo);

		if (count($return) > 0 && !in_array(null, $return)) {
			$pdo->commit();
			exit("success:Assessment Saved");
		}
		$pdo->rollBack();
		exit("error:Failed to save Assessment");
	} catch (PDOException $e) {
		errorLog($e);
		exit("error:General Error");
	}
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FetalPresentationDAO.php';
$fetalPresentations = (new FetalPresentationDAO())->all();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FetalBrainRelationshipDAO.php';
$fetalBrainRelationships = (new FetalBrainRelationshipDAO())->all();
?>
<div style="max-width:800px;min-width:800px;width: 800px;">
	<span></span>
	
	<form method="post" name="assForm" id="assForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:starter, onComplete: completer})">
		<fieldset>
			<legend>General Assessment</legend>
			<div class="row-fluid">
				<label class="span6">Height of Fundus <span class="fadedText">(cm)</span> <input type="number" name="fundus_height" step="any"> </label>
				<label class="span6">Fetal Heart Rate  <input type="number" name="fhr" step="any" data-decimals="0"> </label>
			</div>
			
			<label>Presentation and Position of Foetus <select name="fetal_presentation_id" data-placeholder=" -- Select --">
					<option></option>
					<?php foreach ($fetalPresentations as $fp) { ?>
						<option value="<?= $fp->getId() ?>"><?= $fp->getName() ?></option>
					<?php } ?>
				</select></label>
			<div class="row-fluid">
				<label class="span6">Fetal Lie <select name="fetal_lie" data-placeholder="-- Select --">
						<option></option>
						<?php foreach ($fetalLies as $fetalLie){?>
							<option><?=$fetalLie?></option>
						<?php }?>
					</select></label>
				<label class="span6">Relationship to Brim <select name="fetal_brain_relationship_id" data-placeholder=" -- Select --">
						<option></option>
						<?php foreach ($fetalBrainRelationships as $fb) { ?>
							<option value="<?= $fb->getId() ?>"><?= $fb->getName() ?></option>
						<?php } ?>
					</select></label>
			</div>
			
			
			<div class="clear"></div>
		</fieldset>
		<!--  Comments-->
		<fieldset>
			<legend>General Comments</legend>
			<label><span class="required">*</span>General Comments
				<textarea name="comments" placeholder="General Comments"></textarea></label>
		</fieldset>
		<!--  labs -->
		<fieldset>
			<legend>Lab/Investigation</legend>
			<label>Laboratory <select name="service_centre_id" data-placeholder="Select a receiving lab center">
					<option></option>
					<?php foreach ($allLabCentres as $center) { ?>
						<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
					<?php } ?>
				</select> </label>
			<label>Lab Combos: <input type="hidden" id="lab-combos"></label>
			<label>Lab tests to request:</label>
			<label style="width: 100%;"><input style="width: 100%;" type="hidden" id="labs_to_request" name="lab-reqs"></label>
			<label>Preferred Specimen(s) </label>
			<label><select multiple="multiple" name="specimen_ids[]">
					<?php foreach ($specimens as $s) {
						echo '<option value="' . $s->getId() . '">' . $s->getName() . '</option>';
					} ?>
				</select></label>
		</fieldset>

		<!-- radiological requests-->
		<fieldset>
			<legend>Radiological Investigation</legend>
			<label>Scans to Request:</label>
			<label>
				<select id="scan_request_ids" multiple="multiple" name="scan_request_ids[]" placeholder="select a scan">
					<option data-price="0"></option>
					<?php
					foreach ($scanTypes as $scan_type) {//$scan_type = new Scan();
						echo '<option value="' . $scan_type->getId() . '" data-price="' . $scan_type->getBasePrice() . '">' . $scan_type->getName() . ' (' . $scan_type->getCategory()->getName() . ')</option>';
					}
					?>
				</select>
				<label>Request Note/Reason: <textarea name="request_note"></textarea></label>
			</label>
		</fieldset>

		<fieldset>
			<legend>Prescription</legend>
			<?php
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.pharmacy.php');
			require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');
			$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
			?>
			<div class="row-fluid">
				<label class="span6">Business Unit/Service Center <select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy --">
						<option value=""></option>
						<?php foreach ($pharmacies as $k => $pharm) { ?>
							<option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
						<?php } ?>
					</select></label>

				<label class="span6">Formulary <select id="formulary_id" data-placeholder="--select formulary--">
						<option></option>
						<?php foreach ($formulary as $form) { ?>
							<option value="<?= $form->getId() ?>"><?= $form->getName() ?></option>
						<?php } ?>
					</select> </label>
			</div>

			<div class="row-fluid">
				<label class="span6">Drug Generic Name<input type="hidden" name="filter-generics" id="generic"></label>

				<label class="span6">Drug Name <span id="drug-info" class="fadedText pull-right"></span>
					<input type="hidden" name="drug" id="drug" <?php $pp = new Pharmacy();
					if (!$pp::$canPrescribeBrand) { ?> disabled<?php } ?>>
				</label>
			</div>

			<div class="row-fluid">
				<label class="span2" title="Please Enter Numbers Only!">Frequency
					<input style="min-width: 10px" type="number" data-decimals="0" name="freqno" id="freqno" placeholder="eg. 3">
				</label>
				<label class="span4">
					Frequency Type
					<select name="freqtype" id="freqtype" data-placeholder="-- Select frequency type --">
						<option value=""></option>
						<?php $drugfrequencylist = MainConfig::$drugFrequencies;
						foreach ($drugfrequencylist as $f) { ?>
							<option value="<?= $f ?>"><?= ucfirst($f) ?></option><?php } ?>
					</select>
				</label>
				<label class="span3">Dose <input type="text" name="dose" id="dose" placeholder="Dose quantity"></label>
				<label class="span3">Duration
					<input type="number" name="duration" id="duration" data-decimals="0" value="" placeholder="(value in days) eg: 7">
				</label>
			</div>
				<label>
					Note
					<input type="text" name="comment" placeholder="Regimen Line Instruction">
				</label>
			<div class="row-fluid">
				<label class="span2"><input type="checkbox" name="refillable" id="refillable"> Refillable</label>
				<label class="span2 hide" id="more_refill"><input placeholder="Refills count" name="refill_count" id="refill_count" type="number" data-decimals="0"> </label>
			</div>

			<div class="">
				<button class="btn btn-mini" type="button" id="add-regimen"><i class="icon-plus-sign"></i></button>
				<button class="btn btn-mini" type="button" id="reset-regimen"><i class="icon-remove-sign"></i></button>
				<div id="added-regimen" style="display: inline-block; float: right"></div>
				<label data-name="Regimen" style="display:">Regimen Note
					<textarea name="regnote" id="regnote" cols="40" rows="2" style="width:100%"></textarea>
				</label>
			</div>

			<input id="prescription" name="prescription" type="hidden">
			<input id="prescription_plan" name="prescription_plan" type="hidden">

		</fieldset>
		<fieldset>
			<legend>Next Appointment</legend>
			<label><?php if (!isset($_GET['pid'])) { ?>Select patient<?php } ?>
				<input type="hidden" required="required" name="patient" value="<?= (isset($_GET['pid'])) ? $_GET['pid'] : '' ?>" id="patient" style="width: 100%" class="select2"></label>

			<label>Appointment in the next: <span class="pull-right">Eg: 4 weeks</span></label>
			<div class="row-fluid">
				<label class="span4"><input data-decimals="0" type="number" min="0" name="frequency" placeholder="example: 2" required="required"></label>
				<label class="span8"><select name="interval" required="required">
						<option value=""> --Select--</option>
						<option value="days">Day(s)</option>
						<option value="weeks">Week(s)</option>
						<option value="months">Month(s)</option>
					</select>
				</label>
			</div>
			<label>Clinic
				<input name="apt_clinic_id" type="hidden" id="apt_clinic_id" required placeholder="Select Appointment Clinic">
			</label>
			<div class="row-fluid">
				<label id="actualDateDsp" class="span6 border"></label>
				<label class="span6"><input title="Choose an absolute date" id="actualDate" name="nextAppointmentDate" type="text"></label>
			</div>
		</fieldset>

		<input id="SaveAll" type="submit" class="btn" value="Finish"/>
		<input type="hidden" name="instance_id" value="<?= $_GET['instance'] ?>">
		<input type="hidden" name="pid" value="<?= (int) $patId ?>">
	</form>
</div>
<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugs = <?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugData = drugs;
	var DrugGenericAllergens = <?= json_encode($drugAllergens, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;

	var _allergicGenerics = [];
	_.each(DrugGenericAllergens, function (obj) {
		_.each(obj.superGeneric.data, function(o){
			_allergicGenerics.push(o.id);
		});
	});
	prescription = {
		"pid": "",
		"pharmacy_id": "",
		"inpatient":<?= isset($_GET['aid']) ? $_GET['aid'] : 'false'?>,
		"note": "",
		"regimens": []
	};

	var inPatientContext = false;
	function preparePrescription() {
		var strPrescriptions = [];
		for (var i = 0; i < prescription.regimens.length; i++) {
			var drug = (prescription.regimens[i].drug === null || prescription.regimens[i].drug === "" || prescription.regimens[i].drug.id === undefined) ? null : prescription.regimens[i].drug;
			var gen = (prescription.regimens[i].generic === null || prescription.regimens[i].generic === "") ? null : prescription.regimens[i].generic;
			strPrescriptions.push(prescription.regimens[i].dose + ' ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqno + ' x ' + prescription.regimens[i].freqtype.id + ' for ' + prescription.regimens[i].duration + ' day(s)'+ ' '+prescription.regimens[i].comment);
		}
		//prescription.regimens[i] = reg;

		$('#prescription').val(JSON.stringify(prescription));
		$('#prescription_plan').val(implode(' || ', strPrescriptions));
		prescription.note = $("#regnote").val() || "";
	}

	$(document).ready(function () {
		now = new Date();
		$('#actualDate').datetimepicker({timepicker:false, minDate: now, formatDate: 'Y-m-d', format: 'Y-m-d'});
		$("#added-regimen button").live('click', function () {
			prescription.regimens.splice($(this).data("id"), 1);
			$(this).remove();
			if( $("#added-regimen button").length === 0){
				prescription.pharmacy_id = "";
				prescription.pid = "";
				prescription.note = "";
			}
			preparePrescription();
		});
		$("#reset-regimen").click(function () {
			resetRegimen();
		});
		$("#add-regimen").click(function () {
			reg = validateRegimen();

			if (reg !== null) {
				var i = prescription.regimens.length;
				prescription.regimens[i] = reg;
				var drug = (prescription.regimens[i].drug === null || prescription.regimens[i].drug === "" || prescription.regimens[i].drug.id === undefined) ? null : prescription.regimens[i].drug;
				var gen = (prescription.regimens[i].generic === null || prescription.regimens[i].generic === "") ? null : prescription.regimens[i].generic;
				$("#added-regimen").append('<button class="btn btn-mini" type="button" data-id="' + i + '"><i class="icon-remove-sign"></i> ' + prescription.regimens[i].dose + ' ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqno + ' x ' + prescription.regimens[i].freqtype.id + ' for ' + prescription.regimens[i].duration + ' day(s)</button>');
				resetRegimen();
				//$("label[data-name='Regimen']").show();
				save();
			}
		});

		$("#generic").select2({
			width: '100%',
			allowClear: true,
			placeholder: "select drug generic",
			data: {results: drugGens, text: 'name'},
			formatResult: function (source) {
				return source.name + " (" + source.form + ") " + source.weight; // This loads Drug generic name
			},
			formatSelection: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
			}
		}).on("change", function (e) {
			if (e.added) {
				if( _.includes(_allergicGenerics, e.added.id)){
					$.notify2("Patient is allergic to "+ e.added.name, "warn");
				}
				setDrugs(_.filter(drugs, function (obj) {
					return obj.generic.id === e.added.id;
				}));
			} else {
				setTimeout(function(){$('#formulary_id').trigger('change');}, 150);
			}
		});
		refreshDrug();

		$('#refillable').live('change',function(e){
			if(!e.handled) {
				if (this.checked) {
					$('#more_refill').removeClass('hide');
				} else {
					$('#more_refill').addClass('hide');
					$("#refill_count").val('');
				}
				e.handled = true;
			}
		});
	});

	function filterDrugs() {
		drugData = [];
		$("#drug").select2("val", "");
		$("#drug-info").html("");
		for (var i = 0; i < drugs.length; i++) {
			if ((drugs[i].generic.id === $("#generic").val()) || $("#generic").val() === "") {
				drugData[drugData.length] = drugs[i];
			}
		}
	}

	function save() {
		/*if ($("#regnote").val() === ""){
		 Boxy.alert("Please enter a note");
		 return;
		 } else */
		if (prescription.regimens.length === 0 && prescription.pharmacy_id !== "" && prescription.pid !== "") {
			Boxy.alert("Sorry you need to add one or more regimen data");
			return null;
		}
		prescription.note = $("#regnote").val() || "";

		if ($("#pid").select2("data") === null || $("#pid").select2("data").id === "") {
			Boxy.alert("Please select a patient");
			return null;
		} else {
			prescription.inpatient = false;
			prescription.pid = $("input[name='pid']").val();
		}
		if ($("#pharmacy_id").val() === "" && prescription.pid !== "" && prescription.regimens.length > 0) {
			Boxy.alert("Please select a fulfilling pharmacy");
			return null;
		} else {
			prescription.pharmacy_id = $("#pharmacy_id").val();
		}

		preparePrescription();
	}

	function validateRegimen() {
		regimen = {
			"drug": "",
			"dose": "",
			"freqno": "",
			"freqtype": "",
			"duration": "",
			"refillable": false,
			"refill_count": '',
			"generic": "",
			"comment": "",
			"body_part": ""
		};
		if ($("#drug").select2("data") === null && $("#generic").select2("data") === null) {
			Boxy.alert("Please select a drug name or drug generic name", function () {
				$("#drug").select2("open");
			});
			return null;
		} else {
			if ($("#drug").select2("data") !== null) {
				regimen.drug = $("#drug").select2("data");
			}
			if ($("#generic").select2("data") !== null) {
				regimen.generic = $("#generic").select2("data");
			}
		}

		if ($("#dose").val() === "0" || $("#dose").val() === "") {
			regimen.dose = '-';
			//Boxy.alert("Please enter the drug dosage", function () {
			//	$("#dose").focus();
			//});
			//return null;
		} else {
			regimen.dose = $("#dose").val();
		}

		if ($("#freqno").val() === "") {
			regimen.freqno = '-';
			//Boxy.alert("Please enter the frequency", function () {
			//	$("#freqno").focus();
			//});
			//return null;
		} else {
			regimen.freqno = $("#freqno").val();
		}
		regimen.refillable = $("#refillable").is(":checked");
		regimen.refill_count = $("#refill_count").val();
		regimen.comment = $('input[name="comment"]').val();

		if ($("#freqtype").select2("data") === null) {
			regimen.freqtype = {id: ' -- ', text: ' -- '};
			//Boxy.alert("Please select a frequency type", function () {
			//	$("#freqtype").select2("open");
			//});
			//return null;
		} else {
			regimen.freqtype = {id: $("#freqtype").select2("data").id, text: $("#freqtype").select2("data").text};
		}

		if ($("#duration").val() === "0" || $("#duration").val() === "") {
			regimen.duration = ' -- ';
			//Boxy.alert("Please enter drug duration", function () {
			//	$("#duration").focus();
			//});
			//return null;
		} else {
			regimen.duration = $("#duration").val();
		}
		regimen.body_part = $('select[name="bodypart"]').val() || null;
		if(regimen.refillable && regimen.refill_count <= 0){
			Boxy.alert("Invalid refill option");
			return null;
		}
		return regimen;
	}

	function resetRegimen() {
		$("#generic").select2("val", "").trigger("change");
		$("#drug").select2("val", "").trigger("change");
		refreshDrug();
		$("#dose").val("");
		$("#freqno").val("");
		$("#freqtype").select2("val", "");
		$("#refillable").prop("checked", false).trigger('change').iCheck('update');
		$("#more_refill").addClass('hide');
		$("#refill_count").val('');
		$("#duration").val("");
		$("#drug-info").html("");
		$("input[name='comment']").val("");
		$('select[name="bodypart"]').select2('val', '');
	}

	function refreshDrug() {
		$("#drug").select2({
			width: '100%',
			allowClear: true,
			placeholder: "---select drug---",
			data: function () {
				return {results: drugData, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
			},
			formatSelection: function (source) {
				return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
			}
		}).on("change", function (e) {
			var drug = $("#drug").select2("data");
			if (drug !== null) {
				$("#drug-info").html("<b>Stock level:</b> " + drug.stockQuantity + "; <b>Base Price: &#8358;</b>" + drug.basePrice);
				if (parseInt(drug.stockQuantity) < 1) {

					Boxy.ask(drug.name + " is unavailable in the store<br>Click <strong>Change</strong> to change the drug or <strong>Ignore</strong> to ignore this warning", ['Change', 'Ignore'], function (answer) {
						if (answer === "Change") {
							$("#drug").select2("val", "");
							$("#drug").select2("open");
							$("#drug-info").html("");
						}
					}, {title: "Low Stock Warning"});
				}
				
				if( _.includes(_allergicGenerics, drug.generic.id)){
					$.notify2("Patient is allergic to "+ drug.name, "warn");
				}
			} else {
				$("#drug-info").html("");
			}
		});
	}

	function setDrugs(param) {
		$("#drug").select2('val', '').select2({
			width: '100%',
			allowClear: true,
			placeholder: "---select drug---",
			data: function () {
				return {results: param, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + "(" + source.generic.weight + " " + source.generic.form + ")";
			},

			formatSelection: function (source) {
				return source.name + "(" + source.generic.weight + " " + source.generic.form + ")";
			}
		});
		$("#drug-info").html("");
	}
</script>
<script type="text/javascript">
	var clinics = JSON.parse('<?= json_encode($apt_clinics, JSON_PARTIAL_OUTPUT_ON_ERROR)?>');
	$(document).ready(function () {
		var labCs = <?=(json_encode($labCombos, JSON_PARTIAL_OUTPUT_ON_ERROR))?>;
		var $Form = $('#assForm');
		$Form.formToWizard({
			submitButton: 'SaveAll',
			showProgress: true, //default value for showProgress is also true
			nextBtnName: 'Next',
			prevBtnName: 'Previous',
			showStepNo: true,
			postStepFn: function () {
				try {
					preparePrescription();
				} catch (exception) {
					//console.info("prescription not defined");
				}
			}
		});

		$('#apt_clinic_id').select2({
			width: '100%',
			allowClear: true,
			data: function () {
				return {results: clinics, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		});

		$(document).on('change', 'input[name="frequency"], select[name="interval"]', function (e) {
			if ($('input[name="frequency"]').val() !== "" && $('select[name="interval"]').val() !== "") {
				$('#actualDateDsp').html('<span>' + moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('MMMM Do YYYY') + '<span>');
				$('#actualDate').val(moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('YYYY-MM-DD'));
			} else {
				$('#actualDate, #actualDateDsp').html('');
				$('#actualDate').val('');
			}
		});

		$(document).on('change', '#view3', function (e) {
			var id = $(this).val();
			if (!e.handled) {
				$("dl.systems_review").removeClass("hide").addClass("hide");
				$("dl.systems_review.category" + id).removeClass("hide");
				e.handled = true;
			}
		});

		$(document).on('change', '#view4', function (e) {
			var id = $(this).val();
			if (!e.handled) {
				$("dl.systems_review_").removeClass("hide").addClass("hide");
				$("dl.systems_review_.category" + id).removeClass("hide");
				e.handled = true;
			}
		});

		$('.boxy-content #lab-combos').select2({
			placeholder: "Search and select lab combos",
			width: '100%',
			allowClear: true,
			data: {results: labCs, text: 'name'},
			formatResult: function (data) {
				return data.name;
			},
			formatSelection: function (data) {
				return data.name;
			}
		}).change(function (e) {
			if (e.added !== undefined) {
				select = $('.boxy-content #labs_to_request');
				var dataOld = select.select2('data');
				for (var i = 0; i < e.added.combos.length; i++) {
					dataOld.push(e.added.combos[i].lab);
				}
				select.select2("data", dataOld, true);
			}
		});

		$('.boxy-content #labs_to_request').select2({
			placeholder: "Search and select lab",
			minimumInputLength: 3,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_labs.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			}

		}).change(function () {
		});
	});

	var starter = function () {
		$(document).trigger( 'ajaxSend' );
		jQuery('input[name*="history_data"]').filter(function () {
			return !this.value;
		}).attr('disabled', 'disabled');
		$('.boxy-content > div > span').html('Saving...');
	};

	var completer = function (data) {
		$(document).trigger( 'ajaxStop' );
		state = data.split(":");
		if (state[0] === "error") {
			jQuery('input[name*="history_data"]').filter(function () {
				return !this.value;
			}).removeAttr('disabled');
			Boxy.warn(state[1]);
		} else if (state[0] === "success") {
			showTabs(4);
			Boxy.info(state[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	};
</script>