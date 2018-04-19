<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/7/14
 * Time: 12:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureSpecialtyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$bills = new Bills();
$procedure = (new PatientProcedureDAO())->get($_GET['id']);
$pat = (new PatientDemographDAO())->getPatient($procedure->getPatient()->getId(), false, null, null);

$specialties = (new ProcedureSpecialtyDAO)->all();// (new StaffSpecializationDAO())->getSpecializations();

$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

$noteTypes = getTypeOptions('note_type', 'patient_procedure_note');

$templates = (new ProcedureTemplateDAO())->all();
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ProcedureSpecialty.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedureNote.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureNoteDAO.php';
	$pNote = new PatientProcedureNote();
	$pNote->setPatientProcedure(new PatientProcedure($_GET['id']));
	
	if (!is_blank($_POST['note'])) {
		$pNote->setNote($_POST['note']);
	} else {
		exit("error:Note is blank");
	}
	
	$pNote->setType($_POST['note_type']);
	
	if (!is_blank($_POST['specialization_id'])) {
		$pNote->setSpecialty( new ProcedureSpecialty($_POST['specialization_id']));
	} else {
		exit("error:Specialty is blank");
	}
	if (!isset($_SESSION)) {
		session_start();
	}
	$pNote->setStaff(new StaffDirectory($_SESSION['staffID']));
	
	if ($selfOwe - $creditLimit > 0) {
		//exit("error:Patient has an outstanding balance");
	}
	
	$newNote = (new PatientProcedureNoteDAO())->addPatientProcedureNote($pNote);
	
	if ($newNote !== null) {
		exit("success:Note saved successfully");
	}
	exit("error:Failed to add note");
}
?>
<section style="width: 850px;">
	<div class="well">
		Patients outstanding is: &#8358;<?= number_format($selfOwe, 2); ?>
	</div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:__start,onComplete:__done})">
		<label>Specialty: <span class="pull-right"><a href="javascript:" id="add_specialty_ob">Add New</a></span>
			<select name="specialization_id" data-placeholder="Examples: Surgeon, Anaesthetic's, ...">
				<option></option>
				<?php foreach ($specialties as $s) { ?>
					<option value="<?= $s->getId() ?>"><?= $s->getName() ?></option>
				<?php } ?>
			</select>
		</label>

		<label>Template <span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="procedure_template_link" data-href="/consulting/template_help.php">help</a>
				<!--| <i class="icon-star-empty"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_add.php">add selected to favorites</a> | <i class="icon-star"></i><a href="javascript:;" class="exam_template_link" data-href="template_fav_delete.php">remove selected from favorites</a>--> | <i class="icon-plus-sign"></i><a href="javascript:;" class="procedure_template_link" data-href="/procedures/dialogs/template_new.php">add to list</a></span>
			<select name="template_id" id="template_id" data-placeholder="Select Custom Text Templates">
				<option></option>
				<?php foreach ($templates as $t) {//$t=new ProcedureTemplate()?>
					<option value="<?= $t->getId() ?>" data-text="<?= ($t->getContent()) ?>"><?= $t->getCategory()->getName() ?></option><?php } ?>
			</select>
		</label>

		<label>Note type<select name="note_type">
				<?php foreach ($noteTypes as $type) { ?>
					<option value="<?= $type ?>"><?= $type ?></option>
				<?php } ?>
			</select></label>
		<label>Note:<textarea name="note" placeholder="procedure notes..."></textarea></label>

		<div class="btn-block">
			<button type="submit" class="btn">Save Note</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	function __start() {
	}
	function __done(s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		} else if (data[0] === "success") {
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
		}
	}
	
	
	function refreshProcedureTemplates() {
		$.ajax({
			url: "/api/get_procedure_templates.php",
			dataType: 'json',
			complete: function (s) {
				var data = s.responseJSON;
				var str = '<option></option>';
				for (var i = 0; i < data.length; i++) {
					str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">' + data[i].category.name + '</option>';
				}
				$('#template_id').html(str);
			}
		});
	}

	$(document).ready(function () {
		$('[name="note"]').summernote(SUMMERNOTE_CONFIG);
		$('.boxy-content #template_id').select2().change(function (data) {
			if (data.added !== undefined) {
				var content = $(data.added.element).data("text");
				$('textarea[name="note"]').code(content).focus();
			} else {
				$('textarea[name="note"]').code('').focus();
			}
		}).trigger('change');

		$('.procedure_template_link').click(function (e) {
			if(!e.handled){
				Boxy.load($(this).data("href"));
				e.handled = true;
			}
		});
		
		$('#add_specialty_ob').click(function (e) {
			if(!e.handled){
				Boxy.load('/procedures/dialogs/config.specialty.new.php', {title:'New Procedure Specialty',afterHide: function () {
					populateSpecialties();
				}});
				e.handled = true;
			}
		})
	});
	
	var populateSpecialties = function () {
		var str = '<option></option>';
		$.getJSON('/api/get_procedure_specialties.php', function (data) {
			_.each(data, function (obj) {
				str += '<option value="' + obj.id + '">' + obj.name + '</option>';
			});
			$('select[name="specialization_id"]').html(str);
		})
	}
</script>