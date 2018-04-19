<div><?php
	require $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
	$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes(true);
	?>

	<h5><?= count($schemes) ?> available Insurance Schemes</h5>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Scheme</th>
			<th>Type</th>
			<th>Owner</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<?php foreach ($schemes as $s) { //$s=new InsuranceScheme();?>
			<tr>
				<td>
					<a class="boxy" href="javascript:void(0)" data-href="insurance/schemedetails.php?id=<?= $s->getId() ?>" onclick="Boxy.load($(this).attr('data-href') ,{title:'Insurance Program Details: <?= escape($s->getName()) ?>'})"><?= $s->getName() ?></a>
				</td>
				<td><?= ucwords($s->getType()) ?></td>
				<td><?= $s->getInsurer()->getName() ?></td>
				<td class="">
					<a href="javascript:void(0);" data-href="insurance/boxy.editscheme.php?sid=<?= $s->getId() ?>" onClick="Boxy.load($(this).attr('data-href'),{title:'Edit Insurance Scheme: <?= escape($s->getName()) ?>'})">Edit Details</a> |
					<a href="javascript:void(0);" data-href="insurance/boxy.editscheme_items.php?sid=<?= $s->getId() ?>" onClick="Boxy.load($(this).attr('data-href'),{title:'Edit Scheme Items: <?= escape($s->getName()) ?>'})">Edit Items</a> |
					<a class="boxy" href="javascript:void(0)" data-href="insurance/schemedetails.php?id=<?= $s->getId() ?>" onclick="Boxy.load($(this).attr('data-href') ,{title:'Insurance Scheme Items List: <?= escape($s->getName()) ?>'})">Export Covered Items</a>
					<?php if($s->isReference()){?>| <a href="javascript:" data-href="insurance/replicate_scheme.php?id=<?= $s->getId() ?>"  onclick="Boxy.load($(this).attr('data-href') ,{title:'Replicate Covered Items to Another Scheme: <?= escape($s->getName()) ?>'})">Replicate Covered Items</a><?php }?>
				</td>
			</tr>
		<?php } ?>
	</table>
</div>