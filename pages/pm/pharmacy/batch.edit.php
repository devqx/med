<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/26/14
 * Time: 3:42 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->pharmacy_super)) exit($protect->ACCESS_DENIED);
$DAO = new DrugBatchDAO();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$all = (new ServiceCenterDAO())->all('Pharmacy');
$batch = $DAO->getBatch($_GET['batch']);
if ($_POST) {
	$batch->setQuantity(parseNumber($_POST['quantity']));
	if (!is_blank($_POST['service_centre_id'])) {
		$batch->setServiceCentre(new ServiceCenter($_POST['service_centre_id']));
	} else {
		exit("error:Pharmacy location is required");
	}
	if( !is_blank($_POST['batch_name'])){
		$batch->setName($_POST['batch_name']);
	}else{
		exit("error:Batch name is required");
	}
	
	if(!is_blank($_POST['date'])){
		$batch->setExpirationDate($_POST['date']);
	}else{
		exit("error:Expiration date is required");
	}
	
	
	$new = $DAO->stockAdjust($batch);
	if ($new !== null) {
		exit("ok");
	}
	exit("error:Failed to update Stock");
}
?>
<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: completed})">
	<div class="fadedText">Available: <?= $batch->getQuantity() ?></div>
	<label>Pharmacy <select name="service_centre_id">
			<?php foreach ($all as $center) { ?>
				<option value="<?= $center->getId() ?>"<?= (($batch->getServiceCentre() != null && $batch->getServiceCentre()->getId() == $center->getId()) ? ' selected="selected"' : '') ?>><?= $center->getName() ?></option>
			<?php } ?>
		
		</select> </label>
	<label>Batch #
		<input type="text" name="batch_name" value="<?= $batch->getName() ?>">
	</label>
	<label>
		Expiration Date
		<input type="text" name="date" value="<?= $batch->getExpirationDate() ?>">
	</label>
	<label>Adjusted Batch Quantity <input type="number" name="quantity" value="<?= $batch->getQuantity() ?>" min="0" required="required"> </label>
	
	<div class="btn-block">
		<button class="btn" type="submit">Adjust</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>
<script type="text/javascript">
	$('input[name*="date"]').datetimepicker({format: 'Y-m-d', timepicker: false});

	function completed(s) {
		var data = s.split(":");
		if (data[0] == "error") {
			Boxy.alert(data[1]);
		} else if (s == "ok") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>