<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/16
 * Time: 9:51 AM
 */
if(sizeof($data) > 0){?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Date</th>
		<th>Request By</th>
		<th class="amount">Items</th>
		<th>Status</th>
	</tr>
	</thead>
	<?php foreach ($data as $item){//$item = new DrugRequisition();?>
		<tr>
			<td><a href="javascript:;" class="requisitionLink" data-heading="Requisition Details" data-route="/pages/pm/pharmacy/requisitions/details.php?id=<?= $item->getId() ?>" data-id="<?= $item->getId() ?>"><?= date(MainConfig::$dateTimeFormat, strtotime($item->getCreateDate()))?></a></td>
			<td><?= $item->getCreateUser()->getFullname()?></td>
			<td class="amount"><?= $item->getItemsCount()?></td>
			<td><?= $item->getStatus()?></td>
		</tr>
	<?php }?>
</table>
<?php } else {?>
	<div class="alert-box notice">No requisitions available</div>
<?php }?>

<script type="text/javascript">
	$(document).on('click', '.requisitionLink', function (e) {
		if(!e.handled){
			Boxy.load($(e.target).data('route'), {title: $(e.target).data('heading'), afterHide: function () {
				$('.mini-tab > a.tab:first').click();
			}});
			e.handled = true;
		}
	})
</script>
