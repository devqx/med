<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAllergens.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AllergenCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AllergenCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
$MainConfig = new MainConfig();

$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy)) {
	exit ($protect->ACCESS_DENIED);
}
$allergen_cats = (new AllergenCategoryDAO())->getAll();
$super_generic = (new SuperGenericDAO())->getAll();
if ($_POST) {
	if (is_blank($_POST['allergen_category'])) {
		exit('error:Alergen category is required');
	}
	if ($_POST['allergen_category'] == 1) { // drug
		if (is_blank($_POST['super_generic']) || is_blank($_POST['reaction']) || is_blank($_POST['severity'])) {
			exit('error:Incomplete details for drug-related allergen');
		}
	}
	if (!in_array($_POST['allergen_category'], [1])) { // food and environment
		if (is_blank($_POST['allergen']) || is_blank($_POST['reaction']) || is_blank($_POST['severity'])) {
			exit('error:Incomplete details for non-drug-related allergen');
		}
	}
	//$pdo = (new MyDBConnector())->getPDO();
	//$pdo->beginTransaction();
	$patObj = (new PatientDemographDAO())->getPatient($_POST['id'], false, null, null);
	$allergy = (new PatientAllergens())->setPatient($patObj)->setAllergen(!is_blank($_POST['allergen']) ? $_POST['allergen'] : null)->setReaction(!is_blank($_POST['reaction']) ? $_POST['reaction'] : null)->setSeverity(!is_blank($_POST['severity']) ? $_POST['severity'] : null)->setNotedBy(new StaffDirectory($_SESSION['staffID']))->setCategory(new AllergenCategory($_POST['allergen_category']))->setSuperGeneric(!is_blank($_POST['super_generic']) ? new SuperGeneric($_POST['super_generic']) : null)->add();
	
	if (!$allergy) {
		exit('error: Failed to save patient allergen data');
	} else {
		exit('ok');
	}
}
?>
<div style="width: 450px;">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: start, onComplete: done})">
		<label>Category<select name="allergen_category" id="allergen_category" class="wide">
				<option value="">-- select allergen category --</option>
				<?php foreach ($allergen_cats as $cat) { ?>
					<option value="<?= $cat->getId() ?>"><?= ucwords($cat->getName()) ?></option>
				<?php } ?>
			</select></label>
		<label id="drug_id">Drugs: <select name="super_generic" id="super_generic">
				<option value="">-- select the allergic drug --</option>
				<?php foreach ($super_generic as $super_gen) { ?>
					<option value="<?= $super_gen->getId() ?>"><?= $super_gen->getName() ?></option>
				<?php } ?>
			</select></label>
		<label class="allergen">Allergen<input type="text" name="allergen" id="allergen"></label>
		<label>Reaction <input type="text" name="reaction" id="reaction"></label>
		<label>Severity<select name="severity" class="wide">
				<?php foreach ($MainConfig::allergenSeverities() as $val => $sev) { ?>
					<option value="<?= $val ?>"><?= $sev ?></option>
				<?php } ?>
			</select></label>
		<div class="btn-block"><input type="hidden" name="id" value="<?= $_GET['id']; ?>"/>
			<button type="submit" class="btn">Save &raquo;</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
	}
	function done(s) {
		if (s === "ok") {
			Boxy.get($(".close")).hideAndUnload();
			$("#allerg").click();
		} else {
			var data = s.split(':');
			Boxy.warn(data[1]);
		}
	}

	$(document).ready(function () {
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
	});
</script>
