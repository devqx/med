<?php

$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';


if ($_POST) {
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugSuperGenericData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

	if(is_blank($_POST['super_generic'])){exit('error: drug super generic is required');}

	$generic_ids = array_filter(explode(",",$_POST['drug_generics']));
	if(count($generic_ids)==0){exit('error:At least a generic is required for the super generic');}
	$data = [];

	foreach ($generic_ids as $id){
		$data[] = (new DrugSuperGenericData())->setDrugGeneric(new DrugGeneric($id));
	}

	$super_generic_drug = (new SuperGeneric())->setName($_POST['super_generic'])->setData($data)->add();

	if($super_generic_drug){
		exit('success:super generic saved');
	}
	exit('error:Failed to save super generic');

}
?>
<div style="width: 400px;">
	<span class="error"></span>
	<form id="formGeneric" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : _09023, onComplete: __20453})">
		<label>Super Generic Name <input type="text" name="super_generic" id="super_generic"></label>
		<label><input name="drug_generics"  type="hidden" id="drug_generics"></label>
		<div>
			<button class="btn" type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics) ?>;
	$('input[name="drug_generics"]').select2({
		width: '100%',
		allowClear: true,
		multiple: true,
		placeholder: "select drug generic",
		minimumInputLength: 3,
		data: {results: drugGens, text: 'name'},
		formatResult: function (source) {
			return source.name + " (" + source.form + ") " + source.weight;
		},
		formatSelection: function (source) {
			return source.name + " (" + source.form + ") " + source.weight;
		}
	});

	var elements = $('.boxy-inner');
	var element = $(elements[elements.length - 1]);
	var _09023 = function () {
		//$(element).trigger('ajaxStart');
		$(element).block({
			message: '<div class="ball"></div>',
			css: {
				borderWidth: '0',
				backgroundColor: 'transparent'
			}
		});
	};

	var __20453 = function (s) {
		//$(element).trigger('ajaxStop');
		$(element).unblock();
		var s1 = s.split(":");
		if (s1[0] === "success") {
			Boxy.get($(".close")).hideAndUnload();
		} else if (s1[0] === "error") {
			Boxy.alert(s1[1]);
		}
	};
</script>
