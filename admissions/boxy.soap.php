<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/18/16
 * Time: 3:02 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/func.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BodyPartDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$bodyparts = (new BodyPartDAO())->all(null);
$serviceCenters = (new ServiceCenterDAO())->all();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role)) exit ($protect->ACCESS_DENIED);
$severities = getTypeOptions('severity', 'patient_diagnoses');

sessionExpired();
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.labs.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProgressNoteDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProgressNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDiagnosis.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Diagnosis.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DiagnosisDAO.php';

$templates = (new ExamTemplateDAO())->all();
$specs = (new StaffSpecializationDAO())->getIpSpecializations();
$instance = (new InPatientDAO())->getInPatient($_GET['aid']);

//usort($specs, function ($item1, $item2){
//	$ipConsultationId = 18;
//	if($item1->getId() != $ipConsultationId && $item2->getId() == $ipConsultationId){
//		return 1;
//	} elseif ($item1->getId() == $ipConsultationId && $item2->getId() != $ipConsultationId){
//		return -1;
//	} else {
//		return $item1->getId() -$item2->getId();
//	}
//});

if ($_POST) {
	ob_end_clean();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$return = array();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Prescription.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientSystemsReview.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/SystemsReview.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysicalExamination.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientPhysicalExam.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSystemsReviewDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SystemsReviewDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysicalExaminationDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientPhysicalExamDAO.php';
	$patObj = (new PatientDemographDAO())->getPatient($_POST['pid'], FALSE, $pdo, null);

	if (isset($_POST['specialization_id']) && !is_blank($_POST['specialization_id'])) {
		if(is_blank($_POST['service_centre_id'])){
			$pdo->rollBack();
			exit('error:Service Center is Required when InPatient consultation is billed');
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
		$specialty = (new StaffSpecializationDAO())->get($_POST['specialization_id'], $pdo);
		if (isset($_POST['follow_up'])) {
			$price = (new InsuranceItemsCostDAO())->getItemFollowUpPriceByCode($specialty->getCode(), $_POST['pid'], TRUE, $pdo);
		} else {
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($specialty->getCode(), $_POST['pid'], TRUE, $pdo);
		}
		$pat = (new PatientDemographDAO())->getPatient($instance->getPatient()->getId(), FALSE, $pdo, null);
		$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE, $pdo);
		$bil = new Bill();
		$bil->setPatient($pat);
		$bil->setDescription("In-Patient Consultation: " . $specialty->getName());
		$bil->setItem($specialty);
		$bil->setSource((new BillSourceDAO())->findSourceById(5, $pdo));
		$bil->setSubSource((new BillSourceDAO())->findSourceById(3, $pdo));
		$bil->setTransactionType("credit");
		$bil->setAmount($price);
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($pat->getScheme());
		$bil->setCostCentre((new ServiceCenterDAO())->get($_POST['service_centre_id'], $pdo)->getCostCentre());
		if ((new BillDAO())->addBill($bil, 1, $pdo, $_POST['aid']) == null) {
			$pdo->rollBack();
			ob_end_clean();
			exit("error:Failed to add a charge [in-patient consultancy]");
		}

	}
	//subjective note
	if (!empty($_POST['subjective_note'])) {
		$pNote = new ProgressNote();
		$pNote->setInPatient(new InPatient($_POST['aid']));
		$pNote->setValue(null);
		$pNote->setNote($_POST['subjective_note']);
		$pNote->setNoteType('subj');
		$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
		if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
			$pdo->rollBack();
			ob_end_clean();
			exit("error:Can't save subjective note");
		}
	}

	//systems review
	$systems_reviews = array();
	if (!is_blank(@$_POST['system_review'])) {
		foreach (@$_POST['system_review'] as $systems_review) {
			if (!empty($systems_review)) {
				$systems_reviews[] = (new SystemsReviewDAO())->get($systems_review, $pdo);
				$review = new PatientSystemsReview();

				$review->setDate(date("Y-m-d H:i:s"));
				$review->setPatient(new PatientDemograph($_POST['pid']));
				$review->setReviewer(new StaffDirectory($_SESSION['staffID']));
				$review->setSystemsReview((new SystemsReviewDAO())->get($systems_review, $pdo));
				if ((new PatientSystemsReviewDAO())->add($review, $pdo) == null) {
					$pdo->rollBack();
					ob_end_clean();
					exit("error:Couldn't save the systems review");
				}
			}
		}
		unset($systems_review);
	}

	if (sizeof($systems_reviews) > 0) {
		$sort_systems_reviews = array();
		foreach ($systems_reviews as $s) {
			$sort_systems_reviews[$s->getCategory()->getId()][] = $s;
		}
		unset($s);

		foreach ($sort_systems_reviews as $sort_sr) {
			$system_review = array();
			for ($i = 0; $i < count($sort_sr); $i++) {
				$system_review[] = $sort_sr[$i]->getName() . " (" . $sort_sr[$i]->getCategory()->getName() . ")";
			}

			$pNote = new ProgressNote();
			$pNote->setInPatient(new InPatient($_POST['aid']));
			$pNote->setValue(null);
			$pNote->setNote(implode(', ', $system_review));
			$pNote->setNoteType('revw');
			$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
			if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
				$pdo->rollBack();
				ob_end_clean();
				exit("error:Failed to save systems review summary");
			}
		}
		unset($sort_sr);
	}

	//physical exams
	$physical_exams = array();
	if (!is_blank(@$_POST['physical_exam'])) {
		foreach (@$_POST['physical_exam'] as $physical_exam) {
			if (!empty($physical_exam)) {
				$physical_exams[] = (new PhysicalExaminationDAO())->get($physical_exam, $pdo);
				$review = new PatientPhysicalExam();

				$review->setDate(date("Y-m-d H:i:s"));
				$review->setPatient(new PatientDemograph($_POST['pid']));
				$review->setReviewer(new StaffDirectory($_SESSION['staffID']));
				$review->setPhysicalExamination((new PhysicalExaminationDAO())->get($physical_exam, $pdo));
				if ((new PatientPhysicalExamDAO())->add($review, $pdo) == null) {
					$pdo->rollBack();
					ob_end_clean();
					exit("error:Physical exam failed to save");
				}
			}
		}
		unset($physical_exam);
	}

	if (sizeof($physical_exams) > 0) {
		$sort_physical_exams = array();
		foreach ($physical_exams as $s) {
			$sort_physical_exams[$s->getCategory()->getId()][] = $s;
		}
		unset($s);

		foreach ($sort_physical_exams as $sort_sr) {
			$physical_exam_ = array();
			for ($i = 0; $i < count($sort_sr); $i++) {
				$physical_exam_[] = $sort_sr[$i]->getName() . " (" . $sort_sr[$i]->getCategory()->getName() . ")";
			}
			$pNote = new ProgressNote();
			$pNote->setInPatient(new InPatient($_POST['aid']));
			$pNote->setValue(null);
			$pNote->setNote(implode(', ', $physical_exam_));
			$pNote->setNoteType('ph_ex');
			$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
			if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
				$pdo->rollBack();
				ob_end_clean();
				exit("error:Physical exam summary failed to save");
			}
		}
		unset($sort_sr);
	}

	//plan note
	if (!empty($_POST['plan_note'])) {
		$pNote = new ProgressNote();
		$pNote->setInPatient(new InPatient($_POST['aid']));
		$pNote->setValue(null);
		$pNote->setNote($_POST['plan_note']);
		$pNote->setNoteType('plan');
		$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
		if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
			$pdo->rollBack();
			ob_end_clean();
			exit("error:Plan note failed to save");
		}
	}
	//exam note
	if (!empty($_POST['exam_note'])) {
		$pNote = new ProgressNote();
		$pNote->setInPatient(new InPatient($_POST['aid']));
		$pNote->setValue(null);
		$pNote->setNote($_POST['exam_note']);
		$pNote->setNoteType('exam');
		$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
		if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
			$pdo->rollBack();
			ob_end_clean();
			exit("error:Plan note failed to save");
		}
	}

	//actual diagnoses
	$cases = $_POST['cases'];
	$states = $_POST['states'];
	$sevs = $_POST['severity'];
	$bdy = @$_POST['body_part'];
	$comments = $_POST['d_comment'];
	if (sizeof($cases) > 0) {
		foreach ($cases as $i => $case) {
			if (!is_blank($case)) {
				$diagnosis = (new PatientDiagnosis())->setInPatient($instance)->setPatient(new PatientDemograph($_POST['pid']))->setDiagnosedBy($this_user)->setDiagnosis(new Diagnosis($case))->setNote($comments[$i])->setSeverity($sevs[$i])->setBodyPart($bdy[$i])->setStatus(TRUE)->setType($states[$i]);

				if ((new PatientDiagnosisDAO())->add($diagnosis, $pdo) == null) {
					$pdo->rollBack();
					ob_end_clean();
					exit("error:Diagnosis failed to save");
				} else {
					$summaryNote = trim(ucwords($sevs[$i]) . ' ') . (new DiagnosisDAO())->getDiagnosis($case, $pdo)->getName() . ' (' . ucwords($states[$i]) . ')'. ' <span class=fadedText>'.$comments[$i].'</span>' ;

					$pNote = new ProgressNote();
					$pNote->setInPatient(new InPatient($_POST['aid']));
					$pNote->setValue(null);
					$pNote->setNote($summaryNote);
					$pNote->setNoteType('diag_note');
					$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
					if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
						$pdo->rollBack();
						ob_end_clean();
						exit("error:Diagnosis Summary failed to save");
					}
				}
			}
		}
	}

	//diagnosis raw note
	if (!empty($_POST['diagnosisNote'])) {
		$pNote = new ProgressNote();
		$pNote->setInPatient(new InPatient($_POST['aid']));
		$pNote->setValue(null);
		$pNote->setNote($_POST['diagnosisNote']);
		$pNote->setNoteType('diag_note');
		$pNote->setNotedBy(new StaffDirectory($_SESSION['staffID']));
		if ((new ProgressNoteDAO())->add($pNote, $pdo) == null) {
			$pdo->rollBack();
			ob_end_clean();
			exit("error:Diagnosis note failed to save");
		}
	}
	@ob_end_clean();
	$pdo->commit();
	exit("success:Everything has saved");
}

?>
<script type="text/javascript">
	function begin(form) {
		showPinBox(function () {
			$.ajax({
				url: form.action,
				data: $(form).serialize(),
				type: "POST",
				beforeSend: begin2,
				complete: function (xhr, status) {
					ended(xhr.responseText);
				}
			});
		});
		return false;
	}
	function begin2() {
	}
	function ended(data) {
		var ret = data.split(":");
		if (ret[0] === "error") {
			Boxy.warn(ret[1]);
		} else if (ret[0] === "success") {
			Boxy.get($('.close')).hideAndUnload();
			showTabs(2);
		} else {
			Boxy.alert("Something went wrong");
		}
	}

</script>
<section style="width: 900px;">

	<form name="soapForm" method="post" id="soapForm" action="<?= $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return begin(this)">
		<!--    Current complaints-->
		<fieldset>
			<legend>Current Complaints</legend>
			<div>
				Current Complaints:
				<textarea name="subjective_note" placeholder="Type here ..." rows="4"></textarea>
			</div>

		</fieldset>

		<!--Review of systems-->
		<fieldset>
			<legend>Review Of Systems</legend>
			<?php include_once $_SERVER['DOCUMENT_ROOT'] . "/boxy.system_review.php"; ?>

		</fieldset>
		<!-- Physical Examination -->

		<!--Physical Examination Summary -->
		<fieldset>
			<legend>Physical Examination Summary</legend>
			<label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_help.php">help</a>
					<!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>--> | <i
						class="icon-plus-sign"></i><a href="javascript:;" class="exam_template_link" data-href="template_new.php">add to list</a></span>
				<select name="template_id" id="template_id" data-placeholder="Select Custom Text Templates">
					<option></option>
					<?php foreach ($templates as $t) {//$t=new ExamTemplate()?>
						<option value="<?= $t->getId() ?>" data-text="<?= ($t->getContent()) ?>"><?= $t->getCategory()->getName() ?>
						: <?= $t->getTitle() ?></option><?php } ?>
				</select>
			</label>
			<label>Exam Note:
				<textarea rows="4" name="exam_note" placeholder="type here ..."></textarea>
			</label>
		</fieldset>

		<!-- Diagnosis -->
		<fieldset>
			<legend>Diagnosis</legend>
			<div class="row-fluid">
				<div class="span7">
			<div class="block">
				<button type="button" class="action" onclick="add_diagnosis_data()"><i class="icon-plus-sign"></i> add</button>
				<button type="button" class="action" onclick="remove_diagnosis_data()"><i class="icon-minus-sign"></i> remove
				</button>
			</div>
			<label>Diagnosis Data
				<span class="pull-right"><label style="display: inline;"><input type="radio" checked="checked" name="type_diagnosis" value="icd10">ICD10</label> <label style="display: inline;"><input type="radio" name="type_diagnosis" value="icpc-2">ICPC-2</label>  </span>
			</label>
			<div class="diagnosis row-fluid">
            <span>
                <input type="hidden" name="cases[]" class="span7">

                <select style="display: inline-block;" name="states[]" class="span2">
                    <option value="query">Query</option>
                    <option value="differential">Differential</option>
                    <option value="confirmed">Confirmed</option></select>
                <select name="severity[]" class="span2 hide">
	                <option></option>
                    <?php foreach ($severities as $s) { ?>
	                    <option value="<?= $s ?>"><?= ucwords($s) ?></option><?php } ?>
                </select>
                <input type="text" class="span3" name="d_comment[]" placeholder="Comment" style="margin-left: 2%;">
            </span>
			</div>

			<label class="hide">
				Diagnosis Note:
				<textarea name="diagnosisNote" cols="40" rows="2"></textarea>
			</label>
				</div>
			<!--existing diagnoses area-->
			<div class="span5 overscrollLabDiv" style="height:300px;min-height:300px">
				<span class="pull-left fadedText">Existing Diagnoses</span>
				<a style="margin-bottom:5px" class="pull-right action" href="javascript:" title="Clear selected previous diagnoses" id="reset_diagnosis_for_encounter"></a>
				<ul style="list-style-type: none;margin:0">
					<?php
					$page = 0;
					$pageSize = 9999;
					
					require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
					$type = null;//'confirmed';
					$active = 'true';
					$severity = null; //acute should show when none was specified?
					$data = (new PatientDiagnosisDAO())->one($_GET['pid'], $type, $active, $severity, $page, $pageSize);
					foreach($data->data as $diagnosis){//$diagnosis=new PatientDiagnosis();?>
						<li class="row-fluid" style="display: inline-block;
    background: #FEFEFE;
    border: 2px solid #FAFAFA;
    box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);
    padding: 2px;
    background: -webkit-linear-gradient(45deg, #FFF, #F9F9F9);
    -webkit-transition: all .2s ease;
    -moz-transition: all .2s ease;
    -o-transition: all .2s ease;
    transition: all .2s ease;
   ">
							<label class="span10" for="diag_<?=$diagnosis->id?>" title="Use this previous diagnosis for encounter" style="padding-left:10px"><?= strtoupper($diagnosis->diagnosisType)?> (<?=trim($diagnosis->code)?>): <?=$diagnosis->case?></label>
							<div class="span1"><input title="Use this previous diagnosis for encounter" type="checkbox" name="diagnosis_for_encounter[]" value="<?=$diagnosis->id?>" id="diag_<?=$diagnosis->id?>"> </div>
							<div class="span1"><a title="Resolve diagnoses" href="javascript:;" class="resolveConditionLink2 pull-right" data-pid="<?=$diagnosis->patient_ID?>" data-id="<?=$diagnosis->id?>"><i class="icon icon-remove-sign"></i></a></div>
						</li>
					<?php }?>
				</ul>
			</div>
			<!--/existing diagnoses area-->
			</div>
		</fieldset>

		<!--Plan-->
		<fieldset>
			<legend>Plan</legend>
			<label>Plan:
				<textarea name="plan_note" placeholder="type here ..."></textarea>
			</label>
			<div class="row-fluid">
				<label>Service Center <select name="service_centre_id" data-placeholder="Service Center">
						<option></option>
						<?php foreach ($serviceCenters as $center){?>
							<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
					</select> </label>
			</div>
			<div class="row-fluid">
				<label class="span10">
					Ward Round Consulting Charge:
					<span class="pull-right fadedText">Select appropriate specialty [if any]</span>
					<select name="specialization_id" data-placeholder="Select Doctor's Specialization">
						<option value=""></option>
						<?php foreach ($specs as $spec) {//$spec=new StaffSpecialization();?>
							<option value="<?= $spec->getId() ?>"><?= $spec->getName() ?></option>
						<?php } ?>
					</select>
				</label>
				<label class="span2 no-label pull-right">
					<input type="checkbox" name="follow_up" class=""> Follow-Up
				</label>
			</div>
		</fieldset>
		<input id="SaveAll" type="submit" class="btn" value="Save">
		<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		var $Form = $('#soapForm');
		$Form.formToWizard({
			submitButton: 'SaveAll',
			showProgress: true, //default value for showProgress is also true
			nextBtnName: 'Next',
			prevBtnName: 'Previous',
			showStepNo: true
		});
		setTimeout(function () {
			Boxy.get($(".close")).centerX();
		}, 500);
		setTimeout(function () {
			$('.boxy-content [name="states[]"]').select2({/*width:'29%'*/});
			$('.boxy-content [name="severity[]"]').select2({/*width:'29%'*/});
			$('.boxy-content [name="body_part[]"]').select2();
		}, 5);

		$('#reset_diagnosis_for_encounter').click(function (e) {
			$('input:checkbox:checked[name="diagnosis_for_encounter[]"]').prop('checked', false).iCheck('update');
		});

		$('.resolveConditionLink2').click(function(e){
			if(!e.handled){
				var $this = $(this);
				Boxy.ask('Are you sure to resolve this diagnoses?', ['Yes', 'No'], function(answer){
					if(answer==='Yes'){
						$(document).trigger('ajaxSend');
						$.post('/api/resolve_pre_condition.php', {pid: $($this).data('pid'), id: $($this).data('id')}, function(response){
							$(document).trigger('ajaxStop');
							if(response === true){
								$($this).parent().parent('li.row-fluid').remove();
							} else {
								$.notify2("Sorry, action failed", "error");
							}
						},'json');
					}
				});

				e.handled = true;
			}
		});
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

		$('[name="plan_note"]').summernote(SUMMERNOTE_CONFIG);
		$('textarea[name="exam_note"]').summernote(SUMMERNOTE_CONFIG);
		$('.boxy-content #template_id').select2().change(function (data) {
			if (data.added !== undefined) {
				var content = $(data.added.element).data("text");
				$('textarea[name="exam_note"]').code(content).focus();
			} else {
				$('textarea[name="exam_note"]').code('').focus();
			}
		}).trigger('change');
	});
	
	function add_diagnosis_data() {
		$("div.diagnosis:last").after('<div class="diagnosis row-fluid"><span><input type="hidden" name="cases[]" class="span7"><select style="display: inline-block;" name="states[]" class="span2"><option value="query">Query</option><option value="differential">Differential</option><option value="confirmed">Confirmed</option></select><select name="severity[]" class="span2 hide"><option></option><?php foreach ($severities as $s) {?><option value="<?=$s?>"><?= ucwords($s)?></option><?php }?></select>	             <select style="display: inline-block;" name="body_part[]" class="span2 hide" placeholder="Select body Part"><option value=""></option><?php foreach ($bodyparts as $bp){ ?><option value="<?= $bp->getId() ?>"> <?= $bp->getName() ?></option> <?php } ?></select> <input type="text" class="span3" name="d_comment[]" placeholder="Comment" style="margin-left: 2%;"></span></div>');
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
		$('.boxy-content div.diagnosis:last [name="body_part[]"]').select2({/*width:'29%'*/});
	}

	function remove_diagnosis_data() {
		if ($("div.diagnosis").length > 1) {
			$("div.diagnosis:last").remove();
		}
	}

</script>