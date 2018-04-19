<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/30/16
 * Time: 12:01 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugRequisition.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugRequisitionLine.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugRequisitionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugRequisitionLineDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';

$stores = (new ServiceCenterDAO())->all('Pharmacy');

$requisition = (new DrugRequisitionDAO())->get($_GET['id']);

//todo disable some inputs and buttons based on the status of this record to prevent the user from thinking that
//we are still making changes
//what about cancelling?

?>
<section style="width:1000px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
		<div class="row-fluid">
			<?php if ($requisition->getStatus() == "Draft") { ?>
				<button class="btn span2" type="button" data-action="Validate" data-id="<?= $requisition->getId() ?>">Validate</button><?php } ?>
			<?php if ($requisition->getStatus() == "Validated") { ?>
				<button class="btn span2" type="button" data-action="Approve" data-id="<?= $requisition->getId() ?>">Approve</button><?php } ?>

			<?php if ($requisition->getStatus() == "Approved") { ?>
				<button class="btn span2" type="button" data-action="Receive" data-id="<?= $requisition->getId() ?>">Receive
					into stock
				</button>
				<select class="span10" style="width: default;" name="pharmacy_id" title="Store to receive into" required>
					<?php foreach ($stores as $store) { ?>
						<option value="<?= $store->getId() ?>"><?= $store->getName() ?></option>
					<?php } ?>
				</select>

			<?php } ?>
			<?php if ($requisition->getStatus() == "Received") { ?>

			<?php } ?>
		</div>
		<div class="row-fluid">
			<label class="span8">Request By <select disabled>
					<option><?= $requisition->getCreateUser()->getFullname() ?></option>
				</select></label>
			<label class="span4">Request
				Date<input disabled type="text" name="create_date" value="<?= date(MainConfig::$dateTimeFormat) ?>"> </label>
		</div>

		<label class="menu-head">Request Items
			<span class="pull-right"><a class="btn btn-mini add_request_line_">Add Item</a> </span> </label>
		<?php foreach ($requisition->getItems() as $item) {//$item = new DrugRequisitionLine()?>
			<div class="row-fluid request_items_">
				<label class="span3">Drug<input type="text" name="drug_id[]" required value="<?= $item->getDrug()->getName() ?>" readonly>
				</label>
				<label class="span2">Item Code
					<span class="fadedText">(if available)</span><input readonly type="text" name="item_code[]" required value="<?= $item->getItemCode() ?>">
				</label>
				<label class="span2">Request
					Quantity<input type="number" name="quantity[]" required value="<?= $item->getQuantity() ?>"> </label>
				<label class="span2">Name this
					batch<input type="text" name="batch_name[]" required value="<?= $item->getBatchName() ?>"> </label>
				<label class="span2">Batch expiration date
					<input type="text" name="expiration_date[]" required value="<?= $item->getExpiration() ?>"> </label>
				<label class="span1" style="margin-top:25px"><a class="btn remove_request_line_">&minus;</a> </label>
			</div>
		<?php } ?>

		<button class="btn" type="submit" disabled>Update</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>

	</form>
</section>
<script type="text/javascript">
	$(document).on('click', 'button.btn[type="button"][data-action]', function (e) {
		if (!e.handled) {
			Boxy.ask("Are you sure ?", [$(e.target).data("action"), "Cancel"], function (answer) {
				if (answer != "Cancel") {
					$.post("/api/change_requisition.php", {
						id: $(e.target).data("id"),
						action: $(e.target).data("action"),
						service_centre_id: $('[name="pharmacy_id"]').val()
					}, function (rep) {
						Boxy.get($(".close")).hideAndUnload(function () {
							$('.requisitionLink[data-id="' + $(e.target).data("id") + '"]').get(0).click();
						});
					});


				} else {
					//cancel. do nothing.
				}
			});
			e.handled = true;
		}
	})
</script>
