<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/29/14
 * Time: 4:36 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$forms = MainConfig::$drug_presentations;
$drug = (new DrugDAO())->getDrug($_GET['id']);
$mans = (new DrugManufacturerDAO())->getManufacturers();
$gens = (new DrugGenericDAO())->getGenerics();

if ($_POST) {
	if (is_blank($_POST['drugname'])) {
		exit("error:Drug name is required");
	} else {
		$drug->setName($_POST['drugname']);
		$drug->setId($_POST['drug_']);
		$drug->setCode($_POST[md5('code')]);
	}
	
	if (is_blank($_POST['generic_id'])) {
		exit("error:Generic Name of drug is required");
	} else {
		$drug->setGeneric((new DrugGenericDAO())->getGeneric($_POST['generic_id'], false));
	}
	
	if (is_blank($_POST['manufacturer_id'])) {
		exit("error:Manufacturer info required");
	} else {
		$drug->setManufacturer((new DrugManufacturerDAO())->getManufacturer($_POST['manufacturer_id']));
	}
	
	if (!is_numeric(parseNumber($_POST['base_price']))) {
		exit("error:Invalid base price");
	} else {
		$drug->setBasePrice(parseNumber($_POST['base_price']));
	}
	
	if (isset($_POST['stock_uom'])) {
		$drug->setStockUOM($_POST['stock_uom']);
	} else {
		exit("error:Unit of measure is required");
	}
	if(!is_blank($_POST['erp_id'])){
		$drug->setErpProduct($_POST['erp_id']);
	}
	$data = (new DrugDAO())->updateDrug($drug, $drug->getId());
	if ($data !== null) {
		exit("success:Drug Updated");
	}
	exit("error:Cannot update drug details");
}
$_GET['suppress'] = true;
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_categories.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_manufacturers.php';
?>

<div>
	<div class="well" id="console"></div>
	<form id="adddrugForm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : DoneEdit})">
		<label>Drug Name<span class="required-text">*</span>
			<input type="text" name="drugname" required="required" value="<?= $drug->getName() ?>"></label>
		<label>Generic
			<span class="pull-right"><a href="javascript:;" id="addGenLink">add generic name</a></span>
			<select name="generic_id"><?php foreach ($gens as $g) {?>
					<option value="<?=$g->getId()?>"<?=($drug->getGeneric()->getId() == $g->getId() ? ' selected="selected"' : '')?>><?=$g->getName()?> [<?=$g->getWeight()?> <?=$g->getForm()?>]</option>
				<?php } ?>
			</select>
		
		</label>
		<label>
			Stock Unit of Measure
			<select name="stock_uom" data-placeholder="-- Select --">
				<option></option>
				<?php
				$stock_uoms = (new DrugDAO())->getDrugStockUOMs();
				foreach ($stock_uoms as $uom) {?>
					<option value="<?=$uom?>"<?=($drug->getStockUOM() == $uom ? ' selected="selected"' : '')?>><?=ucwords($uom)?></option>
				<?php } ?>
			</select>
		</label>
		<label>Manufacturer
			<span class="pull-right"><a href="javascript:;" id="addMfgLink">add manufacturer</a></span>
			<select name="manufacturer_id"><?php foreach ($mans as $man) {
					echo '<option value="' . $man->getId() . '"' . ($man->getId() == $drug->getManufacturer()->getId() ? ' selected="selected"' : '') . '>' . $man->getName() . '</option>';
				} ?></select></label>
		<label>Base Price
			<input type="number" value="<?= $drug->getBasePrice() ?>" min="0" step="0.01" name="base_price"></label>
		<input type="hidden" name="drug_" value="<?= $drug->getId() ?>">
		<input type="hidden" name="<?= md5("code") ?>" value="<?= $drug->getCode() ?>">
		<label>Erp Product Id
			<input type="text" name="erp_id"  value="<?= $drug->getErpProduct() ?>"></label>
		<div>
			<button type="submit" class="btn">Update Drug</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		$('#expiry__').datetimepicker({
			timepicker: false, format: 'Y-m-d',
			onShow: function (ct) {
				var date = new Date(moment().add('days', 2)).toISOString().split('T')[0];
				//FIXME: buggy, finally yields to today
				console.log(date);// correct date, but not applied as the minimum date? why???
				this.setOptions({minDate: date});
			}
		});
	});


	var drugCats = <?= json_encode($drugCategories, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugMfgs = <?= json_encode($drugManufacturers, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;

	$("#addMfgLink").on('click', function () {
		Boxy.load('/pages/pm/pharmacy/drugman.php', {
			afterHide: function () {
				$.ajax({
					url: "/api/get_drug_manufacturers.php",
					type: "POST",
					dataType: "json",
					success: function (d) {
						drugMfgs = d;
						refreshMan();
//                    todo: set the value to the just added item, not working
//                    $("#manufacturer_id").select2("data", drugMfgs[drugMfgs.length - 1]);
					}
				});
			}
		});
	});
	$("#addGenLink").on('click', function () {
		Boxy.load('/pages/pm/pharmacy/drug-generic-add.php', {
			afterHide: function () {
				$.ajax({
					url: "/api/get_drug_generics.php",
					type: "POST",
					dataType: "json",
					success: function (d) {
						drugGens = d;
						refreshGen();
//                    todo: set the value to the just added item, not working
//                    $("#generic_id").select2("data",drugGens[drugGens.length - 1]);
					}
				});
			}
		});
	});
	function refreshGen() {
		$("#generic_id").select2("destroy");
		setTimeout(function () {
			$("#generic_id").select2({
				width: '100%',
				allowClear: true,
				placeholder: "select drug generic",
				data: {results: drugGens, text: 'name'},
				formatResult: function (source) {
					return source.name + ' [' + source.weight + ' ' + source.form + ']';
				},
				formatSelection: function (source) {
					return source.name + ' [' + source.weight + ' ' + source.form + ']';
				}
			});
		}, 50);

	}
	function refreshMan() {
		$("#manufacturer_id").select2("destroy");
		setTimeout(function () {
			$("#manufacturer_id").select2({
				width: '100%',
				allowClear: true,
				placeholder: "select manufacturer",
				data: {results: drugMfgs, text: 'name'},
				formatResult: function (source) {
					return source.name;
				},
				formatSelection: function (source) {
					return source.name;
				}
			});
		}, 50);
	}
	refreshGen();
	refreshMan();

	function start() {
	}
	function DoneEdit(s) {
		if (s.indexOf("success") != -1) {
			Boxy.info("Updated");
			Boxy.get($('.close')).hideAndUnload();
		} else {
			s1 = s.split(":");
			if (s1[0] == "error") {
				Boxy.alert(s1[1]);
				$('span.error').html('<span class="warning-bar">' + s1[1] + '</span>').show('fast');
			}
		}
	}
</script>