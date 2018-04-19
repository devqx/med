<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/class.pharmacy.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/Clinic.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BodyPartDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/FormularyDAO.php";

$bodyparts = (new BodyPartDAO())->all();
$formulary = (new FormularyDAO())->all();

$editStyleByAdd = Clinic::$editStyleByAdd;
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
$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');
if (isset($_REQUEST['prescription'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/BodyPart.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();

	$reg = json_decode($_REQUEST['prescription']);

	/*if (trim($reg->note) === "") {
			echo "error:Please make a note about this prescription";
			exit;
	} else*/
	if (sizeof($reg->regimens) === 0) {
		$pdo->rollBack();
		exit("error:Please add one or more regimen data");
	}

	if (is_blank($reg->pharmacy_id)) {
		$pdo->rollBack();
		exit("error:Select a pharmacy");
	}
	$pat = new PatientDemograph($reg->pid);

	$pres = new Prescription();
	$pres->setPatient($pat);
	if (isset($reg->inpatient)) {
		$pres->setInPatient(new InPatient($reg->inpatient));
	}
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
	$pres->setRequestedBy($staff);
	$pres->setNote($reg->note);
	$pres->setHospital($staff->getClinic());
	$pres->setEncounter(!is_blank(@$_REQUEST['encounter_id']) ? new Encounter(@$_REQUEST['encounter_id']) : null);
	$pres->setPrescribedBy($reg->input_by);
	$pds = array();
	$strPrescriptions = [];
	foreach ($reg->regimens as $pre) {
		if ($pre->drug === "" && $pre->generic === "") {
			echo "error:Please a drug name or a generic name";
			exit;
		}

		$pd = new PrescriptionData();
		$g = new DrugGeneric();
		$d = new Drug();
		if ($pre->drug !== "" && $pre->drug != "null") {
			$d->setId($pre->drug->id);
			$d->setName($pre->drug->name);
			$d->setCode($pre->drug->code);
			$d->setStockQuantity($pre->drug->stockQuantity);
			$g->setId($pre->drug->generic->id);
			$d->setGeneric($g);
			//  Set other drug properties if there is a need for it (NOTE that the complete drug properties are here on the request object)
		} else if ($pre->generic !== "") {
			$g->setId($pre->generic->id);
			$g->setName($pre->generic->name);
			if ($pre->drug === "" || $pre->drug == "null") {
				$d = null;
			} else {
				$d->setGeneric($g);
			}
		}

		$pd->setDrug($d);
		$pd->setGeneric($g);
		$pd->setDose($pre->dose);
		$pd->setDuration(parseNumber($pre->duration));
		$pd->setComment($pre->comment);
		$pd->setExternalSource($pre->external_source);
		$pd->setFrequency(parseNumber($pre->freqno) . ' x ' . $pre->freqtype->id);
		$pd->setRefillable($pre->refillable);
		$pd->setRefillNumber(parseNumber($pre->refill_number));
		$pd->setRequestedBy($staff);
		$pd->setHospital($staff->getClinic());
		$pd->setBodyPart((new BodyPartDAO())->get($pre->body_part, $pdo));
		$pd->setDiagnosis((new DiagnosisDAO())->getDiagnosis($pre->diagnosis->id));
		$pds[] = $pd;
		
		require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DrugDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DrugGenericDAO.php';
		$strPrescriptions[] = ($pre->dose . ' ' . (($d !== null) ? (new DrugDAO)->getDrug($d->getId())->getName() : (new DrugGenericDAO())->getGeneric($g->getId())->getName()). ' '. parseNumber($pre->freqno) . ' x ' . $pre->freqtype->id. ' for '.parseNumber($pre->duration).' day(s)' .' '. $pre->comment);
	}
	
	if (!is_blank(@$_REQUEST['encounter_id'])) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
		//$prescription_plan = (implode(' || ', $strPrescriptions));
		foreach ($strPrescriptions as $prescription) {
			$vNote = (new VisitNotes())->setPatient($pat)->setNoteType('plan')->setDescription($prescription)->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter(new Encounter($_REQUEST['encounter_id']))->setNotedBy(new StaffDirectory($_SESSION['staffID']));
			if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
				$pdo->rollBack();
				exit("error:Failed to save Medication Plan Note");
			}
		}
	}
	
	$pres->setData($pds);
	$pres->setServiceCentre((new ServiceCenter($reg->pharmacy_id)));

	$p = (new PrescriptionDAO())->addPrescription($pres);
	if ($p === null) {
		echo(json_encode("error:Unable to save regimen"));
	} else {
		$pq = new PatientQueue();
		$pq->setType("Pharmacy");
		$pq->setPatient($pat);
		(new PatientQueueDAO())->addPatientQueue($pq);
		echo(json_encode([$p, $pds]));
	}
	exit;
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
		if ($("#input_by").val() ===''){
		    prescription.input_by = input_by;
        } else {
		    prescription.input_by = $("#input_by").val();
        }
		var callback = function () {
			$.ajax({
				url: "/boxy.addRegimen.php",
				type: "post",
				dataType: 'json',
				data: {prescription: JSON.stringify(prescription)<?=(isset($_GET['enc_id'])) ? ', encounter_id:'.$_GET['enc_id']:''?>},
				beforeSend: function () {
				},
				success: function (d) {
					if (d.length === 2) {
						Boxy.info("Your prescription was added successfully!",
							function () {
								if (!inPatientContext && !ivfcontext) {
										showTabs(3);
								} else {
									if(inPatientContext){
										aTab(1);
									}
									if(ivfcontext){
									
									}
								}
							},
							{title: "Prescription status:"}
						);
						Boxy.get($(".close")).hideAndUnload();
					} else {
						Boxy.alert(d.split(":")[1]);
					}
				},
				error: function (d) {
					Boxy.alert("Sorry, we couldn't save the prescription");
				}
			});
		};
		showPinBox(callback);
	}

	function validateRegimen() {
		regimen = {
			"drug": "",
			"dose": "",
			"freqno": "",
			"freqtype": "",
			"diagnosis": "",
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
		if ($("#diagnosis").select2("data") !== null) {
			regimen.diagnosis = $("#diagnosis").select2("data");
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

	function resetRegimen() {
		$("#generic").select2("val", "").trigger("change");
		$("#drug").val("");
		$("#dose").val("");
		$("#freqno").val("");
		$("#freqtype").select2("val", "");
		$("#diagnosis").select2("val", "");
		$("#duration").val("");
		$("#drug-info").html("");
		$("input[name='comment']").val("");
		$("#refillable").prop("checked", false).trigger('change').iCheck('update');
		$('input[name="refill_number"]').val("");
		$('select[name="bodypart"]').select2('val', '');
		$('[name="external_source"]').prop('checked', false).iCheck('update');
		$('[name="external_source"][value="no"]').prop('checked', true).iCheck('update');
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
				if( _.includes(_allergicGenerics, drug.generic.id)){
					$.notify2("Patient is allergic to "+ drug.name, "warn");
				}
			} else {
				$("#drug-info").html("");
			}
		});
	}

</script>
<div style="width: 750px;">
	<form id="new_RegimenForm" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<h4>Regimen Data</h4>

		<div id="regData">
			<div>
				<label></label>
				<label>
					<?= isset($_GET['id']) ? "" : "Patient" ?><input type="hidden" name="pid" id="pid" value="<?= @$_GET['id'] ?>"/></label>
				<label> Business Unit/Service Center
					<select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy --">
						<option value=""></option>
						<?php foreach ($pharmacies as $k => $pharm) { ?>
							<option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
						<?php } ?>
					</select></label>

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
					<label class="span3" title="Please Enter Numbers Only!" >Duration <!--<span style="font-size: 90%; font-style: italic; color: #666"></span>-->
						<input type="number" name="duration" id="duration" data-decimals="0" value="" placeholder="(value in days) eg: 7">
					</label>
					<label class="span6">
						Note
						<input type="text" name="comment" placeholder="Regimen Line Instruction">
					</label>
				</div>
				<div class="row-fluid hide">
					<label class="span12">Body part <select name="bodypart" data-placeholder="Select the body part">
							<option value=""></option>
							<?php foreach ($bodyparts as $bp) { ?>
								<option value="<?= $bp->getId() ?>"><?= $bp->getName() ?></option><?php } ?>
						</select></label>
				</div>

        <label <?php if ($this_user->hasRole($protect->doctor_role)){?>class="hide"<?php }?>>Prescribed By
	        <input type="text" id="input_by" name="input_by" placeholder="Enter Fullname" required value="<?= ($this_user->hasRole($protect->doctor_role)) ? $this_user->getFullname() :''?>">
        </label>
				<div>Diagnosis Data
					<span class="pull-right">
          <label style="display: inline;">
           <input type="radio" checked="checked" name="type_diagnosis" value="icd10">ICD10</label>
          <label style="display: inline;"><input type="radio" name="type_diagnosis" value="icpc-2">ICPC-2</label>
         </span>
				</div>
				<div class="diagnosis row-fluid">
        <span>
            <input type="hidden" id="diagnosis" name="diagnosis" class="span12">
        </span>
				</div>
				<p class="clear clearBoth">Prescription was made from another facility</p>
				<div class="row-fluid">
					<label class="span2"><input type="radio" name="external_source" value="yes"> Yes </label>
					<label class="span2"><input type="radio" name="external_source" value="no" checked="checked"> No </label>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<div class="clear">
			<button class="btn btn-mini" type="button" id="add-regimen"><i class="icon-plus-sign"></i></button>
			<button class="btn btn-mini cancel" type="button" id="reset-regimen"><i class="icon-remove-sign"></i></button>
			<div id="added-regimen" style="display: inline-block; float: right"></div>
			<label data-name="Regimen" style="display: none">Regimen Note
				<textarea name="regnote" id="regnote" cols="40" rows="2" style="width:100%"></textarea>
			</label>
		</div>

		<?php if (isset($_GET['aid'])) { ?>
			<input type="hidden" id="inpatient" name="inpatient" value="<?= $_GET['aid'] ?>"><?php } ?>
		  <input type="hidden" name="ivf" id="ivf" value="<?= $_GET['ivf']?>">

		<div class="btn-block">
			<button class="btn" type="button" id="save">Finish</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script type="text/javascript">
	var DrugGenericAllergens = <?= json_encode($drugAllergens, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugs = <?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugData = drugs;
	var prescription = {
		"pid": "",
		"pharmacy_id": "",
		"inpatient":<?= isset($_GET['aid']) ? $_GET['aid'] : 'false'?>,
		"note": "",
		"regimens": [],
		"refill_number": "",
    "input_by": ""
	};
	
	var _allergicGenerics = [];
	_.each(DrugGenericAllergens, function (obj) {
		_.each(obj.superGeneric.data, function(o){
			_allergicGenerics.push(o.id);
		});
	});
	
	var inPatientContext = false;
	var ivfcontext =    $("#ivf").val();
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

		$(".boxy-content input[name='diagnosis']").select2({
			placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
			allowClear: true,
			minimumInputLength: 3,
			formatResult: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			}, formatSelection: function (data) {
				return data.name + " (" + data.type + ": " + data.code + ")";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the diagnosis name or ICD-10/ICPC-2 code";
			},
			ajax: {
				url: '/api/get_diagnoses.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						type: $('[name="type_diagnosis"]:checked').val()
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		});
		
		inPatientContext = ($("#pid").val().trim() === "");
		if (inPatientContext) {
			$('.boxy-content [name="pid"]').select2({
				placeholder: "Patient Name (Patient ID [Patient Legacy ID])",
				allowClear: true,
				minimumInputLength: 3,
				width: '100%',
				formatResult: function (data) {
					return data.fullname + " -" + data.id + (data.lid.trim() !== "" ? "[" + data.lid + "]" : "") + ", Phone: " + data.phone;
				},
				formatSelection: function (data) {
					return data.fullname + " -" + data.id + ", " + data.sex + ", " + moment(data.dob).fromNow(true) + " old " + (typeof data.vitalSigns !== "undefined" && typeof data.vitalSigns.weight !== "undefined" ? ", " + data.vitalSigns.weight.value + "kg" : "");
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the patient name or ID";
				},
				ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
					url: '/api/search_patients.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							limit: 100,
							asArray: true,
							medical: true
						};
					},
					results: function (data, page) { // parse the results into the format expected by Select2.
						// since we are using custom formatting functions we do not need to alter remote JSON data
						return {results: data};
					}
				}
			}).change(function (e) {
				var selDrug = $('#drug');
				if (selDrug.select2('data') !== null) {
					e.added = selDrug.select2('data');
					e.currentTarget = selDrug;
					showInsuranceNotice($("#pid").val(), e);
				}
				
				if($("#pid").val() !== ''){
					$.getJSON('/api/get_patient_allergens.php', {pid: $("#pid").val()}).then(function(response){
						DrugGenericAllergens = (response);
						_.each(DrugGenericAllergens, function (obj) {
							_.each(obj.superGeneric.data, function(o){
								_allergicGenerics.push(o.id);
							});
						});
					})
				}
			}); //End Patient Select2
		}
		$("#added-regimen button").live('click', function () {
			prescription.regimens.splice($(this).data("id"), 1);
			$(this).remove();
		});
		$("#reset-regimen").click(function () {
			resetRegimen();
		});
		$("#add-regimen").click(function () {
			reg = validateRegimen();
			if (reg !== null) {
				var i = prescription.regimens.length;
				prescription.regimens[i] = reg;
				var drug = (prescription.regimens[i].drug === null || prescription.regimens[i].drug === "") ? null : prescription.regimens[i].drug;
				var gen = (prescription.regimens[i].generic === null || prescription.regimens[i].generic === "") ? null : prescription.regimens[i].generic;
				var diag = (prescription.regimens[i].diagnosis === null || prescription.regimens[i].diagnosis === "") ? null : prescription.regimens[i].diagnosis;
				$("#added-regimen").append('<button class="btn btn-mini" type="button" data-id="' + i + '"><i class="icon-remove-sign"></i> ' + prescription.regimens[i].dose + ' ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqno + ' x ' + prescription.regimens[i].freqtype.id + ' for ' + prescription.regimens[i].duration + ' day(s) </button>');
				resetRegimen();
				$("label[data-name='Regimen']").show();
			}
		});

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
				if( _.includes(_allergicGenerics, e.added.id)){
					$.notify2("Patient is allergic to "+ e.added.name, "warn");
				}
				setDrugs(_.filter(drugs, function (obj) {
					return obj.generic.id === e.added.id;
				}));
			} else {
				setTimeout(function(){$('#formulary_id').trigger('change');},150);
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
