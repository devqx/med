<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/28/14
 * Time: 4:48 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
$methods = (new PaymentMethodDAO())->all(TRUE);

?>
<h5><?= count($methods) ?> Payment methods</h5>

<table class="table table-striped">
	<thead>
	<tr>
		<th>Name</th>
		<th>Type</th>
		<th>Ledger Id</th>
		<th class=""></th>
	</tr>
	</thead>
	<?php foreach ($methods as $i => $m) {//$m=new PaymentMethod();?>
		<tr>
			<td style="width: 30%"><?= $m->getName() ?></td>
			<td style="width: 30%"><?= ucfirst($m->getType()) ?></td>
			<td style=""><?= $m->getLedgerId() ?></td>
			<td class=""><a href="javascript:;" data-action="edit" data-id="<?= $m->getId() ?>">Edit</a> |
				<a href="javascript:;" data-action="remove" data-id="<?= $m->getId() ?>">Delete</a></td>
		</tr>
	<?php } ?>
</table>

<script type="text/javascript">
	//    $(document).ready(function () {
	$('a[data-action]').live('click', function (e) {
		if (e.handled != true) {
			if ($(this).data("action") == "edit") {
				Boxy.load("/pm/accounts/edit_payment_method.php?id=" + $(this).data("id"));
			} else {
				//TODO: check in the bills, if this scheme has been used
				Boxy.alert("The payment method might have already been used.");
			}
			e.handled = true;
		}
	});
	//    })
</script>