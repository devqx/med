<?php
if (!isset($_SESSION)) {
	@session_start();
}
$pid = (int)$pid;
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ImmunizationEnrollmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
require_once 'InPatientHealthState.php';


$inp_health_state = ( new InPatientHealthState() )->getInPatientHealthState($pid);
$_patient = (new PatientDemographDAO())->getPatient($pid, TRUE, null, null);
$aEnroll = (new AntenatalEnrollmentDAO())->get($pid);
$iEnroll = (new ImmunizationEnrollmentDAO())->getImmunizationEnrollment($pid);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSpecialEventDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';

$eventsCount = (new PatientSpecialEventDAO())->countForPatient($pid);

$admInstances = (new InPatientDAO())->getInPatientInstances($pid, FALSE); ?>

<div class="line fix-me">
	<div class="pull-left">
		<a title="Change Photo" href="javascript:void(0);"><img class="passport" src="<?= $_patient->getPassportPath(); ?>" width="53"/>
		</a>
	</div>
	<div>
		<h4 class="uppercase"><?= $_patient->getFullname() ?></h4>
		<?php $badge = (new InsuranceSchemeDAO())->get($_patient->getScheme()->getId())->getBadge() ?>
		<h6 class="pull-right" title="Badge"><?= $badge ? html_entity_decode( $badge->getIcon() ) : ''?></h6>
		<span class="fadedText" id="pid_"><i class="icon icon-user"></i><a href="/patient_profile.php?id=<?= $_patient->getId() ?>"><?= $_patient->getId() . ($_patient->getLegacyId() === "" ? "" : " (" . $_patient->getLegacyId() . ")") ?> </a></span>
	</div>
	<div></div>

	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
	$alerts_count = count((new PatientAllergensDAO())->forPatient($ARR['patient_ID']));
	if ($alerts_count > 0) {?>
		<div class="pull-right">
			<a href="javascript:void(0)" id="allergens_btn" title="Allergens"><i class="fa allergen-virus notif-icons abnormal"><sup>
						<small><?= $alerts_count ?></small>
					</sup></i></a>
		</div>
	<?php } ?>

	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
	$alerts_count = count((new AlertDAO())->getForPatient($ARR['patient_ID'], FALSE));
	if ($alerts_count > 0) {
		?>
		<div class="pull-right">
			<a href="javascript:void(0)" title="Alerts" onclick="Boxy.load('/alerts.php?pid=<?= $ARR['patient_ID']; ?>',{title:'Alerts'})" class="required"><i class="fa fa-exclamation-triangle abnormal notif-icons"><sup>
						<small><?= $alerts_count ?></small>
					</sup></i></a>
		</div>
	<?php } ?>
	<div class="pull-right">
		<a href="javascript:void(0)" onClick="Boxy.load('/boxy.id.php?pid=<?= $_patient->getId() ?>',{title:'PATIENT IDENTIFICATION CARD'})"><i class="fa fa-newspaper-o notif-icons"></i></a>
	</div>
	<!--<div class="pull-right"><a href="javascript:void(0)" title="Start New Visit Entry for this patient" onclick="newVisit()" class="action"><i class="icon-list-alt"></i></a></div>-->

	<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") && isset($antenatal) && $antenatal != null) { ?>
		<div class="pull-right"><?php if ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role)) { ?>
				<a href="javascript:void(0)" title="Start New Antenatal Visit Entry for this patient" onclick="newAntenatalVisit()" class=""><i class="fa antenatal-mother notif-icons"></i></a><?php } ?>
		</div>
	<?php } ?>

	<div class="pull-right">
		<?php if ($ip->getStatus() == 'Active') { ?>
			<a href="javascript:void(0);" title="Admitted click to discharge" onclick="Boxy.ask('Are you sure you want to discharge this patient?', ['Yes', 'No'], function (choice) {
				if (choice === 'Yes') {
				sendDischarge('<?= $ip->getId() ?>');
				}
				})" class="admitted"><i class="fa fa-hospital-o notif-icons"></i></a>
		<?php } ?>

	</div>
	<div class="pull-right">
		<a href="javascript:;" style="color: #dab402;" title="Special Events" id="eventsLinkBtn"><i class="fa fa-envelope-square notif-icons">
				<sup>
					<small><?= $eventsCount ?></small>
				</sup></i> </a>
	</div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
	$docs_count = (new PatientAttachmentDAO())->countForPatient($pid);
	?>
	<div class="pull-right">
		<a href="javascript:;"  title="Documents" id="docsLinkBtn"><i class="fa fa-book notif-icons"><sup>
					<small><?= $docs_count ?></small>
				</sup></i></a>
	</div>


    <div class="pull-right " style="padding:0 5px">
        <a href="javascript:;" onclick="Boxy.load('/admissions/boxy.in_patient_health.edit.php?pid=<?php echo $_patient->getId(); ?>')" title="Patient's Health Status - <?php echo $inp_health_state['state'];?>" id="phealth_status">
            <?php if( $inp_health_state['state'] == "Stable"){?>

                <i class="fa fa-circle fa-2x notif-icons"  style="color:#32C744;cursor: pointer"></i>
                <?php }?>

            <?php if( $inp_health_state['state'] == "Critical"){?>

                <i class="fa fa-circle fa-2x notif-icons" style="color:red;cursor: pointer"></i>
            <?php }?>

            <?php if( $inp_health_state['state'] == "Intermediate"){?>

                <i class="fa fa-circle fa-2x notif-icons" style="color:yellow;cursor: pointer"></i>
            <?php }?>

            </i></a>

        <?php if($inp_health_state['risk_to_fall'] == 1){?>
            <i onclick="Boxy.load('/admissions/boxy.in_patient_health.edit.php?pid=<?php echo $_patient->getId(); ?>')" class="fa fa-exclamation-triangle notif-icons fa-2x" style="color:red;cursor: pointer" title=" Patient's Risk To Fall "> </i>

        <?php }?>

    </div>


</div>


<div class="line">
	<div class="item_block">
		<span>Reason</span>
		<span><?= $ip->getReason() ?></span>
	</div>
	<div class="item_block">
		<span>Admitted On</span>
		<span data-date="true"><?= date("dS, M Y h:ia", strtotime($ip->getDateAdmitted())) ?></span>
	</div>
	<div class="item_block">
		<span>Anticipated Discharge</span>
		<span data-date="true"><?= $ip->getAnticipatedDischargeDate() != 0 ? date(MainConfig::$dateFormat, strtotime($ip->getAnticipatedDischargeDate())) : 'N/A' ?></span>
	</div>
	<div class="item_block">
		<span>Ward</span>
		<span><?= ($ip->getWard() === null) ? "N/A" : $ip->getWard()->getName() ?></span>
	</div>
	<div class="item_block">
		<span>Bed/Room</span>
		<span><?= ($ip->getBed() === null) ? "Not Assigned" : $ip->getBed()->getName() . "/" . $ip->getBed()->getRoom()->getName() ?> <?php if ($ip->getStatus() == 'Active' && $ip->getBed() !== null) { ?>
				<a href="javascript:" class="btn btn-small1" onclick="changeBed('<?= $ip->getId() ?>')">
					Transfer Bed</a> <?php } ?></span>
	</div>
	<div class="item_block">
		<span></span>
		<span><?php if ($_patient->getSex() == 'female' && is_dir($_SERVER['DOCUMENT_ROOT'] . "/labourMobile") && $ip->getStatus()=='Active') {
				if (!$ip->getLabourInstance()) { ?><a href="javascript:" class="" onclick="setUpLabour()">Set Up Labour Management</a><?php } else { ?>
					<a target="_blank" href="/labourMobile/">Open Labour Management</a><?php }
			} ?> </span>
	</div>
	
	<?php if ($ip->getStatus() == 'Active') { ?>
		<div class="item_block pull-right">
			<span></span>
			<span>
                <button class="drop-btn" title="Admitted click to discharge" onclick="Boxy.ask('Are you sure you want to discharge this patient?', ['Yes', 'No'], function (choice) {
					if (choice === 'Yes') {
					sendDischarge('<?= $ip->getId() ?>');
					}
					})">Discharge</button>
            </span>

            <?php if(empty( $inp_health_state['state'] )){?>
            <span>
            <button class="drop-btn" onclick="Boxy.load('/admissions/boxy.in_patient_health.edit.php?pid=<?php echo $_patient->getId(); ?>')">
                Add Health State
            </button>
            </span>
            <?php }?>
		</div>
	<?php } ?>
	
	

</div>

<div class="line">
	<div class="item_block">
		<span>Sex</span>
		<span><?= ucwords($_patient->getSex()) ?></span>
	</div>
	<div class="item_block">
		<span>DOB</span>
		<span><?= date("d M, Y", strtotime($_patient->getDateOfBirth())) ?> (<em><?= $_patient->getAge() ?></em>)</span>
	</div>

	<div class="item_block">
		<span>Insurance Status</span>
		<span<?= ((bool)!$_patient->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= strtoupper($_patient->getScheme()->getName()) . " (<em>" . strtoupper($_patient->getScheme()->getType()) . "</em>)" ?></span>
	</div>

	<div class="item_block">
		<span>Admitted</span>
		<span>
            <a href="javascript:;" onclick="showAdmissionHistory('<?= $pid ?>')"><?= count($admInstances) ?> time(s)</a>
	        [<em title="Cumulative # of days on admission this year"><?= $_patient->getNumDaysOnAdmission() ?> days Annual Cum.</em>]
        </span>
	</div>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
$in_patient_id = (isset($ip) && ($ip !== null)) ? $ip->getId() : null;
$vitals = (new VitalSignDAO())->getPatientDemographLastVitalSigns($pid, $in_patient_id, FALSE, ["Temperature", "Blood Pressure", "Respiration", "Pulse", "Weight", "SpO2"]);
?>
<div class="line">
	<div class="item_block">
		<span>Last Vitals</span>
		<span>&nbsp;<?php foreach ($vitals as $v) {/*$v=new VitalSign(); */?><?= ucwords($v->getType()->getName()) ?>:
				<em class="fadedText<?= $v->getAbnormal() ? ' abnormal':''?>"><?= $v->getValue() ?></em> <big class="fadedText">&middot;</big> <?php } ?></span>
	</div>
</div>


<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/antenatal") && isset($antenatal) && $antenatal != null) { ?>
	<div class="line">
		<div class="pull-left fadedText">ANC #: <?= $antenatal->getRequestCode() ?></div>
		<div class="pull-left fadedText">
			<i class="icon icon-user"></i><a href="javascript:void(0)" onclick="Boxy.load('/antenatal/boxy.paternity.php?a_id=<?= $antenatal->getId() ?>', {title: 'Husband Details'});">Husband</a>
			|
		</div>
		<div class="pull-left fadedText">
			<i class="icon-calendar"></i>Enrolled:<?= date('d M, Y', strtotime($antenatal->getEnrolledOn())) ?> |
		</div>
		<div class="pull-left fadedText">
			<i class="icon-credit-card"></i>Package: <?= $antenatal->getPackage() ? $antenatal->getPackage()->getName() : 'N/A ' ?>
			|
		</div>
		<div class="pull-left fadedText">
			<i class="icon-paper-clip"></i>Reason: <?= ucwords($antenatal->getBookingIndication() === 'complication' ? '<a href="javascript:" onclick="Boxy.plain(\'' . ucwords($antenatal->getComplicationNote()) . '\')">Complication Note</a>' : $antenatal->getBookingIndication()) ?>
			| Recommendation: <a href="javascript:" onclick="new Boxy('<section style=\'width:500px\'><?= htmlentities($antenatal->getRecommendation()) ?></section>', {title: 'Antenatal Recommendations...'})">View</a>

			| <a href="javascript:" onclick="editRecommendation(<?= $antenatal->getId()?>)">Edit</a>
		</div>
	</div>
	<div class="line">
		<div class="pull-left fadedText"><i class="icon-calendar"></i>LMP:
			<a href="javascript:" id="lmp_editable"><?= date('d M, Y', strtotime($antenatal->getLmpDate())) ?></a> (Given
			by <?= $antenatal->getLmpSource() ?>) |
		</div>
		<div class="pull-left fadedText">
			<i class="icon-calendar"></i>EDD:<?= date('d M, Y', strtotime($antenatal->getEdDate())) ?> |
		</div>
		<div class="pull-left pregnancy_details fadedText">
			<div class="gestation">Gestational age: <span></span></div>
			|
			<div class="delivery">Number of days to delivery: <span></span></div>
		</div>
	</div>

	<div class="line">
		<div class="pull-left fadedText"><a href="javascript:" onclick="editGPAM(<?= $antenatal->getId() ?>)">Edit</a> |</div>
		<div class="pull-left fadedText">Gravida: <?= $antenatal->getGravida() ?> |</div>
		<div class="pull-left fadedText">Para: <?= $antenatal->getPara() ?> |</div>
		<div class="pull-left fadedText">Alive: <?= $antenatal->getAlive() ?> |</div>
		<div class="pull-left fadedText">Miscarriages: <?= $antenatal->getAbortions() ?> |</div>

		<div class="pull-right fadedText">
			<a href="javascript:;" class=" " title="Pregnancies Registered" onclick="showAntenatalHistory('<?= $_patient->getId() ?>')">Antenatal
				History</a>
			<?php if ($antenatal->getActive()) { ?> |
				<a href="javascript:;" class=" " title="Enrolled; click to close" onclick="Boxy.ask('Are you sure you want to close this antenatal instance?', ['Yes', 'No'], function (choice) {
					if (choice === 'Yes') {
					sendCloseEnrollment('<?= $antenatal->getId() ?>');
					}
					})">Close Antenatal</a>
			<?php } ?>
		</div>
	</div>
<?php } ?>


<!--
<?php if ($aEnroll !== null) { ?>
	<div class="line">
	<div class="pull-left fadedText">Enrolled on
		<i class="icon-calendar"></i> <?= date("d/m/Y", strtotime($aEnroll->getEnrolledOn())) ?> |
	</div>
	<div class="pull-left fadedText">estimated conception date <i class="icon-calendar"></i></div>
	<div class="pull-right fadedText">
		<a href class="btn btn-small1" title="pregnancies registered"><i class="icon-th-list"></i> Antenatal History</a>
	</div>
	<div class="pull-right fadedText"><a class="btn btn-small1" href><i class="icon-ban-circle"></i> Close Antenatal</a>
	</div>
	</div><?php } ?>

<?php if ($iEnroll !== null) { ?>
	<div class="line">
		<div class="fadedText">
			<i class="icon-user"></i><a href="/immunization/patient_immunization_profile.php?id=<?= $_patient->getId() ?>" target="_blank">View
				Immunization Profile</a></div>
	</div>
<?php } ?>
-->

<div class="line">
	<div>
		<i class="icon-book"></i><a href="javascript:void(0)" onclick="Boxy.load('/boxy.patient.more.details.php?id=<?= $_patient->getId() ?>', {title: 'Patient Details'});">More
			Details...</a> |
		<i class="icon-edit"></i><a href="/edit_patient_profile.php?id=<?= $_patient->getId() ?>&aid=<?= $in_patient_id ?>">Update
			Details...</a> |
		<i class="icon-exclamation-sign"></i><a href="javascript:;" onClick="Boxy.load('/boxy.manage_notifications.php?pid=<?= $_patient->getId() ?>', {title: 'Manage Notifications'})">Notification
			Preference</a>
	</div>
</div>
<script type="text/javascript">
	var inPatientContext = true;
	$(document).ready(function () {
		<?php if($eventsCount > 0){?>
		Boxy.load("/specialEvents.php?pid=<?=$ARR['patient_ID'] ?>");
		<?php }?>
		<?php if(isset($antenatal)){?>$("#lmp_editable").editable({
			url: '/api/edit_lmp.php',
			pk: <?=$antenatal->getId()?>,
			type: 'date',
			title: 'Set new LMP date',
			success: function (response, newValue) {
				response = JSON.parse(response);
				if (response.status == "error") {
					return response.message;
				} else {
					location.reload();
				}
			},
			value: '<?= $antenatal->getLmpDate() ?>',
			error: function (response, newValue) {
				return "Server not available right now"
			},
			display: function (value) {
				if (!value) {
					$(this).empty();
					return;
				}
				$(this).html(moment($('<div>').text(value).html()).format('MMM Do, YY'));
			}
		});<?php }?>
		$("#eventsLinkBtn").click(function (e) {
			if (!e.handled) {
				Boxy.load("/specialEvents.php?pid=<?=$ARR['patient_ID'] ?>");
				e.handled = true;
			}
		});

		$("#allergens_btn").click(function (e) {
			if (!e.handled) {
				showTabs(7);
				e.handled = true;
			}
		});
		//$("#age").html(moment("<?= $_patient->getDateOfBirth() ?>").fromNow(true));
		$('#docsLinkBtn').click(function (e) {
			if (!e.handled) {
				showTabs(12);
				e.handled = true;
			}
		});
	});
	$(function () {
		$('#profile_container .fix-me').scrollToFixed({
			preFixed: function () {
				$('.fix-me .passport').animate({'width': '70px'});
				$('#pid_').css({'font-size': '150%'});
			},
			postFixed: function () {
				$('.fix-me .passport').animate({'width': '53px'});
				$('#pid_').css({'font-size': 'small'});
			}
		});
	});

	function newVisit() {
		Boxy.load('/boxy.startnewvisit.php?id=<?= $_patient->getId() ?>', {title: 'Start New Visit'});
	}

	function showAdmissionHistory(pid) {
		Boxy.load("/admissions/instancesDialog.php?pid=" + pid);
	}
	function showAntenatalHistory(pid) {
		Boxy.load("/antenatal/instancesDialog.php?pid=" + pid);
	}

	<?php if(is_dir($_SERVER['DOCUMENT_ROOT']."/antenatal")){ ?>
	function sendCloseEnrollment(aid) {
		Boxy.load('/antenatal/boxy.close-antenatal-enrollment.php?aid=' + aid, {title: 'Close Enrollment'});
	}
	function editGPAM(id){
		Boxy.load('/antenatal/edit.gpam.php?instance='+id);
	}
	function editRecommendation(aid){
		Boxy.load('/antenatal/edit_recommendation.php?id='+aid);
	}
	<?php } ?>

	function changeBed(aid) {
		Boxy.load('changeBed.php?aid=' + aid);
	}

	function showNewDiagnosisDlg() {
		Boxy.load('/boxy.addDiagnosis.php?pid=<?= $_patient->getId() ?>&aid=<?=$ip->getId()?>', {title: 'New Diagnosis'});
	}
	<?php if($antenatal){?>
	function newAntenatalVisit(){
		location.href='/antenatal/patient_antenatal_profile.php?id=<?= $_patient->getId() ?>&aid=<?=$antenatal->getId()?>';
	}
	<?php }?>

	function editOphthalmologyResult(testId, plId, testName) {
		Boxy.load('/ophthalmology/editResult.php?testId=' + testId + '&plId=' + plId, {title: testName});
	}

	function setUpLabour() {
		Boxy.ask("Set up Labour Management for this admission?", ["Yes", "No"], function (answer) {
			if (answer == "Yes") {
				$.getJSON("/api/set_up_labour_from_admission.php", {
					patient_id: '<?= $_patient->getId() ?>',
					aid: <?= $ip->getId()?> }, function (data) {
					if (data.status == "success") {
						Boxy.info(data.message, function () {
							location.reload();
						});
					} else if (data.status == "error") {
						Boxy.warn(data.message);
					}
				});
			}
		})
	}
</script>
<?php unset($_SESSION['app']) ?>
