<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 11:25 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Dentistry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$service = (new DentistryDAO())->get($_GET['id']);

$categories = (new DentistryCategoryDAO())->all();

if ($_POST) {
	if (!empty($_POST['name'])) {
		$service->setName($_POST['name']);
	} else {
		exit("error:Dentistry reference name is required");
	}
	//for the category, the browser makes sure it is selected
	$service->setCategory((new DentistryCategoryDAO())->get($_POST['category_id']));
	
	if (empty($_POST['base_price'])) {
		exit("error: Base Price is required");
	}
	$newDentistry = (new DentistryDAO())->update($service, parseNumber($_POST['base_price']));
	
	if ($newDentistry !== null) {
		exit("success:Updated Dentistry Service details");
	}
	exit("error:Update failed");
}
?>
<div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" name="editDentistryForm" onsubmit="return AIM.submit(this, {onStart:changeDentistryStart, onComplete: changeDentistryStop})">
		<label>Name
			<input type="text" name="name" value="<?= $service->getName() ?>"></label>
		<label>Category
			<select name="category_id" required="required" placeholder="--- Select category ---">
				<option></option>
				<?php foreach ($categories as $c) { ?>
					<option value="<?= $c->getId() ?>"<?= ($c->getId() == $service->getCategory()->getId() ? ' selected="selected"' : '') ?>><?= $c->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Base Price
			<input name="base_price" type="number" min="0" value="<?= (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($service->getCode()) ?>"></label>
		<div class="btn-block" style="margin-top:10px;">
			<button type="submit" class="btn">Update</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Close
			</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function changeDentistryStart() {
	}
	function changeDentistryStop(s) {
		var answer = s.split(":");
		if (answer[0] === "error") {
			Boxy.alert(answer[1])
		} else {
			Boxy.info(answer[1], function () {
				Boxy.get(".close").hideAndUnload();
				reloadServices();
			});
		}
	}
</script>