<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/1/16
 * Time: 3:13 PM
 */
$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Formulary.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/FormularyData.php';
	
	if(is_blank($_POST['name'])){exit('error:An identifier for the formulary is required');}
	
	$generic_ids = array_filter(explode(",",$_POST['generic_ids']));
	if(count($generic_ids)==0){exit('error:At least a generic is required for the formaulary');}
	$data = [];
	foreach ($generic_ids as $id){
		$data[] = (new FormularyData())->setGeneric(new DrugGeneric($id));
	}
	$formulary = (new Formulary())->setName($_POST['name'])->setData($data)->add();
	
	if($formulary){
		exit('success:Formulary saved');
	}
	exit('error:Failed to save formulary');
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _09023, onComplete: __20453})">
		<label>
			Name <input type="text" name="name">
		</label>
		<label>Generics <input type="hidden" name="generic_ids"> </label>
		<p style="margin-bottom: 20px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics) ?>;
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
			refreshThisPage();
		} else if (s1[0] === "error") {
			Boxy.alert(s1[1]);
		}
	};
	
</script>
