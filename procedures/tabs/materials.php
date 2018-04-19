<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);

$items = (new PatientItemRequestDAO())->getItemsForProcedure($_GET['id']);

?>

<?php if (in_array($p->getStatus(), ["started"]) && (@$_GET['add'] != "false")) { ?>
	<div class="menu-head">
		<a href="javascript:;" onclick="addMaterial(<?= $_GET['id'] ?>)">Add Material</a>
	</div>
<?php } ?>

<p></p>
<?php if (count($items) == 0) { ?>
	<div class="notify-bar" >No materials</div>
<?php } else { ?>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th>Date</th>
			<th>Item</th>
			<th>Note</th>
			<th>Quantity</th>
			<th>By</th>
			<th>Status</th>
			<th>*</th>
		</tr>
		</thead>

		<tbody>
		<?php foreach ($items as $it) { ?>
			<?php foreach ($it->getData() as $d) { ?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($it->getRequestDate())) ?> </td>
					<td><?= $d->getItem() ? $d->getItem()->getName() : '----' ?> </td>
					<td><?= $it->getRequestNote() ?> </td>
					<td><?= $d->getQuantity() ?> </td>
					<td><?= $it->getRequestedBy() ?></td>
					<td><?= $d->getStatus() ?></td>
					<td>
						<?php if($d->getStatus()  && $d->getStatus() !== 'cancelled' && !in_array($p->getStatus(), ['cancelled'])) { ?>
							<div class="dropdown pull-right">
								<button class="drop-btn large dropdown-toggle" data-toggle="dropdown" style="padding:10px">
									Action
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu" role="menu" aria-labelledby="dLabel_">
									<?php if ($d->getStatus() !== 'cancelled' && !in_array($p->getStatus(), ['cancelled']) ) { ?>
										<li>
											<a href="javascript:" onclick="cancelRequestItem(<?=$d->getId() ?>)"
										      title="Cancel this Request">Cancel</a></li>
									<?php } ?>
								</ul>
							</div>
						<?php } ?>
					</td>
				</tr>
			<?php } ?>
		<?php } ?>
		</tbody>
	</table>
<?php } ?>

<script type="text/javascript">
	function addMaterial(key) {
		Boxy.load('/procedures/dialogs/new-used-item.php?id=' + key, {
			afterHide: function () {
				// refresh this tab?
				$("#tab-container").easytabs('select', '#materials');
			}
		});
	}



	function cancelRequestItem(id) {
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
							data: {action: 'cancel', id: id, reason: value},
							type: 'POST',
							complete: function (xhr, status) {
								if (status === "success" && xhr.responseText === "true") {
									 $(function () {
										// refresh this tab?
										$("#tab-container").easytabs('select', '#materials');
									});
								}
							}
						});

					} else {
						Boxy.alert("Please enter the reason for cancellation");
					}
				}
			});
	}
</script>