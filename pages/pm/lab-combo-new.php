<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/7/15
 * Time: 2:56 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Lab.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabCombo.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabComboData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabComboDAO.php';
	
	$lab_ids = array_filter(explode(",", $_POST['lab_ids']));
	
	if (is_blank($_POST['name'])) {
		exit("error:Combo Name is blank");
	}
	if (count($lab_ids) <= 1) {
		exit("error:Combo requires more than 1 lab");
	}
	
	$labCombo = new LabCombo();
	$labComboData = [];
	foreach ($lab_ids as $l) {
		$labComboData[] = (new LabComboData())->setLab(new Lab($l));
	}
	$labCombo->setName($_POST['name'])->setCombos($labComboData);
	
	if ((new LabComboDAO())->add($labCombo) !== null) {
		exit("ok:Success");
	}
	exit("error:Failed to save combo");
	
	
}
?>
<div style="width: 500px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: null, onComplete: cf0})">
		<label>Combo Name: <input type="text" name="name"> </label>
		<label>Constituent Labs <input type="hidden" name="lab_ids"> </label>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('.boxy-content input[name="lab_ids"]').select2({
			placeholder: "Search and select labs",
			minimumInputLength: 3,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_labs.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						search: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			}
		});
	});
	var cf0 = function (data) {
		ret = data.split(":");
		if (ret[0] === "error") {
			Boxy.warn(ret[1]);
		} else if (ret[0] === "ok") {
			Boxy.info(ret[1], function () {
				reloadLabCombos();
			});
			Boxy.get($(".close")).hideAndUnload();
		}
	};
</script>
