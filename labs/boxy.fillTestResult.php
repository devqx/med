<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabResultData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabResult.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$lab = (new LabDAO())->getLab($_REQUEST['testType'], true);
if ($_POST) {
	$pl = (new PatientLabDAO())->getLab($_POST['id']);
	if (empty($_POST['test_value']) || empty($_POST['testnote'])) {
		//        exit("error:Test Value AND Note is required");
	}
	$pl->setNotes($_POST['testnote']);
	$pl->setTestDate(date("Y-m-d H:i:s", time()));
	$pl->setPerformedBy($this_user);
	
	$result = new LabResult();
	$result->setLabTemplate($lab->getLabTemplate());
	$result->setPatientLab(new PatientLab($_POST['id']));
	$data = [];
	foreach ($lab->getLabTemplate()->getData() as $idx => $temp) {
		$datum = new LabResultData();
		$datum->setValue($_POST['temp'][$temp->getId()]);
		$datum->setLabTemplateData(new LabTemplateData($temp->getId()));
		$data[] = $datum;
	}
	$result->setData($data);
	$pl->setLabResult($result);
	
	$data = (new PatientLabDAO())->saveResult($pl);
	if ($data !== null) {
		exit("ok");
	}
	exit("Failed to save result");
}
$pl = (new PatientLabDAO())->getLab($_GET['testId']);
?>
<div>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<label>
			Preferred Specimens:
			<select disabled="disabled" multiple="multiple">
				<?php
				$epecimens = (new LabSpecimenDAO())->getSpecimens();
				foreach ($epecimens as $s) {?>
					<option value="<?=$s->getId()?>"
					<?php foreach ($pl->getLabGroup()->getPreferredSpecimens() as $s_) {?>
						<?=($s->getId() == $s_->getId() ? ' selected="selected"' : "");
					}?>><?=$s->getName()?></option>
				<?php }?>
			</select>
		</label>
		<label>
			Received Specimens:
			<select disabled="disabled" multiple="multiple"><?php
				foreach ($epecimens as $s) {?>
					<option value="<?=$s->getId()?>"
					<?php foreach ($pl->getSpecimens() as $s_) {?>
						<?=($s->getId() == $s_->getId() ? ' selected="selected"' : "")?>
					<?php }?>><?=$s->getName()?></option>
				<?php }?></select>
		</label>
		<?php foreach ($lab->getLabTemplate()->getData() as $temp) { //$temp = new LabTemplateData();?>
			<label> <?= $temp->getMethod()->getName() ?>
				<?php if ($temp->getReference() != "") { ?><span class="pull-right fadedText">Reference (<?= $temp->getReference() ?>)</span><?php } ?>
				<!--<input type="text" name="<?= strtr($temp->getMethod()->getName(), array(' ' => '_')) ?>" value="" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $temp->getMethod()->getName() ?>" >-->
				<input type="<?=$temp->getMethod()->getType()?>" name="temp[<?= $temp->getId() ?>]" value="" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $temp->getMethod()->getName() ?>">
			</label>
		<?php } ?>
		<label>
			<textarea name="testnote" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Notes"></textarea></label>

		<div class="btn-block">
			<input type="hidden" name="id" value="<?= $_GET['testId'] ?>">
			<input type="hidden" name="testType" value="<?= $_GET['testType'] ?>">
			<button class="btn" type="submit" name="button" id="button">Save &raquo;</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel &raquo;</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
	}

	function done(s) {
		if (s == 'ok') {
			Boxy.info("Result Saved", function () {
				Boxy.get($('.close')).hideAndUnload();
			});
		} else {
			Boxy.alert(s);
		}
	}
</script>