<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 4:14 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/GeneticTemplateDAO.php';
$data = (new GeneticTemplateDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="new/gen_template.php">New Template</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No Genetic Lab Templates configured</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Template Name</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) {// $item=new GeneticLab()?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td><a class="edit" href="javascript:" data-href="/ivf/pm/edit/gen_template.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>