<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/5/16
 * Time: 9:39 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/InseminationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/FertilizationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFMethodDAO.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/profile/tabs/embryology/zygotes.inc.php';

$data = (new InseminationDAO())->forInstance($_GET['aid']);
$methods = (new IVFMethodDAO())->all();

$data2 = (new FertilizationDAO())->forInstance($_GET['aid']);
//$data2 = $data2[count($data2)-1];
$ff = [];
foreach ($data2 as $fD){
	//$fD = new Fertilization();
	$rec['method_id'] = $fD->getMethod()->getId();
	$rec['zygote'] = $fD->getZygoteType();
	$rec['cell'] = $fD->getCellNo();
	//$rec['comment'] = $fD->getComment();
	$ff[] = (object)$rec;
}

$fertilized = $unfertilized = [];
foreach($zygotes as $zygote)
{
	$zygote = (object)$zygote;
	if($zygote->fertilized){
		$fertilized[] = $zygote;
	} else {
		$unfertilized[] = $zygote;
	}
}
?>

<div class="paper-card">
	<div class="heading">Insemination/Injection <a style="font-size:14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/insemination-new.php?aid=<?= $_GET['aid']?>" onclick="Boxy.load($(this).data('href'))">New Record</a></div>
	<div class="card-content">
		<?php if(count($data) == 0){?>
		No data available to display
		<?php } else {?>
			<table class="table table-striped table-bordered no-border">
				<tr>
					<th>*</th>
					<?php foreach ($methods as $method) { ?>
						<th colspan="3"><?= $method->getName()?></th>
					<?php } ?>
					<th>-</th>
				</tr>
				<tr>
					<th>*</th>
					<?php foreach ($methods as $method) { ?>
						<td># of Eggs</td>
						<td>Source</td>
						<td># Sperm</td>
					<?php } ?>
					<td>Witness</td>
				</tr>
				<?php foreach ($data as $item) {//$item=new Insemination();?>
					<tr>
						<td>*</td>
						<?php foreach ($methods as $method) { ?>
							<td><?= $method->getId()== $item ->getMethod()->getId() ? $item->getTotalEggs() : '- -'?></td>
							<td><?= $method->getId()== $item ->getMethod()->getId() ? $item->getSource()->getName() : '- -'?></td>
							<td><?= $method->getId()== $item ->getMethod()->getId() ?  $item->getTotalSperm() : '- -'?></td>
						<?php } ?>
						<td>
							<?php foreach ($item->getWitnesses() as $witness){?>
								<?= $witness->getFullname()?>
							<?php }?>
						</td>
					</tr>
				<?php }?>

				<?php foreach ($data as $item) {//$item=new Insemination();?>
					<tr>
						<td>Comment</td>

						<td colspan="<?= count($methods) * 3 + 1 ?>">
							<?= $item->getComment()?>
						</td>
					</tr>
				<?php }?>
			</table>
		<?php }?>
	</div>
</div>

<div class="paper-card">
	<div class="heading">Day 1 - Fertilization <a style="font-size:14px" class="pull-right" href="javascript:" data-href="/ivf/profile/tabs/embryology/fertilization-new.php?aid=<?= $_GET['aid']?>" onclick="Boxy.load($(this).data('href'))">New Record</a></div>
	<div class="card-content">
		<?php if(count($data) == 0){?>
			No data available to display
		<?php } else {?>
			<table class="table table-bordered table-striped">
				<tr><td></td> <td colspan="<?=count($fertilized)?>">Fertilized</td><td colspan="<?=count($unfertilized) ?>">Non-Fertilized</td><td>--</td></tr>
				<tr><td>-</td>
						<?php foreach ($fertilized as $item){?><td><?= $item->name?></td><?php }?>
						<?php foreach ($unfertilized as $item){?><td><?= $item->name?></td><?php }?>
						<td>Comments</td>
					</tr>
				<?php foreach ($methods as $method){?>
					<tr><td><?= $method->getName()?></td>
						<?php foreach ($fertilized as $item){?>
							<td>
							<?php foreach ($ff as $f){?>
								<?= ($f->zygote==$item->name && $f->method_id == $method->getId() ? $f->cell : '') ?>
							<?php }?>
							</td>
						<?php }?>
						<?php foreach ($unfertilized as $item){?><td>
							<?php foreach ($ff as $f){?>
								<?= ($f->zygote==$item->name && $f->method_id == $method->getId() ? $f->cell : '') ?>
							<?php }?>
						</td><?php }?>
						<td></td>
					</tr>

				<?php }?>
			</table>
		<?php }?>
	</div>
</div>

