<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/30/16
 * Time: 6:47 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
$departments = (new DepartmentDAO())->getDepartments();
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Name</th>
		<th>Cost Centre</th>
		<th>*</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($departments as $d) {//$d=new Department();?>
		<tr>
		<td><?= $d->getName() ?></td>
		<td><?= $d->getCostCentre()->getName() ?></td>
		<td><a class="editDept" data-title="Editing Department: <?= $d->getName() ?>" data-href="/pages/pm/department.edit.php?id=<?= $d->getId() ?>" href="javascript:">Edit</a></td>
		</tr><?php } ?>
	</tbody>
</table>
<script>
	$(document).ready(function () {
		$('table.table').dataTable();
	}).on('click', '.editDept', function (e) {
		if (!e.handled) {
			Boxy.load($(this).data("href"), {
				title: $(this).data("title"), afterHide: function () {
					loadDepartments();
				}
			});
			e.handled = true;
		}
	});

</script>
