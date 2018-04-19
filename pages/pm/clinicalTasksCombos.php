<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/27/17
 * Time: 10:58 AM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskComboDAO.php';
$combos = (new ClinicalTaskComboDAO())->all();
?>
<section style="width:1000px">
	Existing Clinical Tasks Combos <a href="javascript:;" class="btn pull-right new_combo">Add</a>
	<div class="clear"></div>

	<ul class="list-blocks"><?php foreach ($combos as $H) {//$H=new ClinicalTaskCombo()?>
			<li class="tag"><a class="combo" data-id="<?= $H->getId() ?>" href=""><?= $H->getName() ?></a>
			<a href="#" class="pull-right combo-edit" data-id="<?= $H->getId() ?>">Edit</a></li><?php } ?></ul>
</section>
<script type="text/javascript">
	$(document).on('click', '.combo', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/clinical_combo.view.php?id=" + $(this).data("id"));
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
	$(document).on('click', '.combo-edit', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/clinical_combo.edit.php?id=" + $(this).data("id"));
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
	$(document).on('click', '.new_combo', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/clinical_combo.add.php");
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
</script>
