<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 1/9/17
 * Time: 3:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if (!$this_user->hasRole($protect->pharmacy_super)) exit($protect->ACCESS_DENIED);
$DAO = new ItemBatchDAO();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$all = (new ServiceCenterDAO())->all('Item');
$batch = $DAO->getBatch($_GET['batch']);
if ($_POST) {
	$batch->setQuantity(parseNumber($_POST['quantity']));
	if (!is_blank($_POST['service_centre_id'])) {
		$batch->setServiceCenter(new ServiceCenter($_POST['service_centre_id']));
	} else {
		exit("error:Business location is required");
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
	<label>Business Center <select name="service_centre_id">
			<?php foreach ($all as $center) { ?>
				<option value="<?= $center->getId() ?>"<?= (($batch->getServiceCenter() != null && $batch->getServiceCenter()->getId() == $center->getId()) ? ' selected="selected"' : '') ?>><?= $center->getName() ?></option>
			<?php } ?>

		</select> </label>
	<label>Adjusted Batch Quantity <input type="number" name="quantity" value="0" min="0" required="required"> </label>

	<div class="btn-block">
		<button class="btn" type="submit">Adjust</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>
<script type="text/javascript">
	function completed(s) {
		var data = s.split(":");
		if (data[0] == "error") {
			Boxy.alert(data[1]);
		} else if (s == "ok") {
			Boxy.get($(".close")).hideAndUnload();
		}
	}
</script>