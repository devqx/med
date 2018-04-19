<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/16/18
 * Time: 11:38 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFDrugDAO.php';
$data = (new IVFDrugDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="new/new_ivf_drugs.php">New Drug</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No Drug configured</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th> Name</th>
			<th>Generic</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) { ?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td><?= $item->getGeneric() ? $item->getGeneric()->getName() : '' ?></td>
				<td><a class="edit" href="javascript:" data-href="/ivf/pm/edit/edit_drug.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>