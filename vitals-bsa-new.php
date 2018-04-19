<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/2/15
 * Time: 11:04 AM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
@session_start();

if (isset($_POST['w_value']) && isset($_POST['h_value'])) {
	$aid = isset($_REQUEST['aid']) ? new InPatient($_REQUEST['aid']) : null;
	$VitalSignDAO = new VitalSignDAO();
	$patObj = new PatientDemograph($_POST['pid']);
	$this_user = new StaffDirectory($_SESSION['staffID']);
	$encounter = null;
	$height = parseNumber($_POST['h_value']) / 100;
	$weight = parseNumber($_POST['w_value']);
	
	$value = number_format(parseNumber(($weight ^ 0.425 * $height ^ 0.725) * 0.007184), 2);
	$vs = (new VitalSign())->setValue($value)->setInPatient($aid)->setHospital(new Clinic(1))->setPatient($patObj)->setReadBy($this_user)->setEncounter($encounter)->setType('BSA');
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	if (!$VitalSignDAO->addVitalSign($vs, $pdo)) {
		$pdo->rollBack();
		exit("error:Failed to save BSA Reading");
	}
	$pdo->commit();
	exit('ok:BSA saved');
}
?>
<div style="width: 400px">
	
	<?php
	$what = array();
	$type = $_GET['type'];
	?>
	<form method="post" name="form1" id="form1" action="/vitals-bsa-new.php?type=<?php echo $type ?>&pid=<?php echo $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<span id="message">&nbsp;</span>

		<label>Take <span style="text-decoration:underline">Weight</span> reading </label>
		<div class="row-fluid">
			<label class="span8"><input name="w_value" id="p_value" placeholder="Example: 56.9" type="text"></label>
			<div class="span4 btn">KiloGramme (kg)</div>
		</div>
		<label>Take <span style="text-decoration:underline">Height</span> reading </label>
		<div class="row-fluid">
			<label class="span8"><input name="h_value" id="p_value" placeholder="Example: 2.3" type="text"></label>
			<div class="span4 btn">Meter (m)</div>
		</div>

		<div class="btn-block">
			<button type="submit" class="btn">Save &raquo;</button>
			<button type="button" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel</button>
		</div>
		<input  type="hidden" name="pid" value="<?= $_GET['id']?>">
		<input type="hidden" name="type" value="<?= $_GET['type']?>">
	</form>

</div>
<script type="text/javascript">
	function start() {

	}
	function done(s) {
		if (s.split(":")[0] == 'ok') {
			Boxy.info('Saved !');
			//refresh this tab
			<?php if(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'inpatient_profile.php'){?>
			showTabs(11);
			<?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'patient_antenatal_profile.php'){ ?>
			showTabs(7);
			<?php }else {?>
			showTabs(2);
			<?php }?>
			//then close this dialog,
			Boxy.get($('.close')).hideAndUnload();
		} else {
			var msg = s.split(":");
			$('span#message').html(msg[1]).attr('class', 'warning-bar');
		}
	}
</script>