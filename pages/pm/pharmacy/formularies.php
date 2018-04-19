<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 2:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormularyDAO.php';
$data = (new FormularyDAO())->all();
?>
<section style="width:500px">
	<div class="pull-right clear clearBoth">
		<button class="action" type="button" onclick="refreshThisPage()">Refresh</button>
		<button class="action" type="button" onclick="Boxy.load('/pages/pm/pharmacy/formulary-new.php')">Add</button>
	</div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th># of Generics</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item){//$item = new Formulary();?>
		<tr>
			<td><?= $item->getName()?></td>
			<td><?= count($item->getData())?></td>
			<td><a href="javascript:" onclick="Boxy.load('/pages/pm/pharmacy/formulary-edit.php?id=<?= $item->getId()?>')" data-id="<?= $item->getId()?>">Edit</a></td>
		</tr>
		<?php }?>
		</tbody>
	</table>
</section>
<script type="text/javascript">
	var refreshThisPage = function () {
		$.get('<?= $_SERVER['REQUEST_URI']?>', function (response) {
			$('table').html($(response).find('table').html());
		})
	}
</script>
