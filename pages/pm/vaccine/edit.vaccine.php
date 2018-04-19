<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/8/14
 * Time: 4:37 PM
 */
require_once $_SERVER   ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
require_once $_SERVER   ['DOCUMENT_ROOT'] . '/functions/utils.php';
if ($_POST) {
	$v = (new VaccineDAO())->getVaccine($_POST['vaccine_id']);
	if (!empty($_POST['name'])) {
		$v->setName($_POST['name']);
	}
	if (!empty($_POST['description'])) {
		$v->setDescription($_POST['description']);
	}
	if (!empty($_POST['price'])) {
		$v->setPrice(parseNumber($_POST['price']));
	}
	if (isset($_POST['active'])) {
		$v->setActive($_POST['active']);
	} else {
		$v->setActive('0');
	}
	
	$v2 = (new VaccineDAO())->updateVaccine($v);
	$object = (object)null;
	if ($v2 != null) {
		$object->status = "success";
		$object->message = "Vaccine updated successfully";
		exit(json_encode($object));
	} else {
		$object->status = "error";
		$object->message = "An error occurred while saving the changes";
		exit(json_encode($object));
	}
}

$vaccine = (new VaccineDAO())->getVaccine($_GET['id']);
$active_vaccine = ($vaccine->getActive()) ? ' checked="checked"' : '';
?>
<div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: beginSave, onComplete: endSave})">
		<div class="notify-bar">
			We can only update the name, description and price of the vaccine at this
			time
		</div>
		<span class="well well-small"></span>
		<label><input type="checkbox" name="active"<?= $active_vaccine ?> value="1">
			Active</label>
		<label>Name
			<input type="text" name="name" value="<?= $vaccine->getName() ?>"></label>
		<label>Description
			<input type="text" name="description" value="<?= $vaccine->getDescription() ?>"></label>
		<label>Base Price
			<input name="price" type="number" min="0" value="<?= $vaccine->getPrice() ?>"></label>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
			<input type="hidden" name="vaccine_id" value="<?= $vaccine->getId() ?>">
		</div>
	</form>
</div>
<script type="text/javascript">
	function beginSave() {
		$('.notify-bar + span').html('<img src="/img/loading.gif"> updating...');
	}
	function endSave(s) {
		console.log(s);
		var ret = JSON.parse(s);
		if (ret.status == "success") {
			Boxy.info(ret.message, function () {
				Boxy.get($('.close')).hideAndUnload();
			})
		} else {
			Boxy.alert(ret.message);
		}
		$('.notify-bar + span').html(ret.message);//?
	}

</script>