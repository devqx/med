<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/23/15
 * Time: 5:20 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDiagnosis.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Death.php';
	
	if (is_blank($_POST['patient_id'])) {
		exit("error:Select patients name");
	}
	if (is_blank($_POST['time_of_death'])) {
		exit("error:Pick the time of death");
	}
	$pat = (new PatientDemographDAO())->getPatient($_POST['patient_id'], false, null, true);
	if ($pat == null) {
		exit("error:Patient does not exist or is inactive");
	}
	if (is_blank($_POST['pri_case'])) {
		exit("error:Select primary cause of death");
	}
	$dd = new Death();
	$dd->setAgeAtDeath(date_diff(date_create($pat->getDateOfBirth()), date_create('today'))->y);
	$dd->setDeathCausePrimary(new PatientDiagnosis($_POST['pri_case']));
	$dd->setDeathCauseSecondary(is_blank($_POST['sec_case']) ? null : new PatientDiagnosis($_POST['sec_case']));
	$dd->setPatient($pat);
	$dd->setTimeOfDeath($_POST['time_of_death']);
	$dd->setCreateUser(new StaffDirectory($_SESSION['staffID']));
	$saved = (new DeathDAO())->add($dd);
	if ($saved === null) {
		exit("error:Failed to save death certificate");
	} else {
		exit("success:Death certificate created");
	}
}
?>
<section style="width:500px">
	<form enctype="multipart/form-data" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: start, onComplete: finished})">
		<label>Patient: <input type="hidden" id="patient_id" name="patient_id"></label>
		<label>Time of death: <input type="text" name="time_of_death" readonly placeholder="Time of death"></label>
		<label>Primary cause of death:
			<input type="hidden" name="pri_case"></label>
		<label>Secondary cause of death:
			<input type="hidden" name="sec_case"></label>
		<div class="btn-block">
			<button class="btn" type="submit">Create</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script>
	$(document).ready(function () {
		$('#patient_id').select2({
			placeholder: "Search and select patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
			},
			id: function (data) {
				return data.patientId;
			}
		});
	});

	$("input[name='pri_case'], input[name='sec_case']").select2({
		placeholder: "Enter the diagnosis name or ICD-10/ICPC-2 code",
		allowClear: true,
		minimumInputLength: 3,
		width: '100%',
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

	var now = new Date().toISOString().split('T')[0];
	var timeNow = new Date().toISOString().split('T')[1];
	$("input[name='time_of_death']").datetimepicker({
		format: 'Y-m-d H:i:s',
		formatDate: 'Y-m-d H:i:s',
		step: '30',
		onShow: function (ct) {
			this.setOptions({
				maxDate: now
			});
		}
	});

	start = function () {
		$(document).trigger('ajaxSend');
	};
	finished = function (s) {
		$(document).trigger('ajaxStop');
		s = s.trim();
		var response = s.split(":")[1];
		var status = s.split(":")[0];
		if (status === "error") {
			Boxy.alert(response)
		} else {
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(response);
		}
	};
</script>