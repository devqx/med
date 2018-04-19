<div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PackageDAO.php";
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
	$currency = (new CurrencyDAO())->getDefault();
	$packages = (new PackageDAO())->all();
	?>
	<h5><?= count($packages) ?> Available Packages/Promotional Offers:</h5>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Name</th>
			<th>Category</th>
			<th>Expiration</th>
			<th class="amount">Amount (<?= $currency ?>)</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($packages as $p) { ?>
			<tr>
				<td>
					<?= $p->getName() ?>
				</td>
				<td><?=$p->getCategory()->getName()?></td>
				<td><?= date(MainConfig::$dateFormat, strtotime($p->getExpiration()))?></td>
				<td class="amount"><?= $p->getPrice() ?></td>
				<td><i class="icon-edit"></i>
					<a href="javascript:void(0)" data-href="/pages/pm/packages/boxy.editpackage.php?id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Edit Package'})">Edit</a>
|
					<a href="javascript:void(0)" data-href="/pages/pm/packages/package_items.php?package_id=<?= $p->getId() ?>" onclick="Boxy.load($(this).attr('data-href'),{title:'Package Items'})">Services</a>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>