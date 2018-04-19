<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabResultDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabResultData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabResult.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if ($_POST) {
	$pl = (new PatientLabDAO())->getLab($_POST['id']);
	if (is_blank($_POST['testnote'])) {
		exit("Enter lab result notes");
	}
	$pl->setNotes($_POST['testnote']);
	$pl->setTestDate(date("Y-m-d H:i:s", time()));
	$pl->setPerformedBy($this_user);
	
	$result = $pl->getLabResult();
	
	$result->setLabTemplate($pl->getLabResult()->getLabTemplate());
	$result->setPatientLab($pl);
	$data = [];
	foreach ($_POST['lrData'] as $i => $lrDataValue) {
		list($resultId, $templateDataId) = explode(", ", $i);
		$datum = (new LabResultDataDAO())->getLabResultDatum($resultId, $templateDataId, false);
		$datum->setValue($lrDataValue);
		
		$data[] = $datum;
	}
	$result->setData($data);
	$pl->setLabResult($result);
	
	$data = (new PatientLabDAO())->updateResult($pl);
	if ($data !== null) {
		exit("success:Result Updated");
	}
	exit("error:Failed to save result");
}

$pl = (new PatientLabDAO())->getLab($_GET['plId']);
$lab = $pl->getTest();
?>
<div style="min-width: 500px">
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
	      onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
		<label>
			Preferred Specimens:
			<select disabled="disabled" multiple="multiple">
				<?php $specimens = (new LabSpecimenDAO())->getSpecimens();
				foreach ($specimens as $s) {?>
					<option value="<?=$s->getId()?>"
					<?php foreach ($pl->getLabGroup()->getPreferredSpecimens() as $s_) {?>
						<?=($s->getId() == $s_->getId() ? ' selected="selected"' : "")?>
					<?php }?>><?=$s->getName()?></option>
				<?php }?>
			</select>
		</label>
		<label>
			Taken Specimens:
			<select disabled="disabled" multiple="multiple">
				<?php	foreach ($specimens as $s) {?>
					<option value="<?= $s->getId()?>"
					<?php foreach ($pl->getSpecimens() as $s_) {?>
						<?= ($s->getId() == $s_->getId() ? ' selected' : "");
					}?>><?=$s->getName()?></option>
				<?php }?></select>
		</label>
		
		<?php foreach ($pl->getLabResult()->getData() as $lrData) {?>
			<?php //foreach ($lab->getLabTemplate()->getData() as $temp) {
				//\$temp = new LabTemplateData();
				//if ($lrData->getLabTemplateData()->getId() == $temp->getId()) {
					?>
					<label>
						<?= $lrData->getLabTemplateData()->getMethod()->getName() ?>
						<input type="text" name="lrData[<?= $lrData->getLabResult()->getId() ?>, <?= $lrData->getLabTemplateData()->getId() ?>]"
						       value="<?= $lrData->getValue() ?>" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $lrData->getLabTemplateData()->getMethod()->getName() ?>">
					</label>
				<?php //}
			//}
		} ?>

		<hr>

		<label>
			<textarea name="testnote" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Notes"><?= $pl->getNotes() ?></textarea></label>

		<div class="btn-block">
			<input type="hidden" name="id" value="<?= $_GET['plId'] ?>">
			<button class="btn" type="submit" name="button" id="button">Update Changes</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
	}

	function done(s) {
		var data = s.split(":");
		if (data[0] === 'success') {
			Boxy.info("Result Updated", function () {
				Boxy.get($('.close')).hideAndUnload();
			});
		} else {
			Boxy.alert(data[1]);
		}
	}
</script>