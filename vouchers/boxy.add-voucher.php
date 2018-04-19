<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/20/15
 * Time: 1:00 PM
 */
if (!isset($_SESSION)) {
	@session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

$options = getTypeOptions('type', 'voucher_batch');
$service_centre = (new ServiceCenterDAO())->all('Voucher');
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Voucher.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/VoucherBatch.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/VoucherDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/VoucherBatchDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";
	
	if (parseNumber($_POST['amount']) == 0) {
		exit("error:Please enter amount");
	}
	if ($_POST['description'] == '') {
		exit("error:Please enter a description");
	}
	if ($_POST['expirationdate'] == '') {
		exit("error:Please select expiration date");
	}
	if ($_POST['service_centre_id'] == '') {
		exit("error:Please select service centre");
	}
	$vouchers = [];
	$batch = new VoucherBatch();
	$batch->setExpirationDate($_POST['expirationdate']);
	$batch->setDescription($_POST['description']);
	$batch->setQuantity($_POST['quantity']);
	$batch->setAmount(parseNumber($_POST['amount']));
	$batch->setDateGenerated(date("Y-m-d H:i:s"));
	$batch->setGenerator(new StaffDirectory($_SESSION['staffID']));
	$batch->setType($_POST['type']);
	$batch->setServiceCentre(new ServiceCenter($_POST['service_centre_id']));
	$addBatch = (new VoucherBatchDAO())->add($batch);
	if ($addBatch !== null) {
		for ($i = 0; $i < (int)$_POST['quantity']; $i++) {
			$v = new Voucher();
			$v->setBatch($addBatch->getId());
			
			$new_v = (new VoucherDAO())->add($v);
			if ($new_v) {
				$vouchers[] = $new_v;
			}
		}
	}
	
	if (count($vouchers) > 0) {
		exit("success:" . count($vouchers) . " Vouchers generated");
	}
	exit("error:Failed to generate vouchers");
}
?>

<section>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: s2, onComplete: d2})">
		<div class="w"></div>
		<label>Voucher Centre <select name="service_centre_id"><?php foreach ($service_centre as $sc) { ?>
					<option value="<?= $sc->getId() ?>"><?= $sc->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Quantity <input name="quantity" type="number" min="1" value="1"> </label>
		<label>Amount/Value <input name="amount" type="number" step="0.5" value="0" min="1"> </label>
		<label>Description <input name="description" type="text"></label>
		<label>Expiration Date <input name="expirationdate" id="expirationdate" type="text"></label>
		<label>Type <select name="type"><?php foreach ($options as $option) { ?>
					<option value="<?= $option ?>"><?= ucwords($option) ?></option>
				<?php } ?>
			</select></label>
		<div class="btn-block">
			<button class="btn" type="submit">Generate</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$('.boxy-content').on('submit', 'form', function () {
	});
	function s2() {
		$('.boxy-content > form > .w').html('<img src="/img/loading.gif"> Generating ...')
	}
	function d2(e) {
		var ret = e.split(":");
		if (ret[0] === "error") {
			$('.boxy-content > form > .w').html('<div class="warning-bar">' + ret[1] + '</div>')
		} else {
			Boxy.get($(".close")).hideAndUnload(function () {
				aTab(1);
			});
		}
	}
	$(document).ready(function () {
		$("#expirationdate").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: new Date()});
			}
		});
	});
</script>