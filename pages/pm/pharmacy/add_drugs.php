<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	$drug = new Drug();
	if (empty($_POST['drugname'])) {
		exit("error:Drug name is required");
	} else {
		$drug->setName($_POST['drugname']);
	}
	
	if (empty($_POST['generic_id'])) {
		exit("error:Generic Name of drug is required");
	} else {
		$drug->setGeneric((new DrugGenericDAO())->getGeneric($_POST['generic_id'], false));
	}
	
	if (empty($_POST['manufacturer_id'])) {
		exit("error:Manufacturer info required");
	} else {
		$drug->setManufacturer((new DrugManufacturerDAO())->getManufacturer($_POST['manufacturer_id']));
	}
	
	if (!is_numeric(parseNumber($_POST['base_price']))) {
		exit("error:Invalid base price");
	} else {
		$drug->setBasePrice(parseNumber($_POST['base_price']));
	}
	if (!empty($_POST['stock_uom'])) {
		$drug->setStockUOM($_POST['stock_uom']);
	} else {
		exit("error:Stock UOM required");
	}
	
	if(!empty($_POST['erp_id'])){
		$drug->setErpProduct($_POST['erp_id']);
	}
	
	$data = (new DrugDAO())->addDrug($drug);
	if ($data !== null) {
		exit("success:Drug added");
	}
	exit("error:Cannot add drug to pharmacy");
}
$_GET['suppress'] = true;
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_categories.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_manufacturers.php';
$mans = (new DrugManufacturerDAO())->getManufacturers();
$gens = (new DrugGenericDAO())->getGenerics(null, true);
?>

<div id="adddrug" style="width:600px"><span class="error"></span>
	<form id="adddrugForm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : AddDrug})">
		<label>Drug Name<span class="required-text">*</span>
			<input type="text" name="drugname" required="required"></label>
		<label>Generic
			<span class="pull-right"><a href="javascript:;" id="addGenLink">add generic name</a></span>
			<input type="hidden" name="generic_id" id="generic_id">
		</label>
		<label>
			Stock Unit of Measure
			<select name="stock_uom">
				<?php
				$stock_uoms = (new DrugDAO())->getDrugStockUOMs();
				foreach ($stock_uoms as $uom) {
					echo '<option value="' . $uom . '">' . ucwords($uom) . '</option>';
				} ?>
			</select>
		</label>
		<label>Manufacturer
			<span class="pull-right"><a href="javascript:;" id="addMfgLink">add manufacturer</a></span>
			<input type="hidden" name="manufacturer_id" id="manufacturer_id">
		</label>
		<label>Base Price <span class="pull-right fadedText">per stock unit</span>
			<input type="number" value="0" min="0" step="0.01" name="base_price"></label>
		<label>ERP Product Id
			<input type="text" name="erp_id" ></label>
		<div>
			<button type="submit" class="btn">Add Drug</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
				Cancel
			</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	var drugCats = <?= json_encode($drugCategories, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugMfgs = <?= json_encode($drugManufacturers, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;

	function AddDrug(s) {
		var s1 = s.split(":");
		if (s1[0] === "success") {
			$('span.error').html('<span class="alert alert-info">' + s1[1] + '</span>');
			$('#adddrugForm').get(0).reset();
			//noinspection JSUnresolvedFunction
			Boxy.get($(".close")).hideAndUnload();
		} else if (s1[0] === "error") {
			$('span.error').html('<span class="alert alert-error">' + s1[1] + '</span>');
		}
	}
	$('#expiry_').datetimepicker({
		timepicker: false, format: 'Y-m-d', onShow: function (ct) {
			var date = new Date(moment().add('days', 2)).toISOString().split('T')[0];
			//fixme: buggy, finally yields to today
			console.log(date);// correct date, but not applied as the minimum date? why???
			this.setOptions({minDate: date.toString()});
		}
	});
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
</script>