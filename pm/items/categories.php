
<div style="width: 1000px">
	<?php
	include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemCategory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';


	$cat = (new ItemCategoryDAO())->getCategories();

	?>
<div class="well well-small">
	<span class="pull-right"><a href="javascript:;" onclick="refreshDrugsBoxy()">Refresh</a></span>
	<span class="pull-left"><a  href="javascript:;" data-href="/pm/items/add_category.php" class="boxylink">Add Category</a></span>
</div>
<table class="table table-hover table-striped small">
	<thead>
	<tr>
		<th style="">Category Name</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	<?php if (sizeof($cat) > 0) {
		foreach ($cat as $it) {?>
			<tr>
			<td nowrap style="width:20%"><?= $it->getName() ?></td>
			<td nowrap style="width: 15%">
				<i class="icon-edit"></i><a href="javascript:;" class="boxylink" data-href="/pm/items/editCategory.php?id=<?= ($it->getId()) ?>"  title="Edit <?= $it->getName() ?>:">Edit</a>
			</td>
			</tr><?php }
	} else { ?>
		<tr>
			<td colspan="10"><span class="notify-bar">No Category to show in this view</span></td>
		</tr>
	<?php } ?>
	</tbody>
</table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('.table.table-hover.table-striped.small').dataTable();
		$(document).on('click', 'a.boxylink', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).data('title')});
				e.handled = true;
			}
		});
	});

	var refreshDrugsBoxy = function () {
		Boxy.get($(".close")).hideAndUnload();
		Boxy.load("/pm/items/categories.php", {
			afterShow: function () {
				$('.table.table-hover.table-striped.small').dataTable();
			}
		});
	}
</script>