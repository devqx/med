<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/23/16
 * Time: 2:23 PM
 */

$drugGenerics = [];
$activeGenericsOnly = true;
$_GET['suppress'] = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SuperGenericDAO.php';
$existingSuperGeneric = (new SuperGenericDAO())->get($_GET['id']);
$super_drugGenerics = [];
foreach ($existingSuperGeneric->getData() as $item) {
	$super_drugGenerics[] = $item->getId();
}
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SuperGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugSuperGenericData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugSuperGenericDataDAO.php';
	
	if(is_blank($_POST['name'])){exit('error:An identifier for the super generic is required');}

	$existing = (new SuperGenericDAO())->get($_POST['id']);

	$generic_ids = array_filter(explode(",",$_POST['generic_ids']));
	$initial_generics = array_filter(explode(",",$_POST['initial_generics']));
	
	$removed = array_values(array_diff($initial_generics, $generic_ids));
	foreach($removed as $item){
		(new DrugSuperGenericDataDAO())->findWithGeneric($item, $_POST['id'])->delete();
	}
	
	//exit('error:You have removed '.count($removed). ' items');
	if(count($generic_ids)==0){exit('error:At least a generic is required for the super generic');}
	$data = [];
	foreach ($generic_ids as $id){
		$data[] = (new DrugSuperGenericData())->setDrugGeneric(new DrugGeneric($id));
	}
	$super_gen = $existing->setName($_POST['name'])->setData($data)->update();

	if($super_gen){
		exit('success:Super generic updated');
	}
	exit('error:Failed to update Drug Super Generic');
}
?>
<section style="width:500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _09023_, onComplete: __20453_})">
		<label>
			Name <input type="text" name="name" value="<?= $existingSuperGeneric->getName()?>">
		</label>
		<label>Generics <input type="hidden" name="generic_ids" value="<?= implode(",",$super_drugGenerics) ?>"> </label>
		<input type="hidden" name="id" value="<?= $_GET['id']?>">
		<input type="hidden" name="initial_generics" value="<?= implode(",",$super_drugGenerics) ?>">
		<p style="margin-bottom: 20px"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Update</button>
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