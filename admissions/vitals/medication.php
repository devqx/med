<?php
if (!isset($_SESSION)) {
	session_start();
}
if (!isset($_SESSION ['staffID'])) {
	exit('error:' . $this->SESSION_EXPIRED);
}

$_GET['suppress'] = TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
//require_once($_SERVER['DOCUMENT_ROOT'] . '/Connections/dbconnection.php');
$activeGenericsOnly = TRUE;
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/get_drug_generics.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/api/get_drugs.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.pharmacy.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/class.admissions.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php');

$pp = new Pharmacy();
$pharmacies = (new ServiceCenterDAO())->all('Pharmacy');

if (isset($_REQUEST['prescription'])) {
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/ClinicalTask.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/ClinicalTaskData.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php');

	$reg = json_decode($_REQUEST['prescription']);
	if (sizeof($reg->regimens) === 0) {
		echo "error:Please add one or more regimen data";
		exit;
	}
	if (is_blank($reg->pharmacy_id)) {
		echo "error:Select a pharmacy";
		exit;
	}

	if (AdmissionSetting::$ipMedicationTaskRealTimeDeduct) {
		$pat = new PatientDemograph();
		$pat->setId($reg->pid);
		$pres = new Prescription();
		$pres->setPatient($pat);
		$pres->setRequestedBy($staff);
		$pres->setHospital($staff->getClinic());
		$inp = new InPatient();
		$inp->setId($reg->inpatient);
		$pres->setInPatient($inp);
		$pds = array();
		
		foreach ($reg->regimens as $pre) {
			if ($pp::$canPrescribeBrand) {
				if ($pre->generic === "") {
					echo "error:Please a drug name or a generic name";
					exit;
				}
			} else {
				if ($pre->drug === "" && $pre->generic === "") {
					echo "error:Please a drug name or a generic name";
					exit;
				}
			}

			$pd = new PrescriptionData();
			$g = new DrugGeneric();
			$d = new Drug();
			if ($pre->drug !== "" && $pre->drug != "null") {
				$d->setId($pre->drug->id);
				$g->setId($pre->drug->generic->id);
				$d->setGeneric($g);
				//  Set other drug properties if there is a need for it (NOTE that the complete drug properties are here on the request object)
			} else if ($pre->generic !== "") {
				$g->setId($pre->generic->id);
				if ($pre->drug === "" || $pre->drug == "null") {
					$d = null;
				} else {
					$d->setGeneric($g);
				}
			}
			$pd->setDrug($d);
			$pd->setGeneric($g);
			$pd->setDose($pre->dose);
			$pd->setDuration($pre->duration);
			$pd->setFrequency($pre->freqText);
			$pd->setRefillable(1);
			$pd->setRefillNumber($pre->duration);
			$pd->setRefillDate((new DateTime($pre->startTime))->modify('+1 day')->format('Y-m-d H:i:s')  ); //set it to tomorrow
			$pd->setRequestedBy($staff);
			$pd->setHospital($staff->getClinic());
			$pds[] = $pd;
		}
		$pres->setData($pds);
		$pres->setServiceCentre((new ServiceCenter($reg->pharmacy_id)));
		$p = (new PrescriptionDAO())->addPrescription($pres);
		if ($p === null) {
			echo(json_encode("error:Unable to save regimen"));
			exit;
		}
	}


	$tData = array();
	$staff = new StaffDirectory($_SESSION['staffID']);
	foreach ($reg->regimens as $r) {
		$tDatum = new ClinicalTaskData();

		$tDatum->setClinicalTask(new ClinicalTask($reg->ctid));
		$tDatum->setDrug((($r->drug == null) ? null : new Drug($r->drug->id)));
		$tDatum->setGeneric((($r->generic == null) ? null : new DrugGeneric($r->generic->id)));
		$tDatum->setDose($r->dose);
		$tDatum->setFrequency($r->freq);
		$tDatum->setType(NULL);
		$tDatum->setTaskCount($r->duration);
		$tDatum->setStartTime($r->startTime);
		$tDatum->setCreatedBy($staff);
		$tDatum->setPrivate(!is_blank($r->_private) && $r->_private == 'true' ? true : false);

		$tData[] = $tDatum;
	}

	$newTData = (new ClinicalTaskDataDAO())->addClinicalTaskData($tData);
	@ob_end_clean();
	if ($newTData === null) {
		echo(json_encode("error:Unable to save Medication"));
	} else {
		echo(json_encode("ok:Medication added successfully"));
	}
	exit;
}
?>

<div style="width: 750px;">
	<form id="new_RegimenForm" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
		<h4>Medication</h4>

		<div id="regData">
			<div>
				<label>Pharmacy <select id="pharmacy_id" name="pharmacy_id" data-placeholder="-- Select pharmacy--">
						<option value=""></option>
						<?php foreach ($pharmacies as $k => $pharm) { ?>
							<option value="<?= $pharm->getId() ?>"><?= $pharm->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label>Drug Generic Name <input type="hidden" name="filter-generics" id="generic"></label>
				<label>Drug Name
					<span id="drug-stock-level" style="color: #666; float: right; font-style: italic"></span><input type="hidden" name="drug" id="drug"<?php if (!$pp::$canPrescribeBrand) { ?> disabled="disabled"<?php } ?>></label>
				<div class="row-fluid">
					<label class="span4">Dose <input type="text" name="dose" id="dose"/></label>
					<label class="span8">Frequency
						<!--<input type="text" name="freq" id="freq" value="" placeholder="eg: 2 x daily">-->
						<select name="freq" id="freq">
							<option value="">select frequency</option>
							<option value="<?= (24 * 60) ?>">1 x daily</option>
							<option value="<?= (12 * 60) ?>">2 x daily</option>
							<option value="<?= (8 * 60) ?>">3 x daily</option>
							<option value="<?= (6 * 60) ?>">4 x daily</option>
							<option value="<?= (4 * 60) ?>">Every 4 Hours</option>
							<option value="<?= (3 * 60) ?>">Every 3 Hours</option>
							<option value="<?= (2 * 60) ?>">Every 2 Hours</option>
							<option value="<?= (1 * 60) ?>">Every 1 Hour</option>
						</select>
					</label>
				</div>
				<div class="row-fluid">
					<label class="span4">For <span class="pull-right fadedText"># of times to execute</span>
						<span><input type="number" data-decimals="0" name="task_count" id="task_count" min="1"> </span>
					</label>
					<div class="span6"><span class="pull-right"><label><input type="checkbox" onclick="setImmediately(this)"> Immediately</label></span>
						<label>Start task on <input id="startTime" type="text" name="start_time" class="date"> </label>
					</div>
					<?php if(!is_blank(@$_GET['source'])){?>
						<div class="span2">
							<label>
								<span class="">Private ?</span>
								<input type="checkbox" id="private" name="private" title="" style="display: block;margin-top: 8px;">
							</label>
						</div>
					<?php }?>
				</div>
				<div class="alert-box notice">Prescription is refillable by default</div>


			</div>
		</div>

		<div class="btn-block">
			<button class="btn btn-mini" type="button" id="add-regimen"><i class="icon-plus-sign"></i></button>
			<div id="added-regimen" style="display: inline-block; float: right"></div>
		</div>

		<div class="btn-block">
			<input type="hidden" name="pid" id="pid" value="<?= $_REQUEST['pid'] ?>"/>
			<input type="hidden" name="inpatient" id="inpatient" value="<?= $_REQUEST['aid'] ?>"/>
			<button class="btn" type="button" id="save">Finish</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>

</div>

<script type="text/javascript">
	var drugGens = <?= json_encode($drugGenerics, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugs = <?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var drugData =<?= json_encode($drugs, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	var prescription = {pid: "", ctid: <?= isset($_GET['ctid']) ? $_GET['ctid'] : "" ?>, regimens: []};
	$(document).ready(function () {
		$("#startTime").datetimepicker({
			format: 'Y-m-d H:i'
		});
		$('#save').click(function () {
			save();
		});

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
				$("#added-regimen").append('<button style="margin-right: 3px;" class="btn btn-mini" type="button" data-id="' + i + '"><i class="icon-remove-sign"></i> ' + prescription.regimens[i].dose + ' (' + ((drug !== null) ? prescription.regimens[i].drug.generic.form : prescription.regimens[i].generic.form) + ') ' + ((drug !== null) ? prescription.regimens[i].drug.name : gen.name) + ' ' + prescription.regimens[i].freqText + '</button>');
				resetRegimen();
			}
		});

		$("#generic").select2({
			width: '100%',
			allowClear: true,
			placeholder: "--- select drug generic ---",
			data: {results: drugGens, text: 'name'},
			formatResult: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
			},
			formatSelection: function (source) {
				return source.name + " (" + source.form + ") " + source.weight;
			}
		}).on("change", function () {
			filterDrugs();
		});
		refreshDrug();
	});

	function refreshDrug() {
		$("#drug").select2({
			width: '100%',
			allowClear: true,
			placeholder: "--- select drug ---",
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
				$("#drug-stock-level").html("<b>Stock level:</b> " + drug.stockQuantity);
				if (parseInt(drug.stockQuantity) < 1) {
					Boxy.ask(drug.name + " is unavailable in the store<br>Click <strong>Change</strong> to change the drug or <strong>Ignore</strong> to ignore this warning", ['Change', 'Ignore'], function (answer) {
						if (answer === "Change") {
							$("#drug").select2("val", "");
							$("#drug").select2("open");
							$("#drug-info").html("");
						}
					}, {title: "Low Stock Warning"});
				}
			} else {
				$("#drug-stock-level").html("");
			}
		});
	}

	function filterDrugs() {
		drugData = [];
		$("#drug").select2("val", "");
		$("#drug-stock-level").html("");
		for (var i = 0; i < drugs.length; i++) {
			if ((drugs[i].generic.id === $("#generic").val()) || $("#generic").val() === "") {
				drugData[drugData.length] = drugs[i];
			}
		}
	}

	function save() {
		prescription.inpatient = $("#inpatient").val();
		prescription.pid = $("#pid").val();
		if ($("#pharmacy_id").val() === "") {
			Boxy.alert("Please select a fulfilling pharmacy");
			return null;
		} else {
			prescription.pharmacy_id = $("#pharmacy_id").val();
		}
		if (prescription.regimens.length === 0) {
			Boxy.alert("Sorry, you need to add one or more medication data");
			return;
		}

		$.ajax({
			url: "/admissions/vitals/medication.php",
			type: "post",
			dataType: 'json',
			data: {prescription: JSON.stringify(prescription)},
			beforeSend: function () {
//                console.log(JSON.stringify(prescription));
			},
			success: function (d) {
				if (d.split(":")[0] === "ok") {
					try {
						Boxy.get($('.close')).hideAndUnload(function(){
							Boxy.get($('.close')).hideAndUnload();
						});
						$('#tabbedPane').find('li.active a').click();
					}catch(except){
						location.reload();
					}
				} else {
					Boxy.alert(d.split(":")[1]);
				}
			},
			error: function (d) {
				Boxy.alert("Sorry, we couldn't save the prescription");
			}
		});
	}

	function validateRegimen() {
		regimen = {"drug": "", "freq": "", "dose": "", "duration": "", "startTime": "", _private: $('#private').is(':checked')};
//        if ($("#drug").select2("data") === null) {
		/*Boxy.alert("Please select a drug name", function () {
		 $("#drug").select2("open");
		 });
		 return null;*/
//        } else {
		if ($("#drug").select2("data") !== null) {
			var d = $("#drug").select2("data");
			for (var i = 0; i < prescription.regimens.length; i++) {
				if (prescription.regimens[i].drug.id == d.id) {
					Boxy.alert(prescription.regimens[i].drug.name + " is already added", function () {
						$("#drug").select2("open");
					});
					return null;
				}
			}
			regimen.drug = $("#drug").select2("data");
		}
		if ($("#generic").select2("data") !== null) {
			regimen.generic = $("#generic").select2("data");
		}
//        }

		if ($("#freq").val() === "") {
			Boxy.alert("Please enter the frequency", function () {
				$("#freq").focus();
			});
			return null;
		} else {
			regimen.freq = $("#freq").val();
			regimen.freqText = $("#freq option[value='" + $("#freq").val() + "']").text();
		}

		if ($("#task_count").val() === "") {
			Boxy.alert("Please enter the task count", function () {
				$("#task_count").focus();
			});
			return null;
		} else {
			regimen.duration = $("#task_count").val();
		}

		if ($("#startTime").val() === "") {
			Boxy.alert("Please choose the task start time", function () {
				$("#startTime").focus();
			});
			return null;
		} else {
			regimen.startTime = $("#startTime").val();
		}


		if ($("#dose").val() === "0" || $("#dose").val() === "") {
			Boxy.alert("Please enter the drug dosage", function () {
				$("#dose").focus();
			});
			return null;
		} else {
			regimen.dose = $("#dose").val();
		}
		return regimen;
	}

	function resetRegimen() {
		$("#generic").select2("val", "");
		$("#drug").select2("val", "");
		$("#dose").val("");
		$("#freq").val("");
		$("#drug-stock-level").html("");
		$("#task_count").val("");
		$("#startTime").val("");
		$('#private').prop('checked', false).iCheck('update');
	}

	function setImmediately(el) {
		var ctrl = $('#startTime');
		if ($(el).is(":checked")) {
			ctrl.val(moment().format('YYYY-MM-DD HH:mm'))
		} else {
			ctrl.val('');
		}
	}
</script>
