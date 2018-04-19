<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/22/15
 * Time: 10:48 AM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/HistoryDAO.php';
$histories = (new HistoryDAO())->all();
?>
<section>
	Existing History Categories <a href="javascript:;" class="btn pull-right new_history">Add</a>
	<div class="clear"></div>

	<ul class="list-blocks"><?php foreach ($histories as $H) {//$H=new History()?>
			<li class="tag"><a class="history" data-id="<?= $H->getId() ?>" href=""><?= $H->getTemplate()->getLabel() ?></a>
			<a href="#" class="pull-right history-edit" data-id="<?= $H->getId() ?>">Edit</a></li><?php } ?></ul>
</section>
<script type="text/javascript">
	$(document).on('click', '.history', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_history.view.php?id=" + $(this).data("id"));
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
	$(document).on('click', '.history-edit', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_history.edit.php?id=" + $(this).data("id"));
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
	$(document).on('click', '.new_history', function (e) {
		if (!e.handled) {
			Boxy.load("/pages/pm/medical_history.add.php");
			e.preventDefault();
			e.handled = true;
			return false;
		}
	});
</script>
