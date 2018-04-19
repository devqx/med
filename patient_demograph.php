<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$mgr = new Manager();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';

$all = isset($_GET['_a']) ? $_GET['_a'] : null;
$patient = (new PatientDemographDAO())->getPatient($ARR['patient_ID'], TRUE, null, $all);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
$admInstances = (new InPatientDAO())->getInPatientInstances($ARR['patient_ID'], FALSE);

if (!isset($_SESSION)) {
	@session_start();
}
$staff = new StaffManager();
$lifeStyle = $mgr->getAllLifeStyle();
$arvEnrolled = FALSE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
$nextAppointment = (new AppointmentDAO())->getPatientNextAppointment($ARR['patient_ID']);
if (is_dir("arvMobile")){
	require_once $_SERVER['DOCUMENT_ROOT'] . '/arvMobile/web/classes/DAOs/ArvEnrollmentDAO.php';
	$arvEnrolled = (new ArvEnrollmentDAO())->isEnrolled($ARR['patient_ID']);
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSpecialEventDAO.php';

$eventsCount = (new PatientSpecialEventDAO())->countForPatient($ARR['patient_ID']);

$sql = "SELECT `value`, (SELECT count(a.id) FROM appointment a LEFT JOIN appointment_group g ON a.group_id=g.id WHERE g.patient_id = " . $ARR['patient_ID'] . " AND a.status='Active') AS `count` FROM vital_sign WHERE patient_id = " . $ARR['patient_ID'] . " ORDER BY read_date DESC LIMIT 1";

$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$chk = $stmt->execute();
$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
?>

<div class="line fix-me">
	<div class="pull-left">
		<a title="Change Photo" href="javascript:void(0);"><img class="passport" src="<?= $patient->getPassportPath(); ?>" width="53"/>
		</a>
	</div>
	<div>
		<h4 class="uppercase"><?= $patient->getFullname() ?></h4>
		<?php $badge = (new InsuranceSchemeDAO())->get($patient->getScheme()->getId())->getBadge() ?>
		<h6 class="pull-right" title="Badge"><?= $badge ? html_entity_decode( $badge->getIcon() ) : ''?></h6>
		<span class="fadedText" id="pid_"><i class="icon icon-user"></i><a href="/patient_profile.php?id=<?= $ARR['patient_ID'] ?>"><?= $ARR['patient_ID'] ?> <?= (trim($patient->getLegacyId()) != "") ? ' (' . $patient->getLegacyId() . ')' : '' ?></a></span>
	</div>
	<div class="pull-right">
		<a title="BARCODE" href="javascript:void(0)" onclick="Boxy.load('/boxy.barcode.print.php?pid=<?= $ARR['patient_ID'] ?>')" class=""><i class="fa fa-barcode notif-icons"></i></a>
	</div>
	<div class="pull-right">
		<a title="EMR Card" href="javascript:void(0)" onClick="Boxy.load('/boxy.id.php?pid=<?= $ARR['patient_ID'] ?>')" class=""><i class="fa fa-newspaper-o notif-icons"></i></a>
	</div>
	<div class="pull-right"><?php if ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role)) { ?>
			<a href="javascript:void(0)" title="Start New Visit Entry for this patient" onclick="newVisit()" class=""><i class="fa fa-pencil-square-o notif-icons"></i></a><?php } ?>
	</div>

	<?php if (is_dir("antenatal") &&  isset($antenatal) && $antenatal != null) { ?>
		<div class="pull-right"><?php if ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role)) { ?>
			<a href="javascript:void(0)" title="Start New Antenatal Visit Entry for this patient" onclick="newAntenatalVisit()" class=""><i class="fa antenatal-mother notif-icons"></i></a><?php } ?>
		</div><?php } ?>
	<div class="pull-right"><?php if (is_dir('admissions') && isset($ip) && $ip !== null) { ?>
			<a href="/admissions/inpatient_profile.php?aid=<?= $ip->getId() ?>&pid=<?= $ip->getPatient()->getId() ?>" title="Admitted" class="admitted"><i class="fa fa-hospital-o notif-icons"></i></a>
		<?php } ?>
	</div>
	<div class="pull-right">
		<a href="javascript:" style="color: #dab402;" title="Special Events" id="eventsLinkBtn"><i class="fa fa-envelope-square notif-icons">
				<sup>
					<small><?= $eventsCount ?></small>
				</sup></i> </a>
	</div>
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
	$docs_count = (new PatientAttachmentDAO())->countForPatient($ARR['patient_ID']);
	?>
	<div class="pull-right">
		<a href="javascript:;" title="Documents" id="docsLinkBtn"><i class="fa fa-book notif-icons"><sup>
					<small><?= $docs_count ?></small>
				</sup></i></a>
	</div>
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
	
<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAllergensDAO.php';
	$alerts_count = count((new PatientAllergensDAO())->forPatient($ARR['patient_ID']));
	if ($alerts_count > 0) {?>
		<div class="pull-right">
			<a href="javascript:void(0)" title="Allergens" id="allergens_btn"><i class="fa allergen-virus notif-icons abnormal"><sup>
						<small><?= $alerts_count ?></small>
					</sup></i></a>
		</div>
	<?php } ?>
	
	<?php if (is_dir($_SERVER['DOCUMENT_ROOT']."/arvMobile") &&  $arvEnrolled) { ?>
		<div class="pull-right">
			<a href="javascript:void(0)" title="ARV Consultation" onclick="showTabs(100)"><i class="fa ribbon-ribbon notif-icons"></i></a>
		</div>
	<?php } ?>
	<script>
		function newVisit() {
			Boxy.load('/boxy.startnewvisit.php?id=<?= $ARR['patient_ID']; ?>', {title: 'New Visit'});
		}
		function newAntenatalVisit() {
			Boxy.load('/antenatal/boxy.startnewvisit.php?id=<?= $ARR['patient_ID']; ?>&aid=<?= isset($antenatal) ? $antenatal->getId() : '' ?>', {title: 'New Antenatal Visit'});
		}
	</script>
</div>


<div class="line">
	<div class="item_block">
		<span>Sex</span>
		<span><?= ucwords($patient->getSex()) ?></span>
	</div>
	<div class="item_block">
		<span>DOB</span>
		<span><?= date(MainConfig::$dateFormat, strtotime($patient->getDateOfBirth())) ?> (<em><?= $patient->getAge() ?></em>)</span>
	</div>
	<div class="item_block">
		<span>Insurance Status</span>
		<span<?= ((bool)!$patient->getInsurance()->getActive() ? ' class="abnormal" title="Insurance is not active"' : '') ?>><?= strtoupper($patient->getScheme()->getName()) . " (<em>" . strtoupper($patient->getScheme()->getType()) . "</em>)" ?></span>
	</div>
	<div class="item_block">
		<span>Visited</span>
		<span><?= ($stmt->rowCount() > 0 ? $row["count"] : '0'); ?> time(s)</span>
	</div>
	<?php if(is_dir('admissions')) { ?>
	<div class="item_block">
		<span>Admitted</span>
		<span>
			<a href="javascript:" onclick="showAdmissionHistory('<?= $patient->getId() ?>')"><?= count($admInstances) ?>	time(s)</a>
			[<em title="Cumulative # of days on admission this year"><?=$patient->getNumDaysOnAdmission()?> days Annual Cum.</em>]</span>
	</div>
	<?php } ?>

    <?php if(in_array($patient->getEnablePortal(), ['open', ''])) {?>
    <div class="item_block">
        <i class="fa fa-"></i>
    <a href="javascript:" onclick="enablePortal()">.</a>
</div>
    <?php } else { ?>
    <div class="item_block">
        <i class="fa fa-"></i>
        <span>Patient Portal Enabled</span>
    <?php } ?>

<?php /* ?>
      <!--    <div><strong>Sex</strong> --><?php //echo ucwords($ARR['sex']) ?><!--</div>-->
      <!--    <div><strong>DOB</strong> --><?php //echo date("d M, Y",strtotime($ARR['date_of_birth']))  ?><!--</div>-->
      <div><strong>Life Style</strong>
      <?php
      $p_life_styles = explode("|", $ARR['lifestyle']);
      $lifeStyleText = array();//to hold the text of the lifestyles as we discover them
      for($i=0; $i<sizeof($lifeStyle); $i++){
      if(in_array($lifeStyle[$i][0], $p_life_styles)){
      $lifeStyleText[] = $lifeStyle[$i][1];
      }
      }
      echo implode(", ",$lifeStyleText);?></div>
      <div><strong>Socioeconomic Status</strong> <?= $ARR['name'] ?></div>
      <div><strong>Insurance Status</strong>
      <?php require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.insurance.php';$insurance=new Insurance();$ins_mgr = new InsuranceManager();echo str_replace(' ','&nbsp;',$insurance->getPatientInsurance($ARR['patient_ID'], 'type'))?>
      </div>
      <div><strong>Payer</strong>
      <?php $prov = $ins_mgr->getInsuranceSchemeOwnerName($insurance->getPatientInsurance($ARR['patient_ID'], 'scheme')); echo ((strtolower($prov)!='self')?$prov:'N/A');?>
      </div>
      <div><strong>Scheme</strong>
      <?php $scheme =  $ins_mgr->getInsuranceSchemeName($insurance->getPatientInsurance($ARR['patient_ID'], 'scheme') );if(strtolower($scheme)!='self'){echo str_replace(' ','&nbsp;',$scheme  );}else{echo 'N/A';}?>
      </div>
      <div><strong>Valid through</strong>
      <?= (($insurance->getPatientInsurance($ARR['patient_ID'], 'expiration')!='N/A')?str_replace(' ','&nbsp;',date ( "d M, Y", strtotime ( $insurance->getPatientInsurance($ARR['patient_ID'], 'expiration')))):'N/A');?>
      </div><?php */ ?>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
$in_patient_id = (isset($ip) && ($ip !== null)) ? $ip->getId() : null;
$vitals = (new VitalSignDAO())->getPatientDemographLastVitalSigns($patient->getId(), $in_patient_id, FALSE, ["Temperature", "Blood Pressure", "Respiration", "Pulse", "Weight", "SpO2", "Pain Scale"]);
?>
<div class="line">
	<div class="item_block">
		<span>Last Vitals</span>
		<span>&nbsp;<?php foreach ($vitals as $v) {/*$v=new VitalSign(); */?><?= ucwords($v->getType()->getName()) ?>:
				<em class="fadedText<?= $v->getAbnormal() ? ' abnormal':''?>"><?= $v->getValue() ?></em> <big class="fadedText">&middot;</big> <?php } ?></span>
	</div>
</div>
<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") && isset($antenatal) && $antenatal != null) { ?>
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
			<i class="icon-calendar"></i>EDD:<?= $antenatal->getEdDate() ? date('d M, Y', strtotime($antenatal->getEdDate())) : 'N/A' ?> |
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
			<a href="javascript:;" class=" " title="Pregnancies Registered" onclick="showAntenatalHistory('<?= $patient->getId() ?>')">Antenatal
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
	<?php if ($antenatal->getDateClosed()) { ?>
		<div class="line">
			<div class="pull-left">Closing Note:</div>
			<div class="pull-left fadedText"><?= htmlentities($antenatal->getCloseNote()) ?></div>
			<div class="pull-left">| Date</div>
			<div class="pull-left fadedText"><?= date(  MainConfig::$shortDateFormat,strtotime($antenatal->getDateClosed())) ?></div>
		</div>
	<?php } ?>
	
<?php } ?>
<div class="line">
	<?php if (is_dir("antenatal") && (!isset($antenatal) || $antenatal === null) && $patient->getSex() == "female") { ?>
		<div class="pull-right fadedText">
			<a href="javascript:;" class=" " title="Pregnancies Registered" onclick="showAntenatalHistory('<?= $patient->getId() ?>')">Antenatal
				History</a>
		</div>
	<?php } ?>
	<?php if (is_dir("immunization") && $mgr->isImmunization($ARR['patient_ID']) && is_dir("immunization")) {
		//todo: remove this link also if we are already in the immunization profile ?>
		<?php if ($this_user->hasRole($protect->nurse) || $this_user->hasRole($protect->doctor_role)) { ?>
			<div class="fadedText">
				<i class="icon-user"></i><a href="/immunization/patient_immunization_profile.php?id=<?= $ARR['patient_ID'] ?>" target="_blank">View
					Immunization Profile</a></div>
		<?php }
	} ?>
</div>
<?php if (isset($ip) && ($ip !== null)) { ?>
	<div class="line">
		<div class="fadedText"><i class="icon-calendar"></i>Admitted
			on <?= date("Y M, d", strtotime($ip->getDateAdmitted())) ?></div>
		<div class="fadedText"><i class="icon-user"></i> by <?= $ip->getAdmittedBy() ?></div>
		<div class="fadedText"><i class="icon-check-empty"></i> Reason: <?= $ip->getReason() ?></div>
	</div>
<?php } ?>


<div class="line small">
	<div>
		<i class="icon-book"></i><a href="javascript:void(0)" onclick="Boxy.load('/boxy.patient.more.details.php?id=<?= $ARR['patient_ID'] ?>', {title: 'Patient Details'});">More
			Details...</a>
		<?php if ($this_user->hasRole($protect->records)) { ?>| <i class="icon-edit"></i>
			<a href="/edit_patient_profile.php?id=<?= $ARR['patient_ID'] ?>">Update Details...</a> |<?php } ?>
		<?php if ($this_user->hasRole($protect->records)) { ?><i class="icon-bolt"></i>
			<a href="javascript:;" onClick="Boxy.load('/boxy.manage_notifications.php?pid=<?= $ARR['patient_ID'] ?>', {title: 'Manage Notifications'})">Notification
				Preference</a> | <?php } ?>
		<?php if ($this_user->hasRole($protect->records)) { ?><i class="icon-time"></i>
			<a href="javascript:;" onClick="showMedicalHistory()">Medical History</a><?php } ?> |
		<?php if (is_dir("arvMobile") && $arvEnrolled && $this_user->hasRole($protect->doctor_role)) { ?><i class="virus-virus1"></i>
			<a href="javascript:;" onClick="showArvCommencement()">ART Commencement</a><?php } ?>
	</div>

	<div class="pull-right"><i class="icon-calendar"></i>
		<span style="margin-right: 20px">Next Appointment: <?php if (!is_null($nextAppointment)) { ?>
				<span data-date="true"><?= date("M j, Y H:i A", strtotime($nextAppointment->getStartTime())) ?></span><?php } else { ?>None<?php } ?></span><?php if ($this_user->hasRole($protect->records) || $this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) { ?>
			<button href="javascript:;" class="drop-btn mini" onclick="Boxy.load('/appointments/boxy_createAppointment.php?pid=<?= $ARR['patient_ID'] ?>', {unloadOnHide: true, afterHide:function(){/*location.reload()*/}})">
				New Appointment</button><?php } ?></div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		<?php if($eventsCount > 0){?>
		Boxy.load("/specialEvents.php?pid=<?=$ARR['patient_ID'] ?>");
		<?php }?>
		$("#age").html(moment("<?= $ARR['date_of_birth'] ?>").fromNow(true));
		<?php if(isset($antenatal)){?>$("#lmp_editable").editable({
			url: '/api/edit_lmp.php',
			pk: <?=$antenatal->getId()?>,
			type: 'date',
			title: 'Set new LMP date',
			success: function (response, newValue) {
				response = JSON.parse(response);
				if (response.status === "error") {
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
		<?php if (!is_null($nextAppointment)) { ?>$('span[data-date="true"]').html(moment("<?= $nextAppointment->getStartTime() ?>").format(" Do MMMM, YYYY"));<?php } ?>

		$("#eventsLinkBtn").click(function (e) {
			if (!e.handled) {
				Boxy.load("/specialEvents.php?pid=<?=$ARR['patient_ID'] ?>");
				e.handled = true;
			}
		});
		$("#allergens_btn").click(function (e) {
			if (!e.handled) {
				showTabs(4);
				e.handled = true;
			}
		});
		$('#docsLinkBtn').click(function (e) {
			if (!e.handled) {
				//todo if it's the normal profile, else in antenatal
				showTabs(15);
				e.handled = true;
			}
		});

		<?php if (is_dir($_SERVER['DOCUMENT_ROOT'] . "/antenatal") && isset($antenatal) && $antenatal != null) { ?>
		<?php if($antenatal->getLmpDate()){?>
		var $newDate = moment('<?= $antenatal->getLmpDate() ?>', "YYYY-MM-DD").add(40, 'weeks').format('YYYY-MM-DD');
		var now = moment(new Date());
		var edd = moment($newDate);
		$('.pregnancy_details .gestation span').html(now.diff(moment('<?= $antenatal->getLmpDate() ?>', "YYYY-MM-DD"), 'week') + ' week(s)');
		$('.pregnancy_details .delivery span').html(edd.diff(now, 'days') + ' day(s)');
		<?php }?>
		<?php }?>

	});
	$(function () {
		$("#tabbedPane").find("ul li:not([style*='display: none']):last a").css({'border-right-width': '1px !important;'});
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

	function showAdmissionHistory(pid) {
		Boxy.load("/admissions/instancesDialog.php?pid=" + pid);
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

	function showAntenatalHistory(pid) {
		Boxy.load("/antenatal/instancesDialog.php?pid=" + pid);
	}

	function showMedicalHistory() {
		Boxy.load('/boxy.medical_history.php?pid=<?=$ARR['patient_ID'] ?>', {title: 'Patient Medical History'})
	}
	function showNewDiagnosisDlg() {
		Boxy.load('/boxy.addDiagnosis.php?pid=<?= $patientID ?>', {title: 'New Diagnosis'});
	}

	function showArvCommencement() {
		Boxy.load('/arvMobile/web/tab/commencement.php?pid=<?=$ARR['patient_ID'] ?>', {title: 'Arv Commencement'});
	}

	function editOphthalmologyResult(testId, plId, testName){
		Boxy.load('/ophthalmology/editResult.php?testId='+testId+'&plId='+plId, {title: testName});
	}

	function enablePortal() {
	    var data,status = '';
	    Boxy.confirm("Do you want to enable portal for this patient ?", function () {
            $.ajax({
                url :'http://localhost:8000/create/patient/',
                type: 'POST',
                data: {'pid': '<?= $ARR['patient_ID'] ?> '},
                complete: function (d) {
                    status = d.responseText;
                    data = status.split(':');
                    if(data[0] === 'success'){
                        Boxy.info(data[1]);
                    }else{
                        Boxy.alert("error: Could not enable patient portal");

                    }
                },

            });

        });

    }

</script>
<?php unset($_SESSION['app']) ?>
