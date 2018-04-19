<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/6/17
 * Time: 1:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DRTDAO.php';
$data = (new DRTDAO())->all();
?>
<section>
	<div>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>Service Name</th>
				<th>Description</th>
				<th class="amount">Base Price</th>
				<th>*</th>
			</tr>
			</thead>
			<?php foreach ($data as $drt) {//$drt=new DRT;?>
				<tr>
					<td><?= $drt->getName() ?></td>
					<td><?= $drt->getDescription() ?></td>
					<td class="amount"><?= $drt->getBasePrice() ?></td>
					<td><a href="javascript:" class="edit_drt" data-id="<?= $drt->getId() ?>">Edit</a></td>
				</tr>
			<?php } ?>
		</table>
	</div>
</section>
<script type="text/javascript">
	$(document).on('click', '.edit_drt', function (e) {
		if (!e.handled) {
			Boxy.load('/pm/accounts/drtServiceEdit.php?id=' + $(e.target).data('id'));
			e.handled = true;
		}
	});
</script>
