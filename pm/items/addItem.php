<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 1/9/17
 * Time: 11:41 AM
 */
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';

	$it = new Item();
	if (!empty($_POST['item_name'])) {
		$it->setName($_POST['item_name']);
	} else {
		exit("error:Name is required");
	}

	if (is_blank($_POST['base_price'])) {
		exit("error: Base Price is required");
	} else {
		$it->setBasePrice(parseNumber($_POST['base_price']));
	}
	if(!is_blank($_POST['generic_id'])){
	    $it->setGeneric((new ItemGenericDAO())->get($_POST['generic_id']));
    }
	
	if(!is_blank($_POST['erp_product_id'])){
		$it->setErpProductId($_POST['erp_product_id']);
	}

	if(!is_blank($_POST['description'])){
	    $it->setDescription($_POST['description']);
    }

	$newItem = (new ItemDAO())->addItem($it);
	if ($newItem !== null) {

        exit("ok:Item added successfully!");
	} else {
		exit("error:Failed to add Item");
	}
}

//$_GET['suppress'] = true;

include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_item_generic.php';
?>
<div id="additem" style="width:600px"><span class="error"></span>
	<form  action="<?= $_SERVER['REQUEST_URI'] ?>" name="newItemForm" method="post">
		<label>Item Name<span class="required-text">*</span>
			<input type="text" name="item_name" required="required"></label>
        <label>Item Generic
            <span class="pull-right"><a href="javascript:;" id="addGenLink">add Generic</a></span>
            <input type="hidden" name="generic_id" id="generic_id"></label>
		<label>Base Price
			<input name="base_price" type="number" value="0" min="0">
		</label>
		<label>ERP Product Id
			<input type="text" name="erp_product_id">
		</label>
		<label>Description <textarea name="description"></textarea> </label>

		<div class="btn-block" style="margin-top: 10px;">
			<button type="button" class="btn" name="item" id="saveItem">Save</button>
			<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>
	</form>
</div>

<script>
	var itemGen = <?= json_encode($generics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	$(document).ready(function () {

		$("#saveItem").click(function () {
			$.ajax({
				url: "<?= $_SERVER['REQUEST_URI'] ?>",
				type: "post",
				data: $("form[name='newItemForm']").serialize(),
				success: function (d) {
					if (d.split(":")[0] === "ok") {
                        Boxy.info("Item Saved Successfully", function () {
                            Boxy.get($(".close")).hideAndUnload();
                        });

                    } else {
                            Boxy.alert("Unable to save Item");
					}
				},
			});
		});

		$('.service_center').select2({
			placeholder: 'Select service center',
			allowClear: true,
			width: '100%',
		});

		$('.table.table-hover.table-striped.small').dataTable();
		$(document).on('click', 'a.boxylink', function (e) {
			if (!e.handled) {
				Boxy.load($(this).data('href'), {title: $(this).attr('title') || $(this).data('title')});
				e.handled = true;
			}
		});



		$('#addGenLink').on('click', function () {
			Boxy.load('/pm/items/add_item_generic.php', {
				afterHide: function () {
					$.ajax({
						url : "/api/get_item_generic.php",
						type: "POST",
						dataType: "json",
						success: function(c){
                            itemGen = c;
                            refreshIGen();
						}
					});
				}
			});
		});

	});


	function refreshIGen() {
		$("#generic_id").select2("destroy");
		setTimeout(function () {
			$("#generic_id").select2({
				width: '100%',
				allowClear: true,
				placeholder: 'Select Item Generic',
				data: {results: itemGen, text: 'name'},
				formatResult: function (source) {
					return source.name;
				},
				formatSelection: function (source) {
					return source.name;
				}
			});
		}, 50);

	}
    refreshIGen();
</script>
