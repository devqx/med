<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/8/15
 * Time: 2:27 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NursingService.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/NursingServiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$service = (new NursingServiceDAO())->get($_GET['id']);

if ($_POST) {
	$service = (new NursingServiceDAO())->get($_POST['id']);
	if (!is_blank($_POST['service_name'])) {
		$service->setName(escape($_POST['service_name']));
	} else {
		exit("error:Service name is blank");
	}
	if (!is_blank($_POST['service_price'])) {
		$service->setBasePrice(parseNumber($_POST['service_price']));
	} else {
		exit("error:Service price is blank");
	}
	
	if ((new NursingServiceDAO())->update($service)) {
		exit("success:Service Updated!");
	}
	exit("error:failed to update service details");
}

?>
<div style="width: 600px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" id="form3" onsubmit="return AIM.submit(this, {onStart: _t1_, onComplete: _o1_})">
		<span></span>
		<label>Service Name
			<input value="<?= $service->getName() ?>" type="text" name="service_name" required="required">
		</label>
		<label>Service Cost <span class="pull-right"><i class="icon-info-sign"></i> <em>might be charged during clinical task execution</em></span><input type="number" value="<?= $service->getBasePrice() ?>" name="service_price" min="0" step="0.01" required="required">
		</label>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">
				Cancel
			</button>
		</div>
		<input type="hidden" name="id" value="<?= $service->getId() ?>">
	</form>
</div>
<script type="text/javascript">
	function _t1_() {
	}
	function _o1_(s) {
		if (s.split(":")[0] == "error") {
			$('#form3 > span:first-child').html(s.split(":")[1]).removeClass("warning-bar").addClass("warning-bar");
		} else if (s.split(":")[0] == "success") {
			//reload this tab
			showTabs(7);
			$('#form3 > span:first-child').html('');
			Boxy.get($(".close")).hide()
		}
	}
</script>
