<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/16
 * Time: 4:16 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/QualityControlTypeDAO.php';
$data = (new QualityControlTypeDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="new/qc_item.php">New Parameter</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No Quality Control Parameters specified</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Q.C. Type/Description</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) {// $item=new QualityControlType()?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td><a class="edit" href="javascript:;" data-href="/ivf/pm/edit/qc_item.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>