<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 11:25 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Scan.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ScanCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$scan = (new ScanDAO())->getScan($_GET['id']);

$cats = (new ScanCategoryDAO())->getCategories();

if ($_POST) {
	if (!empty($_POST['name'])) {
		$scan->setName($_POST['name']);
	} else {
		exit("error:Scan reference name is required");
	}
	//for the category, the browser makes sure it is selected
	$scan->setCategory((new ScanCategoryDAO())->getCategory($_POST['category_id']));
	
	if (empty(parseNumber($_POST['base_price']))){
		exit("error: Base Price is required");
	}
	$newScan = (new ScanDAO())->updateScan($scan, parseNumber($_POST['base_price']));
	
	if ($newScan !== null) {
		exit("success:Updated Scan details");
	}
	exit("error:Update failed");
}
?>
<div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" name="editScanForm" onsubmit="return AIM.submit(this, {onStart:changeScanStart, onComplete: changeScanStop})">
		<label>Name <input type="text" name="name" value="<?= $scan->getName() ?>"></label>
		<label>Category
			<select name="category_id" required="required" placeholder="--- scan category ---">
				<option></option>
				<?php foreach ($cats as $c) { ?>
					<option value="<?= $c->getId() ?>"<?= ($c->getId() == $scan->getCategory()->getId() ? ' selected="selected"' : '') ?>><?= $c->getName() ?></option>
				<?php } ?>
			</select></label>
		<label>Base Price
			<input name="base_price" type="number" min="0" value="<?= (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($scan->getCode()) ?>"></label>
		<div class="btn-block" style="margin-top:10px;">
			<button type="submit" class="btn"><i class="icon-save"></i>Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Close
			</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function changeScanStart() {
	}
	function changeScanStop(s) {
		var answer = s.split(":");
		if (answer[0] === "error") {
			Boxy.alert(answer[1])
		} else {
			Boxy.info(answer[1], function () {
				Boxy.get(".close").hideAndUnload();
			});
		}
	}
</script>