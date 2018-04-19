<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 4:01 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticLabDAO.php';
$data = (new GeneticLabDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="new/pgd_lab.php">New Genetic Lab</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No Genetic Labs configured</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Genetic Test Name</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) {// $item=new GeneticLab()?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td><a class="edit" href="javascript:;" data-href="/ivf/pm/edit/pgd_lab.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>