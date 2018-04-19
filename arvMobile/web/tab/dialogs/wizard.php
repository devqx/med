<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/24/16
 * Time: 4:31 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryTemplateDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvPatientHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvHistoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ArvPatientHistory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ArvHistory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ArvHistoryTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDiagnosis.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ArvPatientHistoryData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ArvDrugData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AppointmentGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Appointment.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvDrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ArvDrugDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentGroupDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsulting.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/ArvConsultingData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvConsultingDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvConsultingDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/CurrencyDAO.php";

$currency = (new CurrencyDAO())->getDefault();

$severities = getTypeOptions('severity', 'patient_diagnoses');
$specimens = (new LabSpecimenDAO())->getSpecimens();
$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], false);
$HISTORY = (new ArvHistoryDAO())->all();


if (!$_SESSION) {
	session_start();
}
if ($_POST) {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false, $pdo);
	$patObj = (new PatientDemographDAO())->getPatient($_POST['pid'], false, $pdo);
	
	$arvConsultation = new ArvConsulting();
	$arvConsultation->setCreateTime(time());
	$arvConsultation->setPatient($patObj);
	$arvConsultation->setCreateUser($this_user);
	
	$arvConsultationData = [];
	
	foreach ($_POST['history_data'] as $historyId => $Value) {
		$pHistory = new ArvPatientHistory();
		$pHistory->setPatient(new PatientDemograph($_POST['pid']));
		$pHistory->setCreator(new StaffDirectory($_SESSION['staffID']));
		$pHistDATA = [];
		$history = (new ArvHistoryDAO())->get($historyId, $pdo);
		
		$pHistory->setHistory($history);
		
		foreach ($Value as $tplId => $comment) {
			if (!is_blank($comment)) {
				$pHistDatum = new ArvPatientHistoryData();
				$pHistDatum->setHistoryTemplateData(new ArvHistoryTemplate($tplId));
				$pHistDatum->setValue($comment);
				
				$pHistDATA[] = $pHistDatum;
			}
		}
		$pHistory->setData($pHistDATA);
		$response = [];
		if (count($pHistDATA) > 0) {
			$resp = (new ArvPatientHistoryDAO())->add($pHistory, $pdo);
			if ($resp != null) {
				$response[] = $resp;
				$arvConsultationData[] = (new ArvConsultingData())->setType('Patient Status')->setTypeData($resp);
			}
			if (count($response) !== count($_POST['history_data'])) {
				$pdo->rollBack();
				exit("error:Failed to save Status Data");
			}
		}
		
	}
	
	foreach ($_POST['lab_data'] as $historyId => $Value) {
		$pHistory = new ArvPatientHistory();
		$pHistory->setPatient(new PatientDemograph($_POST['pid']));
		$pHistory->setCreator(new StaffDirectory($_SESSION['staffID']));
		$pHistDATA = [];
		$history = (new ArvHistoryDAO())->get($historyId, $pdo);
		
		$pHistory->setHistory($history);
		
		foreach ($Value as $tplId => $comment) {
			if (!is_blank($comment)) {
				$pHistDatum = new ArvPatientHistoryData();
				$pHistDatum->setHistoryTemplateData(new ArvHistoryTemplate($tplId));
				$pHistDatum->setValue($comment);
				
				$pHistDATA[] = $pHistDatum;
			}
		}
		$pHistory->setData($pHistDATA);
		$response = [];
		if (count($pHistDATA) > 0) {
			$resp = (new ArvPatientHistoryDAO())->add($pHistory, $pdo);
			if ($resp != null) {
				$response[] = $resp;
				$arvConsultationData[] = (new ArvConsultingData())->setType('Laboratory Status')->setTypeData($resp);
			}
			if (count($response) !== count($pHistDATA)) {
				$pdo->rollBack();
				exit("error:Failed to save Lab Data");
			}
		}
		
	}
	
	$cases = @$_POST['cases'];
	$states = @$_POST['states'];
	
	foreach ($cases as $i => $case) {
		if (!is_blank($case)) {
			$diagnosis = (new PatientDiagnosis())->setPatient($patObj)->setDate(@$_POST['diagnosis_date'][$i])->setDiagnosedBy($this_user)->setDiagnosis(new Diagnosis($case))->setNote($_POST['diagnosisNote'])->setStatus(true)->setType($states[$i]);
			$newDiagnosis = (new PatientDiagnosisDAO())->add($diagnosis, $pdo);
			if ($newDiagnosis == null) {
				$pdo->rollBack();
				exit("error:Diagnosis failed to save");
			} else {
				$arvConsultationData[] = (new ArvConsultingData())->setType('Diagnoses')->setTypeData($newDiagnosis);
				$summaryNote = (new DiagnosisDAO())->getDiagnosis($case, $pdo)->getName() . ' (' . ucwords($states[$i]) . ')';
				$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('asst')->setDescription($summaryNote)->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user);
				
				if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
					$pdo->rollBack();
					exit("error:Failed to save Diagnoses summary Note");
				}
			}
		}
	}
	$arv_drug_data = !is_blank($_POST['arv_drug_data']) ? json_decode($_POST['arv_drug_data']) : [];
	
	@session_start();
	
	foreach ($arv_drug_data as $d) {
		$drug = (new ArvDrugData())->setPatient(new PatientDemograph($_POST['pid']))->setArvDrug((new ArvDrugDAO())->get($d->drug->id, $pdo))->setType($d->type)->setDose($d->dose)->setState('active')->setPrescribedBy(new StaffDirectory($_SESSION['staffID']));
		$newDrug = (new ArvDrugDataDAO())->add($drug, $pdo);
		if ($newDrug === null) {
			$pdo->rollBack();
			exit("error:Failed to save ARV Drug Data [" . $d->drug->name . "]");
		}
		$arvConsultationData[] = (new ArvConsultingData())->setType('Drug')->setTypeData($newDrug);
	}
	
	if (isset($_POST['lab-reqs']) && !empty($_POST['lab-reqs'])) {
		$request = new LabGroup();
		$request->setPatient($patObj);
		$request->setRequestedBy($this_user);
		
		$pref_specimens = array();
		$sel_specimens = isset($_POST['specimen_ids']) ? $_POST['specimen_ids'] : [];
		foreach ($sel_specimens as $s) {
			if (!empty($s))
				$pref_specimens[] = (new LabSpecimenDAO())->getSpecimen($s, $pdo);
		}
		$request->setPreferredSpecimens($pref_specimens);
		
		$lab_data = array();
		$tests = explode(",", $_POST['lab-reqs']);
		
		foreach ($tests as $l) {
			$lab_data[] = (new LabDAO())->getLab($l, true, $pdo);
		}
		$request->setRequestData($lab_data);
		$newLabRequest = (new PatientLabDAO())->newPatientLabRequest($request, false, $pdo);
		if ($newLabRequest == null) {
			$pdo->rollBack();
			exit("error:Failed to create the lab request(s)");
		}
		$labRequestsData[] = $newLabRequest;
	}
	$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('arv')->setDescription($_POST['comments'])->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user);
	
	if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
		$pdo->rollBack();
		exit("error:Failed to save Assessment Comment");
	} else {
		$arvConsultation->setComment($_POST['comments']);
	}
	
	if (!is_blank($_POST['nextAppointmentDate'])) {
		$ag = new AppointmentGroup();
		$ag->setCreator($this_user);
		$ag->setDepartment($this_user->getDepartment());
		$ag->setType("Visit");
		$ag->setIsAllDay(true);
		$ag->setResource(null);
		$ag->setDescription("ARV Visit Appointment");
		$ag->setPatient(new PatientDemograph($patObj->getId()));
		$apps = $appInvs = [];
		
		$app = new Appointment();
		$app->setEditor($this_user);
		$app->setStartTime($_POST['nextAppointmentDate']);
		$app->setEndTime(null);
		$apps[] = $app;
		
		$staffs = [];
		
		$ag->setAppointments($apps);
		$ag->setInvitees($appInvs);
		
		$appGroup = (new AppointmentGroupDAO())->add($ag, $pdo);
		if (!$appGroup) {
			$pdo->rollBack();
			exit("error:Appointment exists for the Selected Date");
		}
		$arvConsultation->setNextAppointment($_POST['nextAppointmentDate']);
	}
	$arvConsultation->setData($arvConsultationData);
	
	if ((new ArvConsultingDAO())->add($arvConsultation, $pdo) !== null) {
		$pdo->commit();
		exit("success:Saved Data successfully");
	} else {
		$pdo->rollBack();
		exit("error:Something happened somewhere.");
	}
}
?>

<section style="width: 800px">
	<p>Add Data for <?= $patient->getFullname() ?> <span class="pull-right"></span></p>
	<form id="arvWizardFrm" autocomplete="off" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: pot1, onComplete: pot2})">
		<?php $H = $HISTORY[0]; ?>
		<fieldset>
			<legend><?= $H->getTemplate()->getLabel() ?></legend>
			<?php foreach ((new ArvHistoryTemplateDataDAO())->byTemplate($H->getTemplate()->getId()) as $item) {
				//$item=new ArvHistoryTemplateData();?>
				<label><?= $item->getLabel() ?><?= $item->renderType("history_data[" . $H->getId() . "][" . $item->getId() . "]") ?></label><?php } ?>
		</fieldset>
		<fieldset>
			<legend>Other OIs/Problems</legend>
			<div class="block">
				<button type="button" class="action" onclick="add_diagnosis_data()"> add</button>
				<button type="button" class="action" onclick="remove_diagnosis_data()"> remove</button>
			</div>
			<label>Diagnosis Data
				<span class="pull-right"><label style="display: inline;"><input type="radio" checked="checked" name="type_diagnosis" value="icd10">ICD10</label> <label style="display: inline;"><input type="radio" name="type_diagnosis" value="icpc-2">ICPC-2</label>  </span></label>
			<div class="diagnosis row-fluid">
            <span>
                <input type="hidden" name="cases[]" class="span3">

                <select style="display: inline-block;" name="states[]" class="span3">
                    <option value="query">Query</option>
                    <option value="differential">Differential</option>
                    <option value="confirmed">Confirmed</option></select>
                <select name="severity[]" class="span3">
                    <?php foreach ($severities as $s) { ?>
	                    <option value="<?= $s ?>"><?= ucwords($s) ?></option><?php } ?>
                </select>
                <input type="text" class="span3" name="comment[]" placeholder="Comment" style="margin-left: 2%;">
            </span>
			</div>

			<label>
				Diagnosis Note:
				<textarea name="diagnosisNote" cols="40" rows="2"></textarea>
			</label>
			<?php
			$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
			$pageSize = 10;
			
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
			$type = 'confirmed';
			$active = 'true';
			$severity = 'chronic';
			$data = (new PatientDiagnosisDAO())->one($patient->getId(), $type, $active, $severity, $page, $pageSize);
			if (count($data->data) == 0) {
				?>
				<div class="notify-bar">No diagnoses available</div> <?php } else {
				$totalSearch = $data->total;
				?>
				<table class="table">
					<thead>
					<tr>
						<th>Date</th>
						<th>Diagnosis</th>
						<th>Type</th>
						<th>Status</th>
						<th>By</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($data->data as $diagnosis) {//$diagnosis=new PatientDiagnosis();?>
						<tr>
							<td><?= date("Y M,d h:ia", strtotime($diagnosis->date_of_entry)) ?></td>
							<td><?= strtoupper($diagnosis->diagnosisType) ?> (<?= trim($diagnosis->code) ?>): <?= $diagnosis->case ?></td>
							<td><?= ucwords($diagnosis->_status) ?></td>
							<td><?= ($diagnosis->active ? 'Active' : 'Resolved') ?></td>
							<td><?= $diagnosis->username ?></td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php } ?>
		</fieldset>
		<fieldset>
			<legend>ARV Drugs</legend>
			<div class="btn-block">
				<button class="btn-small1 action pull-right" type="button" onclick="_clearArvDrugs(_showArvDrugs);"> Clear</button>
				<span class="pull-right">&nbsp;</span>
				<button class="btn-small1 action pull-right" type="button" onclick="Boxy.load('/arvMobile/web/tab/dialogs/new_drug.php', {afterHide: function(){_showArvDrugs()}})"> Add</button>
			</div>
			<hr>
			<input type="hidden" name="arv_drug_data" id="arv_drug_data">
			<table class="table table-bordered table-striped" id="arvDrugsList">
				<thead>
				<tr class="menu-head">
					<th>Line</th>
					<th>Drug</th>
					<th>Dose</th>
					<th width="10%">*</th>
				</tr>
				</thead>
				<tbody></tbody>
			</table>

		</fieldset>
		<fieldset>
			<?php $H = $HISTORY[1]; ?>
			<legend><?= $H->getTemplate()->getLabel() ?></legend>
			<?php foreach ((new ArvHistoryTemplateDataDAO())->byTemplate($H->getTemplate()->getId()) as $item) {
				//$item=new ArvHistoryTemplateData();?>
				<label><?= $item->getLabel() ?><?= $item->renderType("lab_data[" . $H->getId() . "][" . $item->getId() . "]") ?></label>
			<?php } ?>
			<label>Other Labs: <span class="fadedText">(Request)</span> </label>
			<label><input type="hidden" id="labs_to_request" name="lab-reqs"></label>
			<label></label>
			<label>Preferred Specimen(s) <select multiple="multiple" name="specimen_ids[]">
					<?php foreach ($specimens as $s) {
						echo '<option value="' . $s->getId() . '">' . $s->getName() . '</option>';
					} ?>
				</select></label>
		</fieldset>
		<fieldset>
			<legend>General Comments</legend>
			<label><span class="required">*</span>General Comments <textarea required name="comments" placeholder="General Comments"></textarea></label>
		</fieldset>
		<fieldset>
			<legend>Next Appointment</legend>
			<label>Appointment in the next: <span class="pull-right">Eg: 4 weeks</span></label>
			<div class="row-fluid">
				<label class="span4"><input type="number" min="0" name="frequency" placeholder="example: 2" required="required"></label>
				<label class="span8"><select name="interval" required="required">
						<option value=""> --Select--</option>
						<option value="days">Day(s)</option>
						<option value="weeks">Week(s)</option>
						<option value="months">Month(s)</option>
					</select>
				</label>
			</div>
			<div class="row-fluid">
				<label id="actualDateDsp" class="span12 border"></label>
				<input id="actualDate" name="nextAppointmentDate" type="hidden">
			</div>
		</fieldset>
		<div class="clear"></div>
		<div class="btn-group">
			<button class="btn" type="submit" id="saveWizardBtn">Save</button>
		</div>

		<input type="hidden" name="pid" value="<?= $patient->getId() ?>">
	</form>
</section>
<script type="text/javascript">
	var $Form = $('#arvWizardFrm');
	$Form.formToWizard({
		submitButton: 'saveWizardBtn',
		showProgress: true, //default value for showProgress is also true
		nextBtnName: 'Next',
		prevBtnName: 'Previous',
		showStepNo: true
	});
	$(document).on('click', '[data-idx]', function (e) {
		var id = $(this).data("idx");
		if (!e.handled) {
			var _arvDrugs = $('#arv_drug_data').val();
			if (_arvDrugs != "") {
				_arvDrugs = $.parseJSON(_arvDrugs);
				_arvDrugs.splice(id, 1);
				$("#arv_drug_data").val(JSON.stringify(_arvDrugs));
			}
			setTimeout(function () {
				_showArvDrugs();
			}, 5);

			e.handled = true;
		}
	}).on('change', 'input[name="frequency"], select[name="interval"]', function (e) {
		if ($('input[name="frequency"]').val() !== "" && $('select[name="interval"]').val() !== "") {
			$('#actualDateDsp').html('<span>' + moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('MMMM Do YYYY') + '<span>');
			$('#actualDate').val(moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('YYYY-MM-DD'));
		} else {
			$('#actualDate, #actualDateDsp').html('');
		}
	}).ready(function () {
		$('.boxy-content #labs_to_request').select2({
			placeholder: "Search and select lab",
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

		}).change(function () {
			var total = 0;
			$.each($(this).select2("data"), function () {
				total = parseFloat(this.basePrice) + total;
			});
			$("form label.output").html("Estimated Test cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>");
		});
	});

	var pot1 = function () {
		jQuery('input[name*="history_data"]').filter(function () {
			return !this.value;
		}).attr('disabled', 'disabled');
		$('section > p > span').html('Saving...');
	};

	var pot2 = function (data) {
		var state = data.split(":");
		if (state[0] === "error") {
			jQuery('input[name*="history_data"]').filter(function () {
				return !this.value;
			}).removeAttr('disabled');
			$('section > p > span').html('<img src="/assets/alert/error.png"> ' + state[1]);
		} else if (state[0] === "success") {
			$('section > p > span').html('<img src="/assets/alert/success.png"> ' + state[1]);
			setTimeout(function () {
				Boxy.get($(".close")).hideAndUnload();
				setTimeout(function () {
					Boxy.get($(".close")).hideAndUnload();
					showMedicalHistory();
				}, 500);
			}, 1500);
		}
	};

	setTimeout(function () {
		$('.boxy-content [name="states[]"]').select2({/*width:'29%'*/});
		$('.boxy-content [name="severity[]"]').select2({/*width:'29%'*/});
	}, 5);
	$(".boxy-content input[name='cases[]']").select2({
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

	function add_diagnosis_data() {
		$("div.diagnosis:last").after('<div class="diagnosis row-fluid"><span><input type="hidden" name="cases[]" class="span3"><select style="display: inline-block;" name="states[]" class="span3"><option value="query">Query</option><option value="differential">Differential</option><option value="confirmed">Confirmed</option></select><select name="severity[]" class="span3"><?php foreach ($severities as $s) {?><option value="<?=$s?>"><?= ucwords($s)?></option><?php }?></select><input type="text" class="span3" name="comment[]" placeholder="Comment" style="margin-left: 2%;"></span></div>');
		$('.boxy-content div.diagnosis:last [name="cases[]"]').select2({
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
		$('.boxy-content div.diagnosis:last [name="states[]"]').select2({/*width:'29%'*/});
		$('.boxy-content div.diagnosis:last [name="severity[]"]').select2({/*width:'29%'*/});
	}

	function remove_diagnosis_data() {
		if ($("div.diagnosis").length > 1) {
			$("div.diagnosis:last").remove();
		}
	}

	function _showArvDrugs() {
		var _arvDrugs = $('#arv_drug_data').val();
		if (_arvDrugs != "") {
			_arvDrugs = $.parseJSON(_arvDrugs);
		} else {
			_arvDrugs = [];
		}
		var html = '';
		$.each(_arvDrugs, function (i, obj) {
			html += '<tr><td>' + obj.type + '</td><td>' + obj.drug.name + '</td><td>' + obj.dose + '</td><td><a class="action" href="javascript:" data-idx="' + i + '"> Remove </a></td></tr>';
		});
		$('#arvDrugsList').find('tbody').html(html);
	}

	function _clearArvDrugs(callback) {
		$('#arv_drug_data').val('');
		setTimeout(function () {
			if (typeof callback === "function") {
				callback();
			}
		}, 5);

	}


</script>