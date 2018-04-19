<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BodyPartDAO.php";

$body_parts = (new BodyPartDAO())->all();

$severities = getTypeOptions('severity', 'patient_diagnoses');
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role))
	exit ($protect->ACCESS_DENIED);


if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VisitNotes.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDiagnosis.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	$encounter = isset($_POST['enc_id']) ? (new EncounterDAO())->get($_POST['enc_id'], false, $pdo) : null;
	
	$cases = $_POST['cases'];
	$states = $_POST['states'];
	$sevs = $_POST['severity'];
	$comments = $_POST['comment'];
	$body_part = $_POST['body_part'];
	
	if (sizeof($cases) > 0) {
		foreach ($cases as $i => $case) {
			$diagnosis = (new PatientDiagnosis())->setPatient(new PatientDemograph($_POST['pid']))->setEncounter($encounter)->setDiagnosedBy($this_user)->setDiagnosis(new Diagnosis($case))->setNote($comments[$i])->setSeverity($sevs[$i])->setBodyPart($body_part[$i])->setStatus(true)->setType($states[$i]);
			if ((new PatientDiagnosisDAO())->add($diagnosis, $pdo) == null) {
				$pdo->rollBack();
				exit("error:Diagnosis failed to save");
			}
			$summaryNote = trim(ucwords($sevs[$i]) . ' ') . (new DiagnosisDAO())->getDiagnosis($case, $pdo)->getName() . ' (' . ucwords($states[$i]) . ') // ' . $comments[$i];
			$vNote = (new VisitNotes())->setEncounter($encounter)->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('asst')->setDescription(trim($summaryNote))->setDateOfEntry(date('Y-m-d H:i:s'))->setNotedBy($this_user);
			
			if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
				$pdo->rollBack();
				exit("error:Failed to save Diagnoses summary Note");
			}
		}
	}
	
	//diagnosis raw note
	if (!empty($_POST['diagnosisNote'])) {
		$vNote = (new VisitNotes())->setPatient(new PatientDemograph($_POST['pid']))->setNoteType('diag_note')->setDescription($_POST['diagnosisNote'])->setDateOfEntry(date('Y-m-d H:i:s'))->setEncounter($encounter)->setNotedBy($this_user);
		if (!(new VisitNotesDAO())->addNote($vNote, $pdo)) {
			$pdo->rollBack();
			exit("error:Failed to save Diagnoses Note");
		}
	}
	$pdo->commit();
	exit("success:Data Saved");
} ?>
<section style="width: 800px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:start, onComplete: done})">
		<div>
			<span>Diagnosis</span>
			<div class="btn-block">
				<button type="button" class="action" onclick="add_diagnosis_data()"> add</button>
				<button type="button" class="action" onclick="remove_diagnosis_data()"> remove</button>
			</div>
			<div>Diagnosis Data
				<span class="pull-right">
          <label style="display: inline;">
           <input type="radio" checked="checked" name="type_diagnosis" value="icd10">ICD10</label>
          <label style="display: inline;"><input type="radio" name="type_diagnosis" value="icpc-2">ICPC-2</label>
         </span>
			</div>
			<div class="diagnosis row-fluid">
        <span>
            <input type="hidden" name="cases[]" class="span7">

            <select style="display: inline-block;" name="states[]" class="span2">
                <option value="query">Query</option>
                <option value="differential">Differential</option>
                <option value="confirmed">Confirmed</option></select>
            <select name="severity[]" class="span2 hide">
	            <option value="">--</option>
                <?php foreach ($severities as $s) { ?>
	                <option value="<?= $s ?>"><?= ucwords($s) ?></option><?php } ?>
            </select>
	        <select style="display: inline-block;" name="body_part[]" class="span2 hide" data-placeholder="Select the related body part">
							<option value=""></option>
		        <?php foreach ($body_parts as $bp) { ?>
			        <option value="<?= $bp->getId() ?>"><?= $bp->getName() ?></option> <?php } ?>
	        </select>
          <input type="text" class="span3" name="comment[]" placeholder="Comment" style="margin-left: 2%;">
        </span>
			</div>

			<label class="hide">
				Notes (Query/differential diagnosis)
				<textarea name="diagnosisNote"></textarea>
			</label>
			<div>
				<button type="submit" class="btn">Save</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
				<input type="hidden" name="enc_id" value="<?= $_GET['enc_id'] ?>">
			</div>

		</div>
	</form>
</section>


<script type="text/javascript">
	function start() {
		$.event.trigger("ajaxSend");
	}
	function done(s) {
		$.event.trigger("ajaxStop");
		data = s.split(":");
		if (data[0] === "success") {
			showTabs(5);
			Boxy.get($('.close')).hideAndUnload();
		} else {
			Boxy.warn(data[1]);
		}
	}
	setTimeout(function () {
		$('.boxy-content [name="states[]"]').select2();
		$('.boxy-content [name="severity[]"]').select2();
		$('.boxy-content [name="body_part[]"]').select2();
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
		$("div.diagnosis:last").after('<div class="diagnosis row-fluid"><span><input type="hidden" name="cases[]" class="span7"><select style="display: inline-block;" name="states[]" class="span2"><option value="query">Query</option><option value="differential">Differential</option><option value="confirmed">Confirmed</option></select><select name="severity[]" class="span2 hide"><option value="">--</option><?php foreach ($severities as $s) {?><option value="<?=$s?>"><?= ucwords($s)?></option><?php }?></select><select style="display: inline-block;" name="body_part[]" class="span2 hide" placeholder="Select the related body part"><option value=""></option><?php  foreach ($body_parts as $bp){?> <option value="<?= $bp->getId() ?>"><?= $bp->getName() ?></option> <?php }?></select><input type="text" class="span3" name="comment[]" placeholder="Comment" style="margin-left: 2%;"></span></div>');
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
		$('.boxy-content div.diagnosis:last [name="states[]"]').select2();
		$('.boxy-content div.diagnosis:last [name="severity[]"]').select2();
		$('.boxy-content div.diagnosis:last [name="body_part[]"]').select2();
	}
	function remove_diagnosis_data() {
		if ($("div.diagnosis").length > 1) {
			$("div.diagnosis:last").remove();
		}
	}
</script>