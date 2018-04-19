<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AllergenCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$allergen_cats = (new AllergenCategoryDAO())->getAll();
$super_generic = (new SuperGenericDAO())->getAll();
$encounters = (new EncounterDAO())->forPatient(@$_GET['id']);

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamRoomDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubscribedDoctorDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAllergens.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AllergenCategory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	if (!isset($_SESSION)) {
		session_start();
	}
	$patObj = new PatientDemograph($_POST['pid']);
	$this_user = new StaffDirectory($_SESSION['staffID']);
	$VitalSignDAO = new VitalSignDAO();
	$encounter_ = new EncounterDAO();
	$VNDAO = new VisitNotesDAO();
	$pdo->beginTransaction();
	
	$encounter = isset($_POST['encounter_id']) ? (new EncounterDAO())->get(@$_POST['encounter_id'], false, $pdo) : null;
	
	if (isset($_POST['bp_value']) && trim($_POST['bp_value']) != '') { //preg_match('/(\d+)\/(\d+)/',$_POST['bp_value'])){
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Blood Pressure', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['bp_value'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Blood Pressure Reading");
		}
		unset($new);
	}

	if (isset($_POST['p_value']) && trim($_POST['p_value']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Pulse', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['p_value'])->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Pulse Reading");
		}
		unset($new);
	}

	if (isset($_POST['rp_value']) && trim($_POST['rp_value']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Respiration', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['rp_value'])->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Respiratory Rate Reading");
		}
		unset($new);
	}

	if (isset($_POST['temp_value']) && trim($_POST['temp_value']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Temperature', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['temp_value'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Temperature Reading");
		}
		unset($new);
	}
	if (isset($_POST['weight_value']) && trim($_POST['weight_value']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Weight', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['weight_value'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Weight Reading");
		}unset($new);
	}
	if (isset($_POST['height_value']) && trim($_POST['height_value']) != '') {
		$height = parseNumber($_POST['height_value']) / 100;
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Height', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($height)->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Height Reading");
		}
	}

	if (isset($_POST['weight_value']) && trim($_POST['weight_value']) != '' && isset($_POST['height_value']) && trim($_POST['height_value']) != '' && trim($_POST['weight_value']) != 0 && trim($_POST['height_value']) != 0) {
		$height = parseNumber($_POST['height_value']) / 100;
		$weight = parseNumber($_POST['weight_value']);
		$value = number_format(parseNumber($weight / ($height * $height)), 2);
		
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('BMI', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save BMI Reading");
		}
		unset($value);
		
		$value = number_format(parseNumber(($weight^0.425 * $height^0.725) * 0.007184), 2);
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('BSA', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save BSA Reading");
		}
	}
	if (isset($_POST['spO2_value']) && trim($_POST['spO2_value']) != '') {
		$value = parseNumber($_POST['spO2_value']);
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('SpO2', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save SpO2 Reading");
		}
		unset($value);
		unset($new);
	}
	
	$a_categories = array_filter($_POST['allergen_category']);
	$a_allergies = array_filter($_POST['allergen']);
	$a_reactions = array_filter($_POST['reaction']);
	$a_severities = array_filter($_POST['severity']);
	$a_super_generics = array_filter($_POST['super_generic']);

	
	//echo error_log($a_categories);
	if(sizeof($a_categories) > 0){
		error_log(json_encode($_POST));
		foreach ($a_categories as $i => $ac){
				$allergy = (new PatientAllergens())
					->setPatient($patObj)
					->setAllergen($a_allergies[$i])
					->setReaction($a_reactions[$i])
					->setSeverity($a_severities[$i])
					->setNotedBy(new StaffDirectory($_SESSION['staffID']))
					->setEncounter($encounter)
					->setCategory(new AllergenCategory($a_categories[$i]))
					//->setSuperGeneric( !is_blank($a_super_generics[$i]) ? new SuperGeneric($a_super_generics[$i]) : null)
					->add($pdo);
				if (!$allergy) {
					$pdo->rollBack();
					exit('error: Failed to save patient allergen data');
				}
		}


	}

		
	
	
	if (isset($_POST['encounter_id']) && !$encounter_->update($_POST['encounter_id'], $pdo)) {
		$pdo->rollBack();
		exit("error: Failed to update Encounter");
	}

	$pdo->commit();
	exit("success:Data saved");
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';

$all = (new PatientDiagnosisDAO())->one($_GET['id']);
$MainConfig = new MainConfig();
$lastDiagnosis = end($all->data);
if ($lastDiagnosis != null && $lastDiagnosis->_status != "confirmed") {
	$lastDiagnosis = prev($all->data);
}
$sourceId = (new BillSourceDAO())->findSourceById(3)->getId();
$_consultations = (new BillDAO())->getBillsBySourceForPatient($sourceId, $_GET['id']);
$_consultation = end($_consultations);

//todo indicate if it's follow up as well
$consultation = ($_consultation !== null && $_consultation !== false ? (new StaffSpecializationDAO())->getSpecializationByTitle(str_replace("FollowUp ", "", (str_replace("Consultancy charges: ", "", $_consultation->getDescription())))) : null);
?>
<section style="width: 700px">
	<form method="post" id="VisitNew" onsubmit="return AIM.submit(this, {'onStart' : startSaving, 'onComplete' : saveComplete})" action="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
		<div class="loader"></div>
		<fieldset>
			<legend>Why are you here?</legend>
			<label>Open Encounter
				<select name="encounter_id" data-placeholder="Select the encounter"><option></option><?php foreach ($encounters->data as $encounter) { ?>
						<option value="<?= $encounter->id ?>"><?= date(MainConfig::$dateTimeFormat, strtotime($encounter->start_date)) . ": " . ($encounter->specialization_ ? $encounter->specialization_->getName(): 'No Specialty') ?></option><?php } ?>
				</select>
			</label>
			<!--
			<label>Complaint: <textarea name="normal_note" style="width:100%; max-width:100%" placeholder="Patient Complaints..."></textarea></label>
			-->
		</fieldset>
		<fieldset>
			<legend>Vital Signs</legend>
			<input type="hidden" name="pid" value="<?= $_GET['id'] ?>">
			<div class="row-fluid">
				<label class="sub span6">Blood Pressure: <span class="pull-right fadedText">mmHg</span>
					<input type="text" name="bp_value" id="bp_value" pattern="<?= (new VitalDAO())->getByName('Blood Pressure')->getPattern() ?>" placeholder="Systolic/Diastolic (Example: 120/80)"/></label>
				<label class="sub span6">Temperature: <span class="pull-right fadedText">&deg;C</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('Temperature')->getPattern()?>" name="temp_value" id="temp_value" placeholder="Example: 34.6"/></label>
			</div>

			<div class="row-fluid">
				<label class="sub span6">Height: <span class="pull-right fadedText">cm</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('Height')->getPattern()?>" step="any"  name="height_value" id="height_value" placeholder="Example: 100.6"/></label>
				<label class="sub span6">Weight:<span class="pull-right fadedText">kg</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('Weight')->getPattern()?>" name="weight_value" id="weight_value" placeholder="Example: 64.2"/></label>
			</div>

			<div class="row-fluid">
				<label class="sub span6">Respiratory Rate: <span class="pull-right fadedText">(cycles per minute)</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('Respiration')->getPattern()?>" name="rp_value" id="rp_value" placeholder="Example: 70"/></label>
				<label class="sub span6">Pulse: <span class="pull-right fadedText">(beats per minute)</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('Pulse')->getPattern()?>" name="p_value" id="p_value" placeholder="Example: 56"/></label>

			</div>
			<div class="row-fluid">
				<label class="sub span6">Sp02: <span class="fadedText pull-right">(%)</span>
					<input type="text" pattern="<?= (new VitalDAO())->getByName('SpO2')->getPattern()?>" min="0" max="100" name="spO2_value" id="spO2_value" placeholder="Example: 96"/></label>
			</div>

		</fieldset>
		<fieldset>
        <div class="span4" id="allergies">
		<button type="button" onclick="cloneAllergen()" id="allergen_clone" class="btn btn-primary">ADD</button>
		<button type="button" onclick="removeAllergen(event)" id="allergen_remove" class="btn btn-primary">REMOVE</button>
		
			<legend>Allergies</legend>
            <div class="allergen_add">
			<label>Category <select data-placeholder="-- select allergen category --" name="allergen_category[]" id="allergen_category" class="wide">
					<option></option>
					<?php foreach ($allergen_cats as $cat) { ?>
						<option value="<?= $cat->getId() ?>"><?= $cat->getName() ?></option>
					<?php } ?>
				</select></label>
				<label id="drug_id">Drug <select data-placeholder="-- select the allergic drug --" name="super_generic[]" id="super_generic">
					<option></option>
					<?php foreach ($super_generic as $super_gen) { ?>
						<option value="<?= $super_gen->getId() ?>"><?= $super_gen->getName() ?></option>
					<?php } ?>
				</select></label>
			
			<label class="allergen">Allergen<input type="text" name="allergen[]" id="allergen"></label>
			<label>Reaction <input type="text" name="reaction[]" id="reaction"></label>
			<label>Severity <select name="severity[]" class="wide">
					<?php foreach ($MainConfig::allergenSeverities() as $val => $sev) { ?>
						<option value="<?= $val ?>"><?= $sev ?></option>
					<?php } ?>
				</select></label>

                </div>
				</div>
        <div class="span4" style="border:1px solid #fafafa;padding:6px;" id="existing_allergies">
		<p class="text-center"> Existing Allergies </p>
		<ul style="list-style-type: none;margin:0">
			<?php 
			$patient_allergiens = ( new PatientAllergensDAO() )->forPatient(@$_GET['id']);
			foreach($patient_allergiens as $allergen){?>
				<li class="row-fluid" style="display: inline-block;
				background: #FEFEFE;
				border: 2px solid #FAFAFA;
				box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
				padding: 2px;margin-bottom:4px;
				background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
				-webkit-transition: all .2s ease;
				-moz-transition: all .2s ease;
				-o-transition: all .2s ease;
				transition: all .2s ease;">
				<?php echo $allergen->getAllergen(); ?>  <span class="pull-right">    ( <?php echo $allergen->getReaction();?> )</span>
				</li>
			<?php };?>
			</ul>
		</div>



		</fieldset>

		<input id="SaveAll" type="submit" class="btn" value="Save"/>

		<div class="boxy-footer alert-box notice" style="margin-top: 10px;">
			<p>Last <em>Confirmed</em> Diagnosis:
				<?= ($lastDiagnosis != null) ? $lastDiagnosis->case : 'N/A' ?>
				<?php if ($lastDiagnosis != null){ ?>
				<span class="fadedText"> on <?= date("dS M, Y", strtotime($lastDiagnosis->date_of_entry)) ?></span>
			</p><?php } ?>
			<br>
			<?php if ($consultation) { ?>Last Seen by "<?= $consultation->getName() ?>" on <?= date("Y M, d h:iA", strtotime($_consultation->getTransactionDate())) ?><?php } else { ?>Not seen a consultant yet<?php } ?>
		</div>
	</form>
</section>
<script>
	function startSaving() {
		$('.loader').html('<img src="/img/loading.gif"/> Please wait...');
	}
	function saveComplete(s) {
		var dat = s.split(":");
		if (dat[0] === "error") {
			$('.loader').html('<span class="warning-bar">' + dat[1] + '</span>');
		} else if (dat[0] === "success") {
			Boxy.get($('.close')).hideAndUnload();
			showTabs(1);
		}
	}
	setTimeout(function () {
		var $Form = $('#VisitNew');
		$Form.formToWizard({
			submitButton: 'SaveAll',
			showProgress: true, //default value for showProgress is also true
			nextBtnName: 'Next',
			prevBtnName: 'Previous',
			showStepNo: true
		});

		$("#drug_id").hide();
		$("#allergen_category").on('change', function () {
			if ($(this).val() == '1') {
				$("#drug_id").show();
				$(".allergen").hide();
			} else {
				$("#drug_id").hide();
				$(".allergen").show();
			}
		});
	}, 0);

	function refreshSubscribersList(s) {
		if (s.checked) {
			$('.srm').find('option[data-subscribed="false"]').attr('disabled', 'disabled');
		} else {
			$('.srm').find('option[data-subscribed="false"]').prop('disabled', false);
		}
		$('.srm').select2("val", "");
	}

    function cloneAllergen(){

		$(".allergen_add:last").after(`
		<div class="allergen_add">
		<label>Category <select placeholder='-- select allergen category --' name='allergen_category[]' class='allergen_category' class='wide'>
		<option></option><?php foreach ($allergen_cats as $cat) { ?>
		<option value="<?php echo $cat->getId(); ?>"><?php echo $cat->getName(); ?>
		</option><?php } ?>
		</select>
		</label>
		<label id="drug_id">Drug <select data-placeholder="-- select the allergic drug --" name="super_generic[]" id="super_generic">
							<option></option>
							<?php foreach ($super_generic as $super_gen) { ?>
								<option value="<?php $super_gen->getId() ?>"><?= $super_gen->getName() ?></option>
							<?php } ?>
						</select></label>
				<label class="allergen">Allergen<input type="text" name="allergen[]" id="allergen"></label>
				<label>Reaction <input type="text" name="reaction[]" class="reaction"></label>
				<label>Severity <select name="severity[]" class="wide">
						<?php foreach ($MainConfig::allergenSeverities() as $val => $sev) { ?>
							<option value="<?= $val ?>"><?= $sev ?></option>
						<?php } ?>
					</select>
					
				</label>
		</div>
`);
        
}

function removeAllergen(e){

if( $(".allergen_add").length > 1 ){
    $(".allergen_add:last").remove();
}
    
}

</script>