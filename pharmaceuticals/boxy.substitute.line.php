<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/21/17
 * Time: 12:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.pharmacy.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.config.main.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Clinic.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BodyPartDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/FormularyDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDataDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/DrugDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/DrugGenericDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/SubstitutionCodeDAO.php";

$bodyparts = (new BodyPartDAO())->all();
$formulary = (new FormularyDAO())->all();
$subCodes = (new SubstitutionCodeDAO())->all();

$protect = new Protect();
if (!isset($_SESSION)) {
	session_start();
}
if (!isset($_SESSION ['staffID'])) {
	exit('error:Your session has expired. Please login again');
}
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->pharmacy)) {
	exit ($protect->ACCESS_DENIED);
}

$linePrescription = (new PrescriptionDataDAO())->getPrescriptionDatum($_GET['line_id'], true);
$parentPrescription = (new PrescriptionDAO())->getPatientPrescriptionByCode($linePrescription->getCode(), true);

$editStyleByAdd = Clinic::$editStyleByAdd;

$_GET['suppress'] = true;
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');
$activeGenericsOnly = true;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php';

//$drugAllergens = new ArrayObject();
include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_patient_allergens.php';

$pp = new Pharmacy();
if (!$pp::$canPrescribeBrand) {
	$drugs = [];
} else {
	include_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php';
}


if($_POST){
	//exit("success:This is a test message");
	if(is_blank($_POST['line_id'])){
		exit("error:Sorry we couldn't determine prescription");
	}
	
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$linePrescription = (new PrescriptionDataDAO())->getPrescriptionDatum($_POST['line_id'], true, $pdo);
	
	if($linePrescription->getStatus()=='substituted'){
		$pdo->rollBack();
		exit('error:Line is already substituted');
	}
	
	if (is_blank($_POST['drug']) && is_blank($_POST['filter-generics'])) {
		exit("error:Select drug or generic");
	}
	
	$pd = new PrescriptionData();
	$g = null;
	$d = null;
	if (!is_blank($_POST['drug'])) {
		$d = (new DrugDAO())->getDrug($_POST['drug'], TRUE, $pdo);
		$g = $d->getGeneric();
	} else if ($_POST['filter-generics'] !== "") {
		$g = (new DrugGenericDAO())->getGeneric($_POST['filter-generics'], FALSE, $pdo);
	}
	
	$pd->setDrug($d);
	$pd->setGeneric($g);
	$pd->setDose($_POST['dose']);
	$pd->setDuration(!is_blank($_POST['duration']) ? parseNumber($_POST['duration']) : '--');
	$pd->setComment($_POST['comment']);
	$pd->setExternalSource(isset($_POST['external_source']) && $_POST['external_source']=='yes');
	$pd->setFrequency((!is_blank($_POST['freqno']) ? parseNumber($_POST['freqno']) : '--') . ' x ' . (!is_blank($_POST['freqtype']) ? $_POST['freqtype'] : '--'));
	$pd->setRefillable(isset($_POST['refillable']) && $_POST['refillable']=='on');
	$pd->setRefillNumber(parseNumber($_POST['refill_number']));
	$pd->setRequestedBy($this_user);
	$pd->setHospital($this_user->getClinic());
	$pd->setStatus('open');
	$pd->setRelated($linePrescription);
	$pd->setCode($linePrescription->getCode());
	
	$linePrescription->setStatus('substituted')->setSubstitutionReason($_POST['regnote'])->setSubstitutedBy($this_user)->setSubstitutedOn( date(MainConfig::$mysqlDateTimeFormat) )->update($pdo);
	if($pd->add($pdo)){
		$pdo->commit();
		exit('success:Substitution successful');
	}
	$pdo->rollBack();
	exit('error:Substitution failed');
}

?>

<script type="text/javascript">
	function setDrugs(data) {
		$("#drug").select2('val', '').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select drug",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
			},
			formatSelection: function (source) {
				return source.name + "( " + source.generic.weight + " " + source.generic.form + ")";
			}
		});
		$("#drug-info").html("");
	}

	function setGenerics(data) {
		$("#generic").select2('val', '').select2({
			width: '100%',
			allowClear: true,
			placeholder: "-- select drug generic --",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
				// This loads Drug generic name
			},
			formatSelection: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
			}
		});
	}

	function filterDrugs() {
		drugData = [];
		$("#drug").select2("val", "");
		$("#drug-info").html("");
		for (var i = 0; i < drugs.length; i++) {
			if ((drugs[i].generic.id === $("#generic").val()) || $("#generic").val() === "") {
				drugData[drugData.length] = drugs[i];
			}
		}
	}
	
	var _start = function(){
		$(document).trigger('ajaxSend');
	};
	var _submit = function(s){
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		
		if(data[0]==='error'){
			Boxy.alert(data[1]);
		} else if(data[0]==='success') {
			Boxy.info(data[1], function(){
				$(".close").click();
				//alert($('td.pres_details[data-href^="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $linePrescription->getCode()?>"]').length);
				setTimeout(function(){
					$('td.pres_details[data-href^="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $linePrescription->getCode()?>"]')[0].click();
				}, 20);
			});
		}
	};
	

	function save() {
		
		
		/*if ($("#regnote").val() === ""){
		 Boxy.alert("Please enter a note");
		 return;
		 } else */
		if (prescription.regimens.length === 0) {
			Boxy.alert("Sorry you need to add one or more regimen data");
			return;
		} else {
			prescription.note = $("#regnote").val() || "";
		}

		if ($("#pid").select2("data") === null || $("#pid").select2("data").id === "") {
			Boxy.alert("Please select a patient");
			return null;
		} else {
			prescription.inpatient = $("#inpatient").val();
			prescription.pid = $("#pid").val();
		}
		if ($("#pharmacy_id").val() === "") {
			Boxy.alert("Please select a fulfilling pharmacy");
			return null;
		} else {
			prescription.pharmacy_id = $("#pharmacy_id").val();
		}
		if ($("#input_by").val() === '') {
			prescription.input_by = input_by;
		} else {
			prescription.input_by = $("#input_by").val();
		}
		showPinBox(callback);
	}

	function validateRegimen() {
		regimen = {
			"drug": "",
			"dose": "",
			"freqno": "",
			"freqtype": "",
			"duration": "",
			"refillable": false,
			"refill_number": "",
			"generic": "",
			"comment": "",
			"body_part": "",
			"external_source": "no"
		};
		if ($("#drug").select2("data") === null && $("#generic").select2("data") === null) {
			Boxy.alert("Please select a drug name or drug generic name", function () {
				$("#drug").select2("open");
			});
			return null;
		} else {
			if ($("#drug").select2("data") !== null) {
				regimen.drug = $("#drug").select2("data");
			}
			if ($("#generic").select2("data") !== null) {
				regimen.generic = $("#generic").select2("data");
			}
		}

		if ($("#dose").val() === "0" || $("#dose").val() === "") {
			<?php if($editStyleByAdd){?>
			regimen.dose = '-';
			<?php } else {?>
			Boxy.alert("Please enter the drug dosage", function () {
				$("#dose").focus();
			});
			return null;
			<?php }?>
		} else {
			regimen.dose = $("#dose").val();
		}

		if ($("#freqno").val() === "") {
			<?php if($editStyleByAdd){?>
			regimen.freqno = '-';
			<?php } else {?>
			Boxy.alert("Please enter the frequency", function () {
				$("#freqno").focus();
			});
			return null;
			<?php }?>
		} else {
			regimen.freqno = $("#freqno").val();
		}
		regimen.refillable = $("#refillable").is(":checked");
		regimen.comment = $('input[name="comment"]').val();
		regimen.refill_number = $('input[name="refill_number"]').val();

		if ($("#freqtype").select2("data") === null) {
			<?php if($editStyleByAdd){?>
			regimen.freqtype = {id: '--', text: '--'};
			<?php } else {?>
			Boxy.alert("Please select a frequency type", function () {
				$("#freqtype").select2("open");
			});
			return null;
			<?php }?>
		} else {
			regimen.freqtype = {id: $("#freqtype").select2("data").id, text: $("#freqtype").select2("data").text};
		}

		if ($("#duration").val() === "0" || $("#duration").val() === "") {
			<?php if($editStyleByAdd){?>
			regimen.duration = '--';
			<?php } else {?>
			Boxy.alert("Please enter drug duration", function () {
				$("#duration").focus();
			});
			return null;
			<?php }?>
		} else {
			regimen.duration = $("#duration").val();
		}
		regimen.body_part = $('select[name="bodypart"]').val() || null;
		regimen.external_source = $('[name="external_source"]').val();

		return regimen;
	}
	
	function refreshDrug() {
		$("#drug").select2({
			width: '100%',
			allowClear: true,
			placeholder: "---select drug---",
			data: function () {
				return {results: drugData, text: 'name'};
			},
			formatResult: function (source) {
				return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
			},
			formatSelection: function (source) {
				return source.name + " (" + source.generic.weight + " " + source.generic.form + ")";
			}
		}).on("change", function (e) {
			var drug = $("#drug").select2("data");
			if (drug !== null) {
				$("#drug-info").html("<b>Stock level:</b> " + drug.stockQuantity + "; <b>Base Price: &#8358;</b>" + drug.basePrice);
				if (parseInt(drug.stockQuantity) < 1) {

					Boxy.ask(drug.name + " is unavailable in the store<br>Click <strong>Change</strong> to change the drug or <strong>Ignore</strong> to ignore this warning", ['Change', 'Ignore'], function (answer) {
						if (answer === "Change") {
							$("#drug").select2("val", "");
							$("#drug").select2("open");
							$("#drug-info").html("");
						}
					}, {title: "Low Stock Warning"});
				}
				showInsuranceNotice($("#pid").val(), e);
				if (_.includes(_allergicGenerics, drug.generic.id)) {
					$.notify2("Patient is allergic to " + drug.name, "warn");
				}
			} else {
				$("#drug-info").html("");
			}
		});
	}

</script>
<div style="width: 750px;">
	<form id="new_RegimenForm" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:_start, onComplete:_submit})">
		<h4>Regimen Data</h4>

		<div id="regData">
			<div>
				<label></label>
				<label><input type="hidden" name="pid" id="pid" value="<?= $parentPrescription->getPatient()->getId() ?>"/></label>

				<label>Formulary <select id="formulary_id" data-placeholder="--select formulary--">
						<option></option>
						<?php foreach ($formulary as $form) { ?>
							<option value="<?= $form->getId() ?>"><?= $form->getName() ?></option>
						<?php } ?>
					</select> </label>

				<label>Drug Generic Name<input type="hidden" name="filter-generics" id="generic"></label>
				<label>Drug Name <span id="drug-info" class="fadedText pull-right"></span>
					<input type="hidden" name="drug" id="drug" <?php $pp = new Pharmacy();
					if (!$pp::$canPrescribeBrand) { ?> disabled="disabled"<?php } ?>></label>

				<label>Frequency
					<span class="pull-right">
						<input type="checkbox" name="refillable" id="refillable"> Refillable
					</span>
				</label>
				<div class="row-fluid" id="more_refill">
					<label class="span12 ">Refills count<input type="number" data-decimals="0" id="refill_number" name="refill_number" placeholder="Add number of refills"></label>
				</div>
				<div class="row-fluid">
					<label class="span3" title="Please Enter Numbers Only!"><input style="min-width: 10px" type="number" data-decimals="0" name="freqno" id="freqno" placeholder="eg. 3"></label>
					<label class="span9">
						<select name="freqtype" id="freqtype" data-placeholder="-- Select frequency type --">
							<option value=""></option>
							<?php $drugfrequencylist = MainConfig::$drugFrequencies;
							foreach ($drugfrequencylist as $f) { ?>
								<option value="<?= $f ?>"><?= ucfirst($f) ?></option><?php } ?>
						</select>
					</label>
				</div>
				<div class="row-fluid">
					<label class="span3" style="margin-bottom: -5px">Dose <input type="text" name="dose" id="dose" placeholder="Dose quantity"></label>
					<label class="span3" title="Please Enter Numbers Only!">Duration <!--<span style="font-size: 90%; font-style: italic; color: #666"></span>-->
						<input type="number" name="duration" id="duration" data-decimals="0" value="" placeholder="(value in days) eg: 7">
					</label>
					<label class="span6">
						Note
						<input type="text" name="comment" placeholder="Regimen Line Instruction">
					</label>
				</div>
				<!--<div class="row-fluid hide">
					<label class="span12">Body part <select name="bodypart" data-placeholder="Select the body part">
							<option value=""></option>
							<?php foreach ($bodyparts as $bp) { ?>
								<option value="<?= $bp->getId() ?>"><?= $bp->getName() ?></option><?php } ?>
						</select></label>
				</div>
				
				<label <?php if ($this_user->hasRole($protect->doctor_role)) { ?>class="hide"<?php } ?>>Prescribed By
					<input type="text" id="input_by" name="input_by" placeholder="Enter Fullname" required value="<?= ($this_user->hasRole($protect->doctor_role)) ? $this_user->getFullname() : '' ?>">
				</label>-->

				<p class="clear clearBoth">Prescription was made from another facility</p>
				<div class="row-fluid">
					<label class="span2"><input type="radio" name="external_source" value="yes"> Yes </label>
					<label class="span2"><input type="radio" name="external_source" value="no" checked="checked"> No </label>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="clear">
			<!--
			<button class="btn btn-mini" type="button" id="add-regimen"><i class="icon-plus-sign"></i></button>
			<button class="btn btn-mini cancel" type="button" id="reset-regimen"><i class="icon-remove-sign"></i></button>
			<div id="added-regimen" style="display: inline-block; float: right"></div>
			-->
			<label data-name="Regimen" style="">Substitution Reason
				<select name="regnote" id="regnote" required>
					<?php foreach ($subCodes as $code){?>
						<option value="<?=$code->getId() ?>"><?= $code->getName()?></option>
					<?php }?>
				</select>
			</label>
		</div>
		<input type="hidden" name="line_id" value="<?= $linePrescription->getId()?>">
		
		<div class="btn-block">
			<button class="btn" type="submit">Continue</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	var DrugGenericAllergens = <?= json_encode($drugAllergens, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugs = <?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugData = drugs;

	var _allergicGenerics = [];
	_.each(DrugGenericAllergens, function (obj) {
		_.each(obj.superGeneric.data, function (o) {
			_allergicGenerics.push(o.id);
		});
	});

	$(document).ready(function () {
		$('#more_refill').hide();
		$("#refillable").on('change', function () {
			if ($(this).is(':checked')) {
				$('#more_refill').show();
			} else {
				$('#more_refill').hide();
				$('#refill_number').val("");
			}
		});

		$('[name="external_source"]').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});

		$('#save').click(function () {
			save();
		});

		$('.boxy-content [name="pid"]').change(function (e) {
			var selDrug = $('#drug');
			if (selDrug.select2('data') !== null) {
				e.added = selDrug.select2('data');
				e.currentTarget = selDrug;
				showInsuranceNotice($("#pid").val(), e);
			}

			if ($("#pid").val() !== '') {
				$.getJSON('/api/get_patient_allergens.php', {pid: $("#pid").val()}).then(function (response) {
					DrugGenericAllergens = (response);
					_.each(DrugGenericAllergens, function (obj) {
						_.each(obj.superGeneric.data, function (o) {
							_allergicGenerics.push(o.id);
						});
					});
				})
			}
		}); //End Patient Select2

		$("#generic").select2({
			width: '100%',
			allowClear: true,
			placeholder: "select drug generic",
			data: {results: drugGens, text: 'name'},
			formatResult: function (source) {
				return source.name + " (" + source.form + ") " + source.weight; // This loads Drug generic name
			},
			formatSelection: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
			}
		}).on("change", function (e) {
			//filterDrugs(drugGens);
			if (e.added) {
				if (_.includes(_allergicGenerics, e.added.id)) {
					$.notify2("Patient is allergic to " + e.added.name, "warn");
				}
				setDrugs(_.filter(drugs, function (obj) {
					return obj.generic.id === e.added.id;
				}));
			} else {
				setTimeout(function () {
					$('#formulary_id').trigger('change');
				}, 150);
			}
		});
		refreshDrug();

		$('#formulary_id').on('change', function (e) {
			var id = $(this).val();
			if (id) {
				$.getJSON('/api/get_formulary.php', {id: id, action: 'generics'}, function (data) {
					var filtered = [];
					var filteredIds = [];
					_.each(data.data, function (formulary) {
						filtered.push(formulary.generic);
						filteredIds.push(formulary.generic.id);
					});
					setGenerics(filtered);
					setDrugs(_.filter(drugs, function (drug) {
						return _.includes(filteredIds, drug.generic.id);
					}));
				});
			} else {
				setGenerics(drugGens);
				setDrugs(drugs);
			}
		});
	});
</script>
