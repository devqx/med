<div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackagesDAO.php";
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
	
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
	$currency = (new CurrencyDAO())->getDefault();
	$packages = (new AntenatalPackagesDAO())->getPackages();
	$s_centers = (new ServiceCenterDAO())->all('Antenatal');
	?>
	<h5><?= count($packages) ?> Available Packages:</h5>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th class="amount">Amount (<?= $currency ?>)</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($packages as $p) { ?>
			<tr>
				<td>
					<a href="javascript:void(0)" data-href="antenatal/package_items.php?package_id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Antenatal Package Items'})"><?= $p->getName() ?></a>
				</td>
				<td class="amount"><?= $p->getAmount() ?></td>
				<td><i class="icon-edit"></i>
					<a href="javascript:void(0)" data-href="antenatal/boxy.editpackage.php?id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Edit Antenatal Package'})">edit</a>
				</td>
			</tr>
		<?php } ?>
	</table>
	<div class="clear"></div>
	<div class="clear"></div>
	<h6 class="menu-head">Existing Business Unit/Service Center <span class="pull-right"><i class="icon-plus"></i><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/antenatal/boxy.service_centre.add.php')">New Center</a></span></h6>
	<div class="clear"></div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th>Cost Center</th>
			<th>Department</th>
		</tr>
		</thead>
		<?php foreach ($s_centers as $s) { ?>
			<tr>
				<td><?= $s->getName() ?></td>
				<td><?= $s->getCostCentre()->getName() ?></td>
				<td><?= $s->getDepartment()->getName() ?></td>
			</tr>
		<?php } ?>
	</table>
</div>