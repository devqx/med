<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 1:18 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$item = (new OphthalmologyItemDAO())->get($_REQUEST['id']);

if ($_POST) {
	if (is_blank($_POST['testtype'])) {
		exit("error:Item Name is required");
	}
	if (is_blank($_POST['base_price'])) {
		exit("error:Set a base price");
	}
	
	$item->setName($_POST['testtype']);
	$item->setBasePrice(parseNumber($_POST['base_price']));
	$update = (new OphthalmologyItemDAO())->update($item);
	exit(json_encode($update));
} ?>
<div style="width: 500px">
	<div class="loading_place"></div>
	<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return AIM.submit(this, {onStart: updating, onComplete: updatedItem})">
		<input type="hidden" name="id" value="<?= $item->getId() ?>">
		<label>Test Name
			<input type="text" name="testtype" id="testtype" value="<?= $item->getName() ?>"/></label>
		<label>
			Base Price
			<input type="number" name="base_price" step="any" value="<?= $item->getBasePrice() ?>">
		</label>

		<div class="button-block">
			<button class="btn">Save Changes</button>
		</div>
	</form>

</div>

<script type="text/javascript">
	function updating() {
		$('.loading_place').html('<img src="/img/loading.gif"> Please wait...');
	}
	function updatedItem(s) {
		try {
			var data1 = JSON.parse(s);
			if (data1 !== null) {
				$('#ophth_items').load('/pages/pm/ophth-items.php');
				Boxy.info("Changes have been saved", function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			} else {
				Boxy.alert("Nothing was changed", function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			}
		} catch (exception) {
			var data = s.split(":");
			if (data[0] == "error") {
				Boxy.alert(data[1]);
			}
		}
	}

	$(document).ready(function () {
		$.ajax({
			url: '/api/ophth_category.php',
			dataType: 'json',
			success: function (a) {
				categoryData = a;
				$('#testCategory2').select2({
					placeholder: "Select a Category",
					width: '100%',
					formatResult: function (data) {
						return data.name;
					},
					formatSelection: function (data) {
						return data.name;
					},
					data: function () {
						return {results: categoryData, text: 'name'};
					},
					initSelection: function (element, callback) {
						var id = $(element).val();
						if (id !== "") {
							$.ajax("/api/ophth_category.php?id=" + id, {
								dataType: "json"
							}).done(function (data) {
								callback(data);
							});
						}
					}
				});
			}
		});

		$.ajax({
			url: '/api/ophth_template.php',
			dataType: 'json',
			success: function (a) {
				templateData = a;
				$('#labTemplate2').select2({
					placeholder: "Select a Template",
					width: '100%',
					formatResult: function (data) {
						return data.label;
					},
					formatSelection: function (data) {
						return data.label;
					},
					data: function () {
						return {results: templateData, text: 'label'};
					},
					initSelection: function (element, callback) {
						var id = $(element).val();
						if (id !== "") {
							$.ajax("/api/ophth_template.php?id=" + id, {
								dataType: "json"
							}).done(function (data) {
								callback(data);
							});
						}
					}
				});
			}
		});
	})
</script>