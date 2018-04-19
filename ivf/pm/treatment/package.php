<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/15/16
 * Time: 4:18 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFPackageDAO.php';
$data = (new IVFPackageDAO())->all();
?>
	<div class="text-right clearfix clear">
		<a href="javascript:" class="action newBtn" data-href="treatment/package.add.php">New IVF Package</a>
	</div>
<?php if (count($data) < 1) { ?>
	<div class="alert-box notice">No IVF Package configured</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Package Name</th>
			<th class="amount">Base Price</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data as $item) {//$item=new IVFPackage()?>
			<tr>
				<td><?= $item->getName() ?></td>
				<td class="price amount"><?= $item->getAmount()?></td>
				<td><a class="edit" href="javascript:;" data-href="/ivf/pm/treatment/package.edit.php?id=<?= $item->getId() ?>">Edit</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>