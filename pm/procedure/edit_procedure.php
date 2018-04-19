<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/14
 * Time: 3:21 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Procedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$cats = (new ProcedureCategoryDAO())->all();
$procedure = (new ProcedureDAO())->getProcedure($_GET['id']);

$_ = new Procedure();

$desc = $_::$desc;

if ($_POST) {
	$p = (new ProcedureDAO())->getProcedure($_GET['id']);
	if (!empty($_POST['name'])) {
		$p->setName($_POST['name']);
	} else {
		exit("error:Name is required");
	}
	if (is_blank($_POST['category_id'])) {
		exit("error: Category is required");
	} else {
		$p->setCategory(new ProcedureCategory($_POST['category_id']));
	}
	if (is_blank($_POST['base_price'])) {
		exit("error: ".str_replace(":", "", $desc[0])." is required");
	} else {
		$p->setBasePrice(parseNumber($_POST['base_price']));
	}
	if (is_blank($_POST['theatre_price'])) {
		exit("error: ".str_replace(":", "", $desc[3])." is required");
	} else {
		$p->setPriceTheatre(parseNumber($_POST['theatre_price']));
	}

	if (is_blank($_POST['surgeon_price'])) {
		exit("error: ".str_replace(":", "", $desc[1])." is required");
	} else {
		$p->setPriceSurgeon(parseNumber($_POST['surgeon_price']));
	}

	if (is_blank($_POST['anaesthesia_price'])) {
		exit("error: ".str_replace(":", "", $desc[2])." is required");
	} else {
		$p->setPriceAnaesthesia(parseNumber($_POST['anaesthesia_price']));
	}

	if (is_blank($_POST['icd_code'])) {
		exit("error: ICD Code is required");
	} else {
		$p->setIcdCode($_POST['icd_code']);
	}
	if (is_blank($_POST['description'])) {
		exit("error: Description is required");
	} else {
		$p->setDescription($_POST['description']);
	}
	$upProced = (new ProcedureDAO())->updateProcedure($p);

	if ($upProced !== null) {
		exit("success:Procedure " . $upProced->getName() . " updated");
	}
	exit("error:Failed to update procedure");
}
?>
<section>
	<div>
		<form method="post" name="newProcedForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:__start, onComplete: __done})">
			<label>Name <input type="text" name="name" value="<?= $procedure->getName() ?>"></label>
			<label>Category <span class="pull-right"><a href="javascript:;" id="catLink">Add</a></span>
				<select name="category_id" placeholder="select a category">
					<option></option>
					<?php foreach ($cats as $cat) { ?>
						<option value="<?= $cat->getId() ?>" <?= ($cat->getId() == $procedure->getCategory()->getId() ? ' selected="selected"' : '') ?>><?= $cat->getName() ?></option><?php } ?>
				</select></label>
			<label>Icd Code <input type="text" name="icd_code" value="<?= $procedure->getIcdCode() ?>"> </label>
			<label>Description <input type="text" name="description" value="<?= $procedure->getDescription() ?>"> </label>
			<label><?=$desc[0]?>
				<input name="base_price" type="number" value="<?= $procedure->getBasePrice() ?>" min="0"></label>
			<label><?=$desc[3]?>
				<input name="theatre_price" type="number" value="<?= $procedure->getPriceTheatre() ?>" min="0"></label>
			<label><?=$desc[1]?>
				<input name="surgeon_price" type="number" value="<?= $procedure->getPriceSurgeon() ?>" min="0"></label>
			<label><?=$desc[2]?>
				<input name="anaesthesia_price" type="number" value="<?= $procedure->getPriceAnaesthesia() ?>" min="0"></label>
			<div class="btn-block" style="margin-top: 10px;">
				<button type="submit" class="btn"><i class="icon-save"></i> Save</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
			</div>
		</form>
	</div>
</section>
<script type="text/javascript">
	function __start() {
	}
	function __done(s) {
		var info = s.split(":");
		if (info[0] === "error") {
			Boxy.alert(info[1]);
		} else if (info[0] === "success") {
			Boxy.get($(".close")).hideAndUnload()
			Boxy.info(info[1]);
		}
	}
</script>