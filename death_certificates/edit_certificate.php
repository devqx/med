<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/20/17
 * Time: 10:37 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Death.php';
$certificate = (new DeathDAO())->get($_GET['id']);
if ($_POST) {
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
	
	$update = (new DeathDAO())->get(base64_decode($_POST['id']))
		->setPatient( new PatientDemograph($_POST['patient_id']) )
		->setDeathCausePrimary(!is_blank($_POST['pri_case'])?new Diagnosis($_POST['pri_case']):NULL)
		->setDeathCauseSecondary(!is_blank($_POST['sec_case'])?new Diagnosis($_POST['sec_case']):NULL)
		->setTimeOfDeath($_POST['time_of_death'])->update();
	
	if($update !== null){
		exit('success:Certificate updated');
	}
	exit('error:Failed to update certificate');
}
?>
<section style="width:500px">
	<form enctype="multipart/form-data" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: start_Edit, onComplete: finished_Edit})">
		<label>Patient: <input type="hidden" id="patient_id" name="patient_id" value="<?= $certificate->getPatient()->getId()?>"></label>
		<label>Time of death: <input type="text" name="time_of_death" readonly placeholder="Time of death" value="<?= $certificate->getTimeOfDeath()?>"></label>
		<label>Primary cause of death:
			<input type="hidden" name="pri_case" value="<?= $certificate->getDeathCausePrimary() ? $certificate->getDeathCausePrimary()->getId() : '' ?>"></label>
		<label>Secondary cause of death:
			<input type="hidden" name="sec_case" value="<?= $certificate->getDeathCauseSecondary() ? $certificate->getDeathCauseSecondary()->getId() : '' ?>"></label>
		<div class="btn-block">
			<input type="hidden" name="id" value="<?= base64_encode($certificate->getId())?>">
			<button class="btn" type="submit">Update</button>
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
				return data.fullname + " (" + data.id + " [" + data.lid + "]), Phone: " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + data.id + " [" + data.lid + "])";
			},
			id: function (data) {
				return data.patientId;
			},
			initSelection: function (element, callback) {
				var pid = $("#patient_id").val();
				if (pid.trim() !== "") {
					$.ajax("/api/search_patients.php", {
						data: {pid: pid, asArray: true},
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
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
		},
		formatSelection: function (data) {
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
		},
		initSelection: function (element, callback) {
			var did = $(element).val();
			if (did.trim() !== "") {
				$.ajax("/api/get_diagnoses.php", {
					data: {id: did, single: true},
					dataType: "json"
				}).done(function (data) {
					callback(data);
				});
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

	start_Edit = function () {
		$(document).trigger('ajaxSend');
	};
	finished_Edit = function (s) {
		$(document).trigger('ajaxStop');
		s = s.trim();
		var response = s.split(":")[1];
		var status = s.split(":")[0];
		if (status === "error") {
			Boxy.alert(response)
		} else {
			Boxy.get($(".close")).hideAndUnload();
			Boxy.info(response);
			reloadThisPage();
		}
	};
</script>
