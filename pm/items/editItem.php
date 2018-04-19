<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$gens = (new ItemGenericDAO())->list_();
if ($_POST) {
	$it = (new ItemDAO())->getItem($_POST['iid']);
	
	if (!is_blank($_POST['name'])) {
		$it->setName($_POST['name']);
	} else {
		exit("error:Name is required");
	}
	if (!is_blank($_POST['generic_id'])) {
		$it->setGeneric((new ItemGenericDAO())->get($_POST['generic_id']));
	}
	
	if (is_blank($_POST['base_price'])) {
		exit("error: Base Price is required");
	} else {
		$it->setBasePrice(parseNumber($_POST['base_price']));
	}
	
	if (is_blank($_POST['description'])) {
		exit("error: Description is required");
	} else {
		$it->setDescription($_POST['description']);
	}
	if (!is_blank($_POST['erp_product_id'])) {
		$it->setErpProductId($_POST['erp_product_id']);
	}
	$newItem = (new ItemDAO())->updateItem($it);
	
	if ($newItem !== null) {
		exit("ok:Item " . $newItem->getName() . " updated successfully!");
	} else {
		exit("error:Failed to Update Item");
	}
} else {
	$item = (new ItemDAO())->getItem($_GET['id']);
}
$_GET['suppress'] = true;

?>
<section>
	<div>
		<form method="post" name="editItemForm" action="<?= $_SERVER['REQUEST_URI'] ?>">
			<label>Name
				<input type="text" name="name" value="<?= $item->getName() ?>">
			</label>

			<label>Generic
				<select id="generic_id" name="generic_id" data-placeholder="select item generic">
					<option></option>
					<?php foreach ($gens as $gen) { ?>
						<option value="<?= $gen->getId() ?>"<?= $item->getGeneric() && $item->getGeneric()->getId() == $gen->getId() ? 'selected="selected"' : '' ?>><?= $gen->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			<label>Description
				<input type="text" name="description" value="<?= $item->getDescription() ?>">
			</label>
			<label>Base Price
				<input name="base_price" type="number" value="<?= $item->getBasePrice() ?>" min="0"></label>
			<label>ERP Product Id
				<input type="text" name="erp_product_id" value="<?= $item->getErpProductId() ?>">
			</label>
			<div class="btn-block" style="margin-top: 10px;">
				<input type="hidden" name="iid" value="<?= $item->getId() ?>">
				<input type="hidden" name="code" value="<?= $item->getCode() ?>">
				<button type="button" class="btn" name="item">
					Save
				</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
					Close
				</button>
			</div>
		</form>
	</div>
</section>
<script type="text/javascript">

	$(document).ready(function () {

		$('button[name="item"]').click(function () {
			$.ajax({
				url: "<?= $_SERVER['REQUEST_URI'] ?>",
				type: "post",
				data: $("form[name='editItemForm']").serialize(),
				success: function (d) {
					if (d.split(":")[0] === "ok") {
						Boxy.info("Item updated successfully", function () {
							Boxy.get($(".close")).hideAndUnload();
						});
					} else {
						Boxy.warn(d.split(":")[1]);
					}
				},
				error: function (d) {
					Boxy.alert("Sorry action failed");
				}
			});
		});

	});

</script>