<?php
//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
//$p = new Manager();
//$aid_ = isset($_REQUEST['aid'])?$_REQUEST['aid']:NULL;
//if (isset($_POST['p_value'])) {
//    echo $p->saveVitalSign($_REQUEST['type'], $_REQUEST['pid'], $_REQUEST['p_value'], $aid_);
//    exit;
//}

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
if($_POST){
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	if(isset($_POST['type_id'])){
		$type = (new VitalDAO())->get($_POST['type_id'], $pdo);
		$new = (new VitalSign())->setType($type)->setPatient(new PatientDemograph($_POST['pid']))->setInPatient( isset($_POST['aid']) ? new InPatient($_POST['aid']) : NULL )
			->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_SESSION['staffID']) )
			->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['p_value'])->add($pdo);
		if($new != null){
			$pdo->commit();
			exit('success:Saved');
		}
		$pdo->rollBack();
		exit('error:Failed to save reading');
	} else if(isset($_POST['type'])) {
		$weight = $_POST['weight_value'];
		$height = $_POST['height_value'];
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
		if($new != null && $new1 != null && $new2 != null){
			$pdo->commit();
			exit('success:Saved');
		}
	}
	$pdo->rollBack();
	exit('error:Failed to save');
}


$vitalObj = (new VitalDAO())->get($_GET['type_id']);
$patient = (new PatientDemographDAO())->getPatient($_REQUEST['id']);
?>
<div style="width: 500px">
	<?php
	//$what = $unit = $placeholder = "";
	//$type = $_GET['type'];
	//if ($type == "weight") {
	//	$what = "Weight";
	//	$unit = "KiloGramme (kg)";
	//	$placeholder = "Example: 56.9";
	//} elseif ($type == "height") {
	//	$what = "Height";
	//	$unit = "Meter (m)";
	//	$placeholder = "Example: 2.3";
	//} elseif ($type == "pulse") {
	//	$what = "Pulse";
	//	$unit = "beats per minute";
	//	$placeholder = "Example: 56";
	//} else if ($type == "rp") {
	//	$what = "Respiratory Rate";
	//	$unit = "breath per minute";
	//	$placeholder = "Example: 70";
	//} else if ($type == "bp") {
	//	$what = "Blood Pressure";
	//	$unit = "mmHg";
	//	$placeholder = "Systolic/Diastolic (Example: 120/80)";
	//} else if ($type == "temp") {
	//	$what = "Temperature";
	//	$unit = "&deg;C";
	//	$placeholder = "Example: 34.6";
	//} else if ($type == "fundus_height") {
	//	$what = "Fundus Height";
	//	$unit = "cm";
	//	$placeholder = "Example: 20.6";
	//} else if ($type == "glucose") {
	//	$what = "Glucose";
	//	$unit = "mg/dL";
	//	$placeholder = "Example: 95";
	//} else if ($type == "protein") {
	//	$what = "Protein";
	//	$unit = "";
	//	$placeholder = "Example: 300";
	//} else if ($type == "fhr") {
	//	$what = "Fetal Heart Rate";
	//	$unit = " bpm";
	//	$placeholder = "Example: 150";
	//} else if ($type == "dilation") {
	//	$what = "Dilation";
	//	$unit = " cm";
	//	$placeholder = "Example: 7";
	//} else if ($type == "mid-arm-circumference") {
	//	$what = "Mid Arm Circumference";
	//	$unit = " cm";
	//	$placeholder = "Example: 12";
	//} else if ($type == "head-circumference") {
	//	$what = "Head Circumference";
	//	$unit = " cm";
	//	$placeholder = "Example: 40";
	//} else if ($type == "length-of-arm") {
	//	$what = "Length of Arm";
	//	$unit = " cm";
	//	$placeholder = "Example: 10";
	//} else if ($type == "pcv") {
	//	$what = "PCV";
	//	$unit = "";
	//	$placeholder = "Example: 10";
	//} else if ($type == "urine") {
	//	$what = "Urine";
	//	$unit = "";
	//	$placeholder = "Example: 9";
	//} else if ($type == "spo2") {
	//	$what = "SpO<sub>2</sub>";
	//	$unit = "%";
	//	$placeholder = "Example: 99";
	//} else if ($type == "pain_scale") {
	//	$what = "Pain Scale";
	//	$unit = "";
	//	$placeholder = "Example: 3";
	//}
	?>
	<form method="post" name="form1" id="form1" action="/vitals-all-new.php" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<span id="message">&nbsp;</span>
		<label>Take <span style="text-decoration:underline"><?= $vitalObj->getName(); ?></span> reading for <?= $patient->getFullname() ?></label>
		<?php if(in_array($vitalObj->getName(), ["BMI", "BSA"])){?>
			<div class="row-fluid">
				<label class="span8">Weight<input type="text" name="weight_value" required pattern="<?=(new VitalDAO())->getByName("Weight")->getPattern() ?>"/> </label>

				<span class="border span4 no-label"><?= (new VitalDAO())->getByName("Weight")->getUnit() ?></span>
			</div>
			<div class="row-fluid">
				<label class="span8">Height<input type="text" name="height_value" required pattern="<?=(new VitalDAO())->getByName("Weight")->getPattern() ?>"/> </label>

				<span class="border span4 no-label"><?= (new VitalDAO())->getByName("Height")->getUnit() ?></span>
			</div>
			<input type="hidden" name="type" value="<?= $vitalObj->getName() ?>">
		<?php } else {?>
			<div class="row-fluid">
				<label class="span8"><input type="text" name="p_value" id="p_value" required pattern="<?=$vitalObj->getPattern() ?>"/> </label>

				<span class="border span4"><?= $vitalObj->getUnit() ?></span>
			</div>
			<input type="hidden" name="type_id" value="<?= $vitalObj->getId() ?>">
		<?php }?>
		

		<div class="btn-block">
			<button type="submit" class="btn">Save &raquo;</button>
			<button type="button" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel</button>
		</div>
		<input type="hidden" name="pid" value="<?= $_GET['id'] ?>">
		<?php if(isset($_GET['aid'])){?><input type="hidden" name="aid" value="<?= $_GET['aid']?>"><?php }?>
	</form>

</div>
<script type="text/javascript">
	function start() {

	}
	function done(s) {
		var msg = s.split(":");
		if (msg[0] === 'success') {
			//refresh this tab
			<?php if(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'inpatient_profile.php'){?>
			showTabs(11);
			<?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'patient_antenatal_profile.php'){ ?>
			showTabs(7);
			<?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'patient_labour_profile.php'){ ?>
			showTabs(1);
			<?php }else {?>
			showTabs(2);
			<?php }?>
			//then close this dialog,
			Boxy.get($('.close')).hideAndUnload();
			Boxy.info(msg[1], function(){
			});
			
		} else {
			$('span#message').html(msg[1]).attr('class', 'warning-bar');
		}
	}
</script>