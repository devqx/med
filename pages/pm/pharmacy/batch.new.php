<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/25/14
 * Time: 2:45 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$all = (new ServiceCenterDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugBatch.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$batch = new DrugBatch();
	
	if (!is_blank($_POST['name'])) {
		$batch->setName($_POST['name']);
		$batch->setDrug(new Drug($_POST['drug_id']));
	} else {
		exit("error:Batch Identification is required");
	}
	if (!is_blank($_POST['quantity'])) {
		$batch->setQuantity(parseNumber($_POST['quantity']));
	} else {
		exit("error:Batch Quantity is required");
	}
	if (!is_blank($_POST['date'])) {
		$batch->setExpirationDate($_POST['date']);
		//TODO: has to be in the future
	} else {
		exit("error:Expiration Date is required");
	}
	if (!is_blank($_POST['service_centre_id'])) {
		$batch->setServiceCentre(new ServiceCenter($_POST['service_centre_id']));
	} else {
		exit("error:Pharmacy location is required");
	}
	
	$new = (new DrugBatchDAO())->add($batch);
	if ($new !== null) {
		exit("ok");
	}
	exit("error:Failed to add batch");
}

?>
<section style="width: 600px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: saved})">
		<label>Pharmacy <select name="service_centre_id">
				<?php foreach ($all as $center) {
					if ($center->getType() === "Pharmacy") { ?>
						<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
					<?php }
				} ?>

			</select> </label>
		<label>Batch #
			<input type="text" name="name" placeholder="Identify the Batch; Number or anything"> </label>
		<label>Available Stock Quantity<input type="number" name="quantity" value="0"> </label>

		<label>Expiration <input name="date" type="text"> </label>
		<input type="hidden" name="drug_id" value="<?= $_GET['d_id'] ?>">

		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
		</div>
	</form>
	<script type="text/javascript">
		$('input[name*="date"]').datetimepicker({format: 'Y-m-d', timepicker: false});

		function saved(s) {
			var data = s.split(":");
			if (data[0] === "error") {
				Boxy.alert(data[1]);
			} else if (s === "ok") {
				Boxy.get($(".close")).hideAndUnload();
			}
		}
	</script>
</section>
