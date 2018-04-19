<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 5:09 PM
 */
$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FormularyDAO.php';
$existingFormulary = (new FormularyDAO())->get($_GET['id']);
$formularyGenerics = [];
foreach ($existingFormulary->getData() as $item) {
	$formularyGenerics[] = $item->getGeneric()->getId();
}
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Formulary.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FormularyData.php';
	if(is_blank($_POST['name'])){exit('error:An identifier for the formulary is required');}
	
	$existing = (new FormularyDAO())->get($_POST['id']);
	
	$generic_ids = array_filter(explode(",",$_POST['generic_ids']));
	if(count($generic_ids)==0){exit('error:At least a generic is required for the formaulary');}
	$data = [];
	foreach ($generic_ids as $id){
		$data[] = (new FormularyData())->setGeneric(new DrugGeneric($id));
	}
	$formulary = $existing->setName($_POST['name'])->setData($data)->update();
	
	if($formulary){
		exit('success:Formulary updated');
	}
	exit('error:Failed to update formulary');
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _09023_, onComplete: __20453_})">
		<label>
			Name <input type="text" name="name" value="<?= $existingFormulary->getName()?>">
		</label>
		<label>Generics <input type="hidden" name="generic_ids" value="<?= implode(",",$formularyGenerics) ?>"> </label>
		<input type="hidden" name="id" value="<?= $_GET['id']?>">
		<p style="margin-bottom: 20px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics, 512) ?>;
	$('input[name="generic_ids"]').select2({
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
	var _09023_ = function () {
		//$(element).trigger('ajaxStart');
		$(element).block({
			message: '<div class="ball"></div>',
			css: {
				borderWidth: '0',
				backgroundColor: 'transparent'
			}
		});
	};

	var __20453_ = function (s) {
		//$(element).trigger('ajaxStop');
		$(element).unblock();
		var s1 = s.split(":");
		if (s1[0] === "success") {
			Boxy.get($(".close")).hideAndUnload();
			refreshThisPage();
		} else if (s1[0] === "error") {
			Boxy.alert(s1[1]);
		}
	};

</script>
