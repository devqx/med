<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/22/17
 * Time: 9:39 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientItemRequestDataDAO.php";

$pps = (new PatientItemRequestDataDAO())->getByCode($_GET['rCode'], TRUE);

?>
<div class="display" style="width: 960px;">
	<h3>Request ID: <?= $_GET['rCode'] ?></h3>
	<h5 class="fadedText">Notes: <?= urldecode($_GET['note']) ?></h5>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th>*</th>
			<th>Item</th>
			<th>Quantity</th>
			<th>Status</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($pps as $k => $d) {  ?>
			<tr>
				<td><a href="javascript:;"  class="btn btn-small1 disabled">Print</a></td>
				<td><?= ($d->getItem() !== null) ? $d->getItem()->getName() : "" ?></td>
				<td><?= $d->getFilledQuantity() ?></td>
				<td><?= $d->getStatus() ?></td>
				<td><a data-id="<?= $d->getId() ?>" href="javascript:; "class="p_action btn btn-small1" data-action="NO" <?=  $d->getStatus() !=='open' ? ' disabled':''?>>cancel</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('.p_action').click(function (e) {
			var action = $(this).data("action");
			var item_id = $(this).data("id");
			if (action === "NO") {
				cancelPrescription(item_id);
			}
			e.preventDefault();
		});
	});


	function cancelPrescription(id) {
		if (confirm("Are you sure you want to cancel this request?")) {
			vex.dialog.prompt({
				message: 'Please enter your reason for cancellation',
				placeholder: 'Request Cancellation note',
				value: null,
				overlayClosesOnClick: false,
				beforeClose: function (e) {
					e.preventDefault();
				},
				callback: function (value) {
					if (value !== false && value !== '') {
						$.ajax({
							url: '/api/item.php',
							data: {action:'cancel',id:id,reason:value},
							type: 'POST',
							complete: function (xhr, status) {
								if (status === "success" && xhr.responseText === "true") {
									$('[data-id="' + id + '"]').prev().html("cancelled");
									$('[data-id="' + id + '"]').parents('tr').find('td input').prop('disabled', true);
									$('[data-id="' + id + '"]').parents('tr').find('td select').prop('disabled', true);
									$('[data-id="' + id + '"]').remove();
								}
							}
						});

					} else {

					}
				}
			});
		}
	}


</script>