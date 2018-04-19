<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 7:44 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/EggCollectionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/FollicleSizeDAO.php';
$data = (new EggCollectionDAO())->forInstance($_GET['aid']);
$follicleSizes = (new FollicleSizeDAO())->all();
?>
<div class="menu-head"></div>
<div class="paper-card">
	<div class="heading">Egg Collection <a style="font-size: 14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/egg-new.php?aid=<?= $_GET['aid'] ?>" onclick="Boxy.load($(this).data('href'))">New Record</a></div>
	<div class="card-content">
		<?php if(count($data) == 0){?>
		No data available to display
		<?php } else {?>
			<?php foreach ($data as $item){//$item=new EggCollection();?>
				<div class="row-fluid">
					<span class="span4"><strong>Set Up By:</strong><br> <?= $item->getUser()->getFullname() ?></span>
					<span class="span4"><strong>Method:</strong><br> <?= $item->getMethod()->getName()?></span>
					<span class="span4"><strong>Procedure By:</strong><br> <?= $item->getDoneBy()->getFullName()?></span>
				</div>

				<table class="table table-bordered table-striped">
					<thead>
					<tr><th>*</th><th>Total</th></tr>
					</thead>
					<tr><th width="25%">Left Ovary total</th><td><?= $item->getTotalLeft()?></td></tr>
					<tr><th>Right Ovary total</th><td><?= $item->getTotalRight()?></td></tr>
					<tr><th>Total</th><td><?= $item->getTotalLeft() + $item->getTotalRight()?></td></tr>
					<tr><th>Comments</th><td><?= $item->getComment()?></td></tr>
				</table>
				<table class="table table-striped">
					<tr>
						<?php foreach ($item->getData() as $size) {//$size=new EggCollectionFollicleData(); ?>
							<td><?= $size->getSize()->getName() ?>:</td><td class="amount fadedText" style="border-right:1px solid #ccc"><code><?= $size->getValue() ?></code></td>
						<?php } ?>
					</tr>
				</table>
				<table class="table table-bordered table-striped">
					<thead>
					<tr><th>Witnesses:</th></tr>
					</thead>
				<?php if(count($item->getWitnesses())>0){ foreach ($item->getWitnesses() as $witness){?>
					<tr><td><?= $witness->getFullname() ?></td></tr>
				<?php }?>
				<?php } else {?>
					<tr><td>No witness available</td></tr>
				<?php }?>
				</table>
				<hr class="border">
			<?php }?>
		<?php }?>
	</div>
</div>
