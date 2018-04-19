<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
$blocks = (new WardDAO())->getWards(true);

if (sizeof($blocks) > 0) { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>SN</th>
			<th>Name</th>
			<th>Cost Centre</th>
			<th>Block</th>
			<th>Base Price</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($blocks as $key => $b) { ?>
			<tr>
				<td><?= $key + 1 ?></td>
				<td><?= $b->getName() ?></td>
				<td><?= $b->getCostCentre()->getName() ?></td>
				<td><?= $b->getBlock()->getName() ?></td>
				<td class="amount"><?= $b->getBasePrice() ?></td>
				<td>
					<a class="__phL" href="javascript:;" data-href="/pages/pm/bedspaces/editWard.php?id=<?= $b->getId() ?>">Edit</a>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<?php
} else {
	echo '<div class="well">No Ward currently exists</div>';
}
?>
<script type="text/javascript">
	$('a.__phL').live('click', function (e) {
		if (!e.handled) {
			Boxy.load($(this).data("href"));
			e.handled = true;
		}
	})
</script>
