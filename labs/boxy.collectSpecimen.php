<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$bills = new Bills();
$lab = (new PatientLabDAO())->getLab($_REQUEST['id']);
$pat = (new PatientDemographDAO())->getPatient($lab->getLabGroup()->getPatient()->getId(), FALSE, null, null);

$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$selfOwe = $_ > 0 ? $_ : 0;

error_log(json_encode(boolval($_REQUEST['aid'])));

if ($_POST) {
	if ($selfOwe - $creditLimit > 0 && (boolval($_REQUEST['aid']) != true)) {
		exit("error:Patient has outstanding credit");
	}
	
	$staff = new StaffDirectory();
	$staff->setId($_SESSION['staffID']);
	$pl = (new PatientLabDAO())->getLab($_POST['id']);
	$pl->setSpecimenCollectedBy($staff);
	$pl->setSpecimenDate(date("Y-m-d H:i:s", time()));

	if (!empty($_POST['specimen_note'])) {
		$pl->setSpecimenNote($_POST['specimen_note']);
	} else {
		$pl->setSpecimenNote(null);//
		//exit("error:Specimen Note required");
	}
	if (!count($_POST['testSpecimen'])) {
		exit("error:No specimen selected");
	}
	$specimens_selected = array();
	foreach ($_POST['testSpecimen'] as $s) {
		$specimens_selected[] = (new LabSpecimenDAO())->getSpecimen($s);
	}

	$pl->setSpecimens($specimens_selected);
	$data = (new PatientLabDAO())->takeSpecimen($pl);
	if ($data !== null) {
		exit("ok");
	}
	exit ("Failed to update specimen info");
}
?>
<div style="width: 600px;">
	<div class="well">
		Patient's Outstanding balance: <?= $currency ?> <?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
		<label>Request Note <div class="fadedText"><?= $lab->getLabGroup()->getRequestNote() ?></div> </label>
		<label>Preferred Specimen(s):
			<select multiple="multiple" disabled="disabled">
				<?php
				$epecimens = (new LabSpecimenDAO())->getSpecimens();
				foreach ($epecimens as $s) {
					echo '<option value="' . $s->getId() . '" ';
					foreach ($lab->getLabGroup()->getPreferredSpecimens() as $s_) {
						echo($s->getId() == $s_->getId() ? ' selected="selected"' : "");
					}
					echo '>' . $s->getName() . '</option>';
				}
				?>
			</select></label>

		<span class="notify-bar">*make sure of the test/specimen match</span>
		<label>Specimen</label>
		<label><select name="testSpecimen[]" multiple="multiple">
				<?php
				foreach ($epecimens as $s) {
					echo '<option value="' . $s->getId() . '">' . $s->getName() . '</option>';
				}
				?>
			</select></label>
		<input type="hidden" name="testid" value="<?php echo $_GET['testid'] ?>">
		<input type="hidden" name="id" value="<?php echo $_GET['id'] ?>">
		<input type="hidden" name="aid" value="<?php echo $_GET['aid'] ?>">
		<label>Specimen Notes
			<textarea name="specimen_note"></textarea></label>

		<div class="btn-block">
			<button type="submit" name="button" class="btn" id="button" <?= (($selfOwe - $creditLimit > 0 && $_GET['aid'] != TRUE) /*|| $bb > $credit_limit*/ ? 'disabled="disabled"' : '') ?>>
				Save
			</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	function start() {
	}
	function done(s) {
		if (s == 'ok') {
			Boxy.info("Saved");
			Boxy.get($('.close')).hideAndUnload();
		} else {
			Boxy.alert('ERROR: (<em>' + s + '</em>)');
		}
	}
</script>
