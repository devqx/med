<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/17/16
 * Time: 3:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$protect = new Protect();
$patient = (new PatientDemographDAO())->getPatient($_GET['id'], TRUE);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSpecialEventDAO.php';
$eventsCount = (new PatientSpecialEventDAO())->countForPatient($patient->getId());

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
$nextAppointment = (new AppointmentDAO())->getPatientNextAppointment($patient->getId());
?>
<div class="line fix-me">
	<div class="pull-left">
		<a title="Change Photo" href="javascript:void(0);"><img class="passport" src="<?= $patient->getPassportPath(); ?>" width="53"/>
		</a>
	</div>
	<div>
		<h4 class="uppercase"><?= $patient->getFullname() ?></h4>
		<?php $badge = (new InsuranceSchemeDAO())->get($patient->getScheme()->getId())->getBadge() ?>
		<h6 class="pull-right" title="Badge"><?= $badge ? html_entity_decode($badge->getIcon()) : '' ?></h6>
		<span class="fadedText" id="pid_"><i class="icon icon-user"></i><a href="/patient_profile.php?id=<?= $patient->getId() ?>"><?= $patient->getId() ?> <?= (trim($patient->getLegacyId()) != "") ? ' (' . $patient->getLegacyId() . ')' : '' ?></a></span>
	</div>
	
	<div class="pull-right">
		<a title="EMR Card" href="javascript:void(0)" onClick="Boxy.load('/boxy.id.php?pid=<?= $patient->getId() ?>')" class=""><i class="fa fa-newspaper-o notif-icons"></i></a>
	</div>
	<div class="pull-right">
		<a href="javascript:;" style="color: #dab402;" title="Special Events" id="eventsLinkBtn"><i class="fa fa-envelope-square notif-icons">
				<sup>
					<small><?= $eventsCount ?></small>
				</sup></i> </a>
	</div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
	$docs_count = (new PatientAttachmentDAO())->countForPatient($patient->getId());
	?>
	<div class="pull-right">
		<a href="javascript:;" title="Documents" id="docsLinkBtn"><i class="fa fa-book notif-icons">
				<sup>
					<small><?= $docs_count ?></small>
				</sup></i></a>
	</div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
	$alerts_count = count((new AlertDAO())->getForPatient($patient->getId(), FALSE));
	if ($alerts_count > 0) {
		?>
		<div class="pull-right">
			<a href="javascript:void(0)" title="Alerts" onclick="Boxy.load('/alerts.php?pid=<?= $patient->getId() ?>',{title:'Alerts'})" class="required"><i class="fa fa-exclamation-triangle abnormal notif-icons"><sup>
						<small><?= $alerts_count ?></small>
					</sup></i></a>
		</div>
	<?php } ?>
	<script type="text/javascript">
		function tellArv() {
			$("#notify2").notify({speed: 500, expires: false});
			$("#notify2").notify("create", {
				title: "Patient is enrolled into ARV clinic",
				text: 'click to hide'
			}, {
				expires: false,
				click: function (e, instance) {
					instance.close();
				}
			});
		}
	</script>
	<?php $arvEnrolled = FALSE;
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
	$nextAppointment = (new AppointmentDAO())->getPatientNextAppointment($patient->getId());
	if (is_dir("arvMobile")) {
		require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvEnrollmentDAO.php';
		$arvEnrolled = (new ArvEnrollmentDAO())->isEnrolled($patient->getId());
	} ?>
	<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/arvMobile") && $arvEnrolled) { ?>
		<div class="pull-right">
			<a href="javascript:void(0)" title="ARV Enrolled" onclick="tellArv()"><i class="fa ribbon-ribbon notif-icons"></i></a>
		</div>
	<?php } ?>
</div>


<div class="line">
	<div class="item_block">
		<span>Sex</span>
		<span><?= ucwords($patient->getSex()) ?></span>
	</div>
	<div class="item_block">
		<span>DOB</span>
		<span><?= date(MainConfig::$dateFormat, strtotime($patient->getDateOfBirth())) ?>
			(<em><?= $patient->getAge() ?></em>)</span>
	</div>
	<div class="item_block">
		<span>Insurance Status</span>
		<span<?= ((bool)!$patient->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= strtoupper($patient->getScheme()->getName()) . " (<em>" . strtoupper($patient->getScheme()->getType()) . "</em>)" ?></span>
	</div>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
$instance = (new IVFEnrollmentDAO())->get($_GET['aid'], TRUE);
?>

<div class="line">
	<div class="pull-left fadedText">IVF #: <?= $instance->getFileNo() ?></div>
	<div class="pull-left fadedText">
		<?php if($instance->getHusband()) {?>
		<i class="icon icon-user"></i><a href="javascript:void(0)" class="profile" data-pid="<?=$instance->getHusband()->getId() ?>">Husband Details</a>
		|<?php } else {?>No Husband specified
		<?php }?>
	</div>
	<div class="pull-left fadedText">
		<i class="icon-calendar"></i> Enrolled: <?= date('d M, Y', strtotime($instance->getDateEnrolled())) ?> |
	</div>
	<div class="pull-left fadedText">
		<i class="icon-credit-card"></i> Package: <?= $instance->getPackage() ? $instance->getPackage()->getName() : 'N/A ' ?>
		|
	</div>
	<div class="pull-left fadedText">
		<i class="icon-paper-clip"></i> Treatment Plan: <?= ucwords($instance->getStimulation()['method']->getName()) ?>
	</div>
</div>

<div class="line">
	<div class="pull-left fadedText"><i class="icon-calendar"></i>Cycle:
		<a href="javascript:" class="editable" id="ivf_cycle"><?= date('M, Y', strtotime($instance->getStimulation()['cycle'])) ?></a>
	</div>
</div>

<div class="line">
	<div class="pull-right fadedText">
		<a href="javascript:;" class="" title="" onclick="showIvfHistory('<?= $patient->getId() ?>')">IVF
			History</a>
		<?php if ($instance->getActive()) { ?> |
			<a href="javascript:;" class=" " title="Enrolled; click to close" onclick="Boxy.ask('Are you sure you want to close this instance?', ['Yes', 'No'], function (choice) {
				if (choice === 'Yes') {
				sendCloseEnrollment('<?= $instance->getId() ?>');
				}
				})">Close Instance</a>
		<?php } ?>
	</div>
</div>

<div class="line small">
	<div>
		<i class="icon-book"></i><a href="javascript:void(0)" onclick="Boxy.load('/boxy.patient.more.details.php?id=<?= $patient->getId() ?>', {title: 'Patient Details'});">More
			Details...</a>
		<?php if ($this_user->hasRole($protect->records)) { ?>| <i class="icon-edit"></i>
			<a href="/edit_patient_profile.php?id=<?= $patient->getId() ?>">Update Details...</a> |<?php } ?>
		<?php if ($this_user->hasRole($protect->records)) { ?><i class="icon-bolt"></i>
			<a href="javascript:;" onClick="Boxy.load('/boxy.manage_notifications.php?pid=<?= $patient->getId() ?>', {title: 'Manage Notifications'})">Notification
				Preference</a> | <?php } ?>
		<?php if ($this_user->hasRole($protect->records)) { ?><i class="icon-time"></i>
			<a href="javascript:;" onClick="showMedicalHistory()">Medical History</a><?php } ?> |
	</div>

	<div class="pull-right"><i class="icon-calendar"></i>
		<span style="margin-right: 20px">Next Appointment: <?php if (!is_null($nextAppointment)) { ?>
				<span data-date="true"><?= date("M j, Y H:i A", strtotime($nextAppointment->getStartTime())) ?></span><?php } else { ?>None<?php } ?></span><?php if ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?>
		<button href="javascript:;" class="drop-btn action mini" onclick="Boxy.load('/appointments/boxy_createAppointment.php?pid=<?= $patient->getId() ?>', {unloadOnHide: true, afterHide:function(){/*location.reload()*/}})">
				New Appointment</button><?php } ?></div>
</div>
<script type="text/javascript">
	function showMedicalHistory() {
		Boxy.load('/boxy.medical_history.php?pid=<?=$patient->getId() ?>', {title: 'Patient Medical History'})
	}

	function showIvfHistory() {
		Boxy.load('/ivf/profile/instances.php?pid=<?=$patient->getId() ?>');
	}

	function sendCloseEnrollment(id) {
		$.post('/ivf/api/close_enrollment.php', {id: id, action: 'close'}, function (response) {
			if(response===true){
				location.reload();
			} else {
				Boxy.alert('Action Failed!');
			}
		})
	}
</script>
