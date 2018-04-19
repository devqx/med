<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/3/16
 * Time: 5:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
$serviceCenters = (new ServiceCenterDAO())->all('MedicalReport');
$referrals = (new ReferralDAO())->all();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/utils.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PatientMedicalReport.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/PatientDemograph.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/MedicalExam.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";
	require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/MedicalExamDAO.php";
	if(is_blank($_POST['service_centre_id'])){exit('error:Service center is Required');}
	if(is_blank($_POST['patient_id'])){exit("error:Patient info not available");}
	if(is_blank($_POST['exam_id'])){exit("error:Examination not available");}
	if(is_blank($_POST['request_note'])){exit("error:Request Note not available");}

	$exam = (new MedicalExamDAO())->get($_POST['exam_id']);
	
	$req = (new PatientMedicalReport())->setPatient(new PatientDemograph($_POST['patient_id']))->setExam($exam)->setRequestNote($_POST['request_note'])->setRequestBy(new StaffDirectory($_SESSION['staffID']))->setServiceCenter( (new ServiceCenterDAO())->get($_POST['service_centre_id']) )->add();

	if($req !== null){
		exit("success:Request added successfully");
	}
	exit("error:Failed to save request");
}
?>
<section <?php if(isset($_GET['pid'])){?>style="width:700px"<?php }?>>
	<form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: starting, onComplete: add_exam_request})">
		<?php if(!isset($_GET['pid'])){?><label>Patient <input type="hidden" id="patient_id" name="patient_id"></label><?php } else {?>
			<input type="hidden" name="patient_id" value="<?=$_GET['pid']?>">
		<?php }?>
		<label>Business Unit/Service Center <select required name="service_centre_id" data-placeholder="Service Center">
				<option></option>
				<?php foreach ($serviceCenters as $center){?>
					<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
			</select> </label>
		<label>Medical Exam <input type="hidden" name="exam_id"></label>
		<label>Request Note <textarea name="request_note"></textarea> </label>
		<label>Referral <select name="referral_id" data-placeholder="--Select Referring Person/Company--">
				<option></option>
				<?php foreach ($referrals->data as $referral){?>
					<option value="<?= $referral->getId()?>"><?= $referral->getName() ?></option>
				<?php }?>
			</select> </label>
		<div class="clear"></div>
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="<?php if(!isset($_GET['pid'])){?>resetRequest();<?php }else {?>Boxy.get(this).hideAndUnload();<?php }?>">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('select[name="referral_id"]').select2({width:'100%'});
		$('select[name="service_centre_id"]').select2({width:'100%'});
		$('input[name="exam_id"]').select2({
			placeholder: "Search and select examination",
			minimumInputLength: 0,
			width: '100%',
			allowClear: true,
			ajax: {
				url: "/api/get_medical_exams.php",
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
				return data.name;
			},
			formatSelection: function (data) {
				return data.name;
			}
		});
		$('input[id="patient_id"]').select2({
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
					//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
				},
				formatSelection: function (data) {
					var details = [];
					details.push(data.patientId ? "EMR ID:"+data.patientId : null);
					details.push(data.fname ? data.fname : null);
					details.push(data.mname ? data.mname : null);
					details.push(data.lname ? data.lname : null);
					return implode(" ", details);
					//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
				},
				id: function (data) {
					return data.patientId;
				}
			});

	});

	function starting() {
		$(document).trigger('ajaxSend');
	}

	function resetRequest() {
		/*$('input[name="exam_id"]').select2("val", "");
		$('input[id="patient_id"]').select2("val", "");
		$('select[name="referral_id"]').select2("val", "");*/
		try {$('.mini-tab > .tab:first').get(0).click();}catch(except){}
	}

	function add_exam_request(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(":");
		if (data[0] == "error") {
			Boxy.alert(data[1]);
		} else {
			try {$('.mini-tab > .tab:first').get(0).click();}catch(except){}
			try {Boxy.get($('.close')).hideAndUnload();}catch(except){}
		}
	}
</script>