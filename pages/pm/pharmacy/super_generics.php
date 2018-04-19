<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/23/16
 * Time: 2:14 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
$data = (new SuperGenericDAO())->getAll();
?>
<div style="width:1000px">
	<div class="pull-right clear clearBoth">
		<button class="action" type="button" onclick="refreshThisPage()">Refresh</button>
		<button class="action" type="button" onclick="Boxy.load('/pages/pm/pharmacy/drug-super-generic-add.php')">Add</button>
	</div>
	<table class="table table-hover table-striped small">
		<thead>
		<tr>
			<th>Name</th>
			<th># of Generics</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item){?>
			<tr>
				<td><?= $item->getName()?></td>
				<td><?= count($item->getData())?></td>
				<td><a href="javascript:" onclick="Boxy.load('/pages/pm/pharmacy/super_generic_edit.php?id=<?= $item->getId()?>')" data-id="<?= $item->getId()?>">Edit</a></td>
			</tr>
		<?php }?>
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
	var refreshThisPage = function () {
        Boxy.get($(".close")).hideAndUnload();
        Boxy.load("/pages/pm/pharmacy/super_generics.php", {
            afterShow: function () {
                $('.table.table-hover.table-striped.small').dataTable();
            }
        });

	}
</script>
