<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
$error = [];
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
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	
	$pdo = (new MyDBConnector())->getPDO();
	
	if (!isset($_SESSION)) {
		session_start();
	}
	$patObj = new PatientDemograph($_POST['pid']);
	$this_user = new StaffDirectory($_SESSION['staffID']);
	$VitalSignDAO = new VitalSignDAO();
	$VNDAO = new VisitNotesDAO();
	
	$pdo->beginTransaction();
	
	if (isset($_POST['bp']) && trim($_POST['bp']) != '') { //preg_match('/(\d+)\/(\d+)/',$_POST['bp_value'])){
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Blood Pressure', $pdo))->setPatient($patObj)->setInPatient(isset($_POST['aid']) ? new InPatient($_POST['aid']) : null)->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy($this_user)->setReadDate(date(MainConfig::$mysqlDateTimeFormat))->setValue($_POST['bp'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Blood Pressure Reading");
		}
		unset($new);
	}
	
	if (isset($_POST['height']) && trim($_POST['height']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Height', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['height'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Height Reading");
		}
		unset($new);
	}
	if (isset($_POST['weight']) && trim($_POST['weight']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Weight', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['weight'])->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Weight Reading");
		} unset($new);
	}
	
	if (isset($_POST['fundus_height']) && trim($_POST['fundus_height']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Fundus Height', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['fundus_height'])->add($pdo);
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Fundus Height Reading");
		}unset($new);
	}
	
	if (isset($_POST['fhr']) && trim($_POST['fhr']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Fetal Heart Rate', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['fhr'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Fetal Heart Rate Reading");
		}
		unset($new);
	}
	
	if (isset($_POST['protein']) && trim($_POST['protein']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Protein', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['protein'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Protein Reading");
		} unset($new);
	}
	
	if (isset($_POST['glucose']) && trim($_POST['glucose']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('Glucose', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['glucose'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save Glucose Reading");
		} unset($new);
	}
	
	if (isset($_POST['pcv']) && trim($_POST['pcv']) != '') {
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('PCV', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['pcv'])->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save PCV Reading");
		} unset($new);
	}
	
	if (isset($_POST['weight']) && trim($_POST['weight']) != '' && isset($_POST['height']) && trim($_POST['height']) != '' && trim($_POST['weight_value']) != 0 && trim($_POST['height_value']) != 0) {
		$height = number_format($_POST['height'] / 100, 2);
		$value = number_format(($_POST['weight'] / ($height * $height)), 1);
		
		$new = (new VitalSign())->setType((new VitalDAO())->getByName('BMI', $pdo))->setPatient($patObj)->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter($encounter)->setHospital(new Clinic(1))->setReadBy( $this_user )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
		
		if ($new == null) {
			$pdo->rollBack();
			exit("error:Failed to save BMI Reading");
		} unset($new);
	}
	
	if (isset($_POST['normal_note']) && trim($_POST['normal_note']) != '') {
		//save normal note
		if (!isset($_SESSION)) {
			session_start();
		}
		
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/AntenatalNote.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalNoteDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
		
		$instance = (new AntenatalEnrollmentDAO())->get($_POST['aid'], false, $pdo);
		//error_log(json_encode($instance));
		$note = new AntenatalNote();
		$note->setPatient($instance->getPatient());
		$note->setAntenatalInstance($instance);
		$note->setEnteredBy(new StaffDirectory($_SESSION['staffID']));
		if (is_blank($_POST['normal_note'])) {
			$pdo->rollBack();
			exit("error:Note is blank");
		}
		$note->setNote($_POST['normal_note']);
		$note->setType('Normal');
		$ret = (new AntenatalNoteDAO())->add($note, $pdo);
		sleep(0.01);
		if ($ret == null) {
			$pdo->rollBack();
			exit("error:Could not save complaint");
		}
	}
	if (isset($_POST['room_id']) && !empty($_POST['room_id']) && isset($_POST['did']) && !empty($_POST['did'])) {
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
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
		
		$get_active_enrollment = (new AntenatalEnrollmentDAO())->getActiveInstance($_POST['pid'], false, $pdo);
		
		//        $room = (new ExamRoomDAO())->getExamRoom($_POST['room_id']);
		//        $subscribedPerson = (new SubscribedDoctorDAO())->getSubscriptionsByRoom($room)[0];
		$specialty = (new StaffSpecializationDAO())->get($_POST['room_id'], $pdo);
		
		if (isset($_POST['followUp'])) {
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['pid'], true, $pdo);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['pid'], true, $pdo);
		}
		
		$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], false, $pdo, null);
		
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo);
		$bil = new Bill();
		$bil->setPatient($pat);
		$bil->setDescription((isset($_POST['followUp']) ? "FollowUp " : "") . "Consultancy charges: " . $specialty->getName());
		$bil->setItem($specialty);
		$bil->setSource((new BillSourceDAO())->findSourceById(3, $pdo));
		$bil->setTransactionType("credit");
		$bil->setAmount($price);
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($pat->getScheme());
		$bil->setCostCentre((new DepartmentDAO())->get($_POST['did'], $pdo)->getCostCentre());
		$b = (new BillDAO())->addBill($bil, 1, $pdo);
		//TODO: send this doctor a notification ...?
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
		$pat = new PatientDemograph($_POST['pid']);
		$pq = new PatientQueue();
		$pq->setType("Antenatal");
		$pq->setSpecialization($specialty);
		$pq->setPatient($pat);
		$pq->setDepartment(new Department($_POST['did']));
		$pq->setFollowUp((isset($_POST['followUp'])) ? 1 : 0);
		//push to doctors' queue
		(new PatientQueueDAO())->addPatientQueue($pq, $pdo);
	}
	$pdo->commit();
	exit("success:Data saved");
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';

$all = (new PatientDiagnosisDAO())->one($_GET['id']);

//TODO: what if there are many that were diagnosed that day
$lastDiagnosis = end($all->data);
if ($lastDiagnosis != null && $lastDiagnosis->_status != "confirmed") {
	$lastDiagnosis = prev($all->data);
}
$sourceId = (new BillSourceDAO())->findSourceById(3)->getId();
$_consultations = (new BillDAO())->getBillsBySourceForPatient($sourceId, $_GET['id']);
$_consultation = end($_consultations);

$consultation = ($_consultation !== null && $_consultation !== false ? (new StaffSpecializationDAO())->getSpecializationByTitle(str_replace("Consultancy charges: ", "", $_consultation->getDescription())) : null);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
$antenatalInstance = (new AntenatalEnrollmentDAO())->getActiveInstance($_GET['id'], true);
//file_put_contents("/tmp/pl.txt", str_replace("Consultancy charges: ","", $_consultation->getDescription()));
?>

<script src="/js/jquery.formToWizard.js"></script>
<script>
	//console.log(<?//= json_encode($all) ?>);
	function startSaving() {
		$('.loader').html('<img src="/img/loading.gif"/> Please wait...');
	}
	function saveComplete(s) {
		var dat = s.split(":");
		console.log(dat);
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
	}, 0);

	function refreshSubscribersList(s) {
		if (s.checked) {
			$('.srm').find('option[data-subscribed="false"]').attr('disabled', 'disabled');
		}
		else {
			$('.srm').find('option[data-subscribed="false"]').prop('disabled', false);
		}
		$('.srm').select2("val", "");
	}

</script>
<section style="width: 700px">
	<form method="post" id="VisitNew"
	      onsubmit="return AIM.submit(this, {'onStart' : startSaving, 'onComplete' : saveComplete})"
	      action="<?php echo $_SERVER['SCRIPT_NAME'] ?>">
		<div class="loader"></div>
		<fieldset>
			<legend>Any complaints?</legend>
			<label><textarea name="normal_note" class="wide" style="width:100%; max-width:100%"></textarea></label>
			<input name="aid" type="hidden" value="<?= $_REQUEST['aid'] ?>">
		</fieldset>
		<fieldset>
			<legend>Antenatal Vital Signs</legend>
			<input type="hidden" name="pid" value="<?= $_GET['id'] ?>">
			<label>Weight <input type="text" name="weight" pattern="<?= (new VitalDAO())->getByName('Weight')->getPattern()?>"> </label>
			<label>Height <input type="text" name="height" pattern="<?= (new VitalDAO())->getByName('Height')->getPattern()?>"> </label>
			<label>Fundus Height <input type="text" name="fundus_height" pattern="<?= (new VitalDAO())->getByName('Fundus Height')->getPattern()?>"> </label>
			<label>Fetal Heart Rate <input type="number" name="fhr" pattern="<?= (new VitalDAO())->getByName('Fetal Heart Rate')->getPattern()?>"> </label>
			<label>Blood Pressure <input type="text" pattern="<?= (new VitalDAO())->getByName('Blood Pressure')->getPattern()?>" title="Example: 125/70" placeholder="Example: 125/70" name="bp"> </label>
			<label>Protein <input type="text" pattern="<?= (new VitalDAO())->getByName('Protein')->getPattern()?>" name="protein"></label>
			<label>Glucose <input type="text" pattern="<?= (new VitalDAO())->getByName('Glucose')->getPattern()?>" name="glucose"></label>
			<label>PCV <input type="text" pattern="<?= (new VitalDAO())->getByName('PCV')->getPattern()?>" name="pcv"></label>
		</fieldset>
		<fieldset>
			<?php
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubscribedDoctorDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			$all_specialty = (new StaffSpecializationDAO())->getSpecializations(null);
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
			$depts = (new DepartmentDAO())->getDepartments();
			?>
			<legend>Who do you want to see?</legend>
			<label>Select Department <select name="did" id="department_id" required="required">
					<option value="">All Departments</option>
					<?php foreach ($depts as $dept) {
						echo '<option value="' . $dept->getId() . '">' . $dept->getName() . '</option>';
					} ?>
				</select></label>
			<label>
				Specialty:
				<select name="room_id" data-placeholder="- - select a specialty - -" class="srm">
					<option value=""></option>
					<?php foreach ($all_specialty as $_) {
						$subscribed = (new SubscribedDoctorDAO())->getSubscriptionsBySpecialty($_->getId(), null);
						$subscribed_staff = count($subscribed) != 0 ? ' data-subscribed="true"' : ' data-subscribed="false"'; ?>
						<option value="<?= $_->getId() ?>"<?= $subscribed_staff ?>><?= $_->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			<label class="pull-left"><input type="checkbox" name="followUp"> Follow-Up consultation</label>
			<label class="pull-right"><input type="checkbox" onchange="refreshSubscribersList(this)"> Show only subscribed consultants </label>
			<div class="clear"></div>
		</fieldset>
		<input id="SaveAll" type="submit" class="btn" value="Save"/>
		<div class="boxy-footer">
			<span>Antenatal Package: <?= $antenatalInstance->getPackage()->getName() ?></span>
			<hr>
			<span>Last <em>Confirmed</em> Diagnosis: <br>
				<?= ($lastDiagnosis != null) ? $lastDiagnosis->case : 'N/A' ?> <?php if ($lastDiagnosis != null){ ?><span class="fadedText"> on <?= date("dS M, Y", strtotime($lastDiagnosis->date_of_entry)) ?></span></span><?php } ?>
			<hr>
			<?php if ($consultation) { ?>Seen by "<?= $consultation->getName() ?>" on <?= date("Y M, d h:iA", strtotime($_consultation->getTransactionDate())) ?><?php } else { ?>Not seen a consultant yet<?php } ?>
		</div>
	</form>
</section>