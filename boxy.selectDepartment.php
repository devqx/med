<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 9/16/15
 * Time: 4:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';

$currency = (new CurrencyDAO())->getDefault();

$referrals = (new ReferralDAO())->all(0, 5000);
$iClass = new Encounter();
$requireSpecialty = $iClass::$requireSpecialty;
$sourceId = (new BillSourceDAO())->findSourceById(3)->getId();
$_consultations = (new BillDAO())->getBillsBySourceForPatient( $sourceId, $_GET['pid'] );
$_consultation = end($_consultations);

$consultation = ($_consultation !== NULL && $_consultation !==FALSE ? (new StaffSpecializationDAO())->getSpecializationByTitle( str_replace("FollowUp ","", ( str_replace("Consultancy charges: ","", $_consultation->getDescription()) ))  ) : NULL);

$depts = (new DepartmentDAO())->getDepartments();

// get the patient previous encounter/s with Dr if any

$prev_encounter = (new EncounterDAO())->forPatient($_GET['pid']);
$prevData = $prev_encounter->data;

if($_POST){
	if(empty($_POST['did'])){
		exit('error:Please select a department');
	}
	exit('ok:'.$_POST['did']);
}
?>
<div style="width:650px;">
	<span class="output"></span>
	<div class="notify-bar">Outstanding Balance: <span><?= $currency ?></span><?= (new PatientDemographDAO())->getPatient($_GET['pid'], FALSE)->getOutstanding()?></div>
	<form id="form_KYK" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : _start, 'onComplete' : _done});">
		<label>Select Department <select name="did" id="department_id" required="required">
				<!--<option value="">All Departments</option>-->
				<?php foreach($depts as $dept){?>
					<option value="<?=$dept->getId()?>"><?=$dept->getName()?></option>
				<?php } ?>
			</select></label>

		<?php
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubscribedDoctorDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
		$all_specialty = (new StaffSpecializationDAO())->getSpecializations(NULL);
		?>
		<label class="pull-right">
			<input type="checkbox" onchange="refreshSubscribersList(this)"> Show only subscribed consultants </label>
		<label>
			Who do you want to see?
			<select name="room_id" data-placeholder="select a specialty" class="srm" required="required">
				<option value=""></option>
				<?php foreach ($all_specialty as $_) {
					$subscribed =  (new SubscribedDoctorDAO())->getSubscriptionsBySpecialty($_->getId(), NULL);
					$subscribed_staff = count($subscribed)!= 0 ? ' data-subscribed="true"' : ' data-subscribed="false"'; ?>
					<option value="<?= $_->getId() ?>"<?=$subscribed_staff ?>><?= $_->getName() ?></option>
				<?php } ?>
			</select>
		</label>
		<label class="pull-left-">
			<select name="checkin_type">
				<option value="">Regular Encounter</option>
				<option value="followUp">Follow Up Encounter</option>
				<option value="review">Investigation Review</option>
			</select>
		</label>
		<label>Referred By <select name="referrer_id" data-placeholder="Select referring entity where applicable">
				<option></option>
				<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
					<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
					)</option><?php } ?>
			</select> </label>

		<div class="clear notice alert-box">
			<ul>
				<?php if(!count($prevData) == 0){ ?>
					Seen by:
				<?php foreach ($prevData as  $enc) { ?>
				<li>  <?= $enc->specialization_->getName() ?>  on <?=  date( 'Y/m/d g:iA', strtotime($enc->start_date)) ?></li>
				<?php } ?>
				<?php } else { ?>
					Not seen a consultant yet
			<?php	} ?>
			</ul>
		</div>
		<div class="clear"></div>
		<div class="btn-block">
			<button type="submit" class="btn">Check In &raquo;</button>
			<button type="button" class="btn-link" onclick="Boxy.get($('.close')).hideAndUnload()">Cancel</button>
		</div>
		<input name="qid" value="<?= $_GET['qid'] ?>" type="hidden">
		<input name="pid" value="<?= $_GET['pid'] ?>" type="hidden">
	</form>
</div>
<script>
function _start(){
	$(".output").html('').removeClass('alert-error');
}
function refreshSubscribersList(s){
	if(s.checked){
		$('.srm').find('option[data-subscribed="false"]').attr('disabled', 'disabled');
	}	else{
		$('.srm').find('option[data-subscribed="false"]').prop('disabled', false);
	}
	$('.srm').select2("val", "");
}
function _done(x){
	if(x.split(':')[0]==='ok'){
		$.post('/check_in.php', $('#form_KYK').serialize(), function (s) {
			if (s.status) {
				location.reload()
			} else {
				Boxy.alert("Failed to check in patient");
			}
		}, 'json').error(function(y) {
			console.log(y);
			if(_.isPlainObject(y)){
				var msg = y.responseText.split(":")[1];
				Boxy.alert(msg);
			} else {
				Boxy.alert("Sorry, failed to check the patient in; <br>You can try to check the patient in from the <a href='/messaging/menu_up.php?type=appointmentlist'>appointment</a> page");
			}
		});
	} else {
		$(".output").html(x.split(':')[1]).addClass('alert-error');
	}
}
</script>