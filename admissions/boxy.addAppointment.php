<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/25/18
 * Time: 10:42 PM
 */

if (!isset($_SESSION)) {
	@session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
$prev_encounter = (new EncounterDAO())->forPatient($_GET['pid']);
$prevData = $prev_encounter->data;

$iClass = new Encounter();
$requireSpecialty = $iClass::$requireSpecialty;

$referrals = (new ReferralDAO())->all(0, 5000);

$sourceId = (new BillSourceDAO())->findSourceById(3)->getId();
if (isset($_GET['pid'])) {
	$_consultations = (new BillDAO())->getBillsBySourceForPatient($sourceId, $_GET['pid']);
} else {
	$_consultations = [];
}

$_consultation = end($_consultations);


$followUp = $_consultation !== null && $_consultation !== FALSE ? (strpos($_consultation->getDescription(), 'FollowUp') !== false) : FALSE;
$consultation = ($_consultation !== null && $_consultation !== FALSE ? (new StaffSpecializationDAO())->getSpecializationByTitle(str_replace("FollowUp ", "", (str_replace("Consultancy charges: ", "", $_consultation->getDescription())))) : null);

$depts = (new DepartmentDAO())->getDepartments();
$apt_clinics = (new AptClinicDAO())->all();

$startOfDayHour = date('H'); //10AM

?>
<section style="width: 650px;">
	<form method="post" name="addAppointmentSimple" action="." onsubmit="return AIM.submit( this, {onStart: save_(), onComplete:null})">
		<label class="output well well-small"></label>
		<ul class="appointmentNewContainer">
			<li><label><input type="checkbox" name="immediate_check_in" id="immediate_check_in" > Check in
					Patient immediately</label></li>
			<?php if (!isset($_GET['startVal'])) { ?>
				<li class="timePart">Schedule an appointment in the next:</li>
				<li class="timePart fadedText"><i class="icon-info-sign"></i><strong>Hint</strong>: 0 schedules an appointment
					for now
				</li>
				<li class="timePart row-fluid">
					<label class="span4">
						<input style="min-width: 10px" type="number" data-decimals="0" min="0" name="frequency" placeholder="example: 2" required="required" value="0"></label>
					<label class="span8"><select name="interval" required="required">
							<option value="days">Day(s)</option>
							<option value="weeks">Week(s)</option>
							<option value="months">Month(s)</option>
							<!-- <option value="years">Year(s)</option>-->
						</select></label>
				</li>
			<?php } else { ?>
				<li class="timePart">Schedule an appointment for patient on:
					<span data-name="startVal"><?= date("d M, Y", strtotime($_GET['startVal'])); ?></span></li>
			<?php } ?>
			<li class="patient">
				<label>For <?php if (!isset($_GET['pid'])) { ?>
						<span class="pull-right fadedText">Select patient</span><?php } ?>
					<input type="hidden" required="required" name="patient" value="<?= (isset($_GET['pid'])) ? $_GET['pid'] : '' ?>" id="patient" style="width: 100%" class="select2"></label>
			</li>
			<li class="row-fluid">
				<div class="span5"> Clinic:
					<span class="pull-right"><a href="javascript:" id="add_apt_clinic">Add Clinic</a></span>
					<label><input name="apt_clinic_id" type="hidden" id="apt_clinic_id" required placeholder="Select Appointment Clinic"></label>
				</div>
				<div class="span7">
					<label>Duration
						<span class="pull-right fadedText">(Minutes) Depends on the <u>Start of Day</u> variable</span>
						<!--<input type="number" min="1" step="1" name="duration" placeholder="minute(s)" value="0">-->
						<select name="duration" data-placeholder="minute(s)">
							<option>0</option>
							<option>15</option>
							<option>30</option>
							<option>45</option>
						</select>
					</label>
				</div>
			</li>
			<li>Appointment Date: <span id="actualDate"></span></li>
			<li></li>

			<li class="timePartCheck">
				<h5>Check In to: </h5>
			</li>
			<li class="timePartCheck">
				<label>Select Department <select name="did" id="department_id" ><!--required="required"-->
						<!--<option value="">All Departments</option>-->
						<?php foreach ($depts as $dept) { ?>
							<option value="<?= $dept->getId() ?>"><?= $dept->getName() ?></option>
						<?php } ?>
					</select></label>
			</li>
			<li class="timePartCheck">
				<?php
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/SubscribedDoctorDAO.php';
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
				$all_specialty = (new StaffSpecializationDAO())->getSpecializations(null);
				?>
				<label class="pull-right">
					<input type="checkbox" onchange="refreshSubscribersList(this)"> Show only subscribed consultants </label>
			</li>
			<li class="timePartCheck">
				<label>
					Who do you want to see?
					<select name="room_id" data-placeholder="select a specialty" class="srm"<?php if($requireSpecialty){?> required="required"<?php }?>> <!--required="required"-->
						<option value=""></option>
						<?php foreach ($all_specialty as $_) {
							$subscribed = (new SubscribedDoctorDAO())->getSubscriptionsBySpecialty($_->getId(), null);
							$subscribed_staff = count($subscribed) != 0 ? ' data-subscribed="true"' : ' data-subscribed="false"'; ?>
							<option value="<?= $_->getId() ?>"<?= $subscribed_staff ?>><?= $_->getName() ?></option>
						<?php } ?>
					</select>
				</label>
			</li>
			<li class="timePartCheck">
				<label class="pull-left-">
					<select name="checkin_type">
						<option value="">Regular Encounter / Initial consultation / Normal consultation</option>
						<option value="followUp">Brief / Follow Up Encounter</option>
						<option value="review">Investigation Review</option>
					</select>
				</label>
			</li>
			<li class="timePartCheck">
				<label>Referred By <select name="referrer_id" data-placeholder="Select referring entity where applicable">
						<option></option>
						<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
							<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
							)</option><?php } ?>
					</select> </label>
			</li>
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

			<li>
				<button class="btn" type="submit">Schedule</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
				<a class="pull-right" onclick="goToAdvanced(this)" href="javascript:;" data-href="/appointments/boxy_createAppointment_advanced.php<?php if (isset($_GET['pid'])) { ?>?pid=<?= $_GET['pid'] ?><?php } ?>">Advanced
					View</a>
			</li>
		</ul>
	</form>
	<script>
		var app = {
			sdates: [],
			edates: [],
			freq: "",
			clinic: "<?= isset($_GET['clinic']) ? $_GET['clinic'] : '' ?>",
			patient: "",
			staffs: [],
			resource: "",
			description: "",
			allDay: false,
			forced: false
		};
		var clinics = JSON.parse('<?= json_encode($apt_clinics)?>');

		$(document).ready(function () {

			setTimeout(function () {
				$('#apt_clinic_id').select2({
					width: '100%',
					allowClear: true,
					data: function () {
						return {results: clinics, text: 'name'};
					},
					formatResult: function (source) {
						return source.name;
					},
					formatSelection: function (source) {
						return source.name;
					}
				});
				<?php if(isset($_GET['startVal']) && $_GET['startVal'] !== date('Y-m-d')){?>
				$('#immediate_check_in').prop('checked', true).iCheck('update').trigger('change');
				<?php } else {?>
				$('#immediate_check_in').trigger('change');
				<?php }?>
			}, 0);



			$("#patient").select2({
				placeholder: "Patient Name (Patient ID [Patient Legacy ID])",
				allowClear: true,
				minimumInputLength: 3,
				formatResult: function (data) {
					return data.fullname + " (" + data.id + " [" + data.lid + "]), Phone: " + data.phone;
				},
				formatSelection: function (data) {
					return data.fullname + " (" + data.id + " [" + data.lid + "])";
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the patient name or ID";
				},
				ajax: {
					// instead of writing the function to execute the request we use Select2's convenient helper
					url: '/api/search_patients.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							limit: 100,
							asArray: true
						};
					},
					results: function (data, page) { // parse the results into the format expected by Select2.
						// since we are using custom formatting functions we do not need to alter remote JSON data
						return {results: data};
					}
				},
				initSelection: function (element, callback) {
					var pid = $("#patient").val();
					if (pid.trim() !== "") {
						$.ajax("/api/search_patients.php", {
							data: {pid: pid, asArray: true},
							dataType: "json"
						}).done(function (data) {
							//$("#type option[value='Meeting']").prop('disabled', true);
							$("#patient").prop('readonly', true);
							callback(data);
						});
					}
				}
			});//End Patient Select2

			// consultant cost
			$('select[name="room_id"]').on('change', function (d) {
				console.log(d)
			})
			

		}).on('change', '[name="frequency"]', function () {
			if ($('[name="frequency"]').val() === 0) {
				$('#immediate_check_in').prop('checked', true).iCheck('update');
			} else {
				$('#immediate_check_in').prop('checked', false).iCheck('update');
			}
			updateDisplay();
		}).on('change', '#immediate_check_in', function () {
			if ($('#immediate_check_in').is(':checked')) {
				$('.timePart').hide();
				$('.timePartCheck').show();
				$('[name="room_id"]').prop('required', true).iCheck('update');
			} else {
				$('.timePart').show();
				$('.timePartCheck').hide();
				$('[name="room_id"]').prop('required', false).iCheck('update');
			}
			updateDisplay();
		}).on('change', 'select[name="interval"]', function () {
			updateDisplay();
		}).on('click', '#add_apt_clinic', function (e) {
			if (!e.handled) {
				Boxy.load('/appointments/add_clinic.php', {
					title: 'Add Appointment Clinic', afterHide: function () {
						reloadClinics();
					}
				});
				e.handled = true;
			}
		});

		function reloadClinics() {
			$.getJSON('/api/get_apt_clinics.php', function (data) {
				$('#apt_clinic_id').select2("val", "");
				clinics = data;
			})
		}

		

		function goToAdvanced(obj) {
			var oldBoxy = Boxy.get($('.close'));
			var oldCallBack = oldBoxy.options.afterHide;
			oldBoxy.options.afterHide = function () {};
			oldBoxy.hideAndUnload();
			Boxy.load($(obj).data('href'), {title: 'Schedule new appointment: Advanced view', afterHide: oldCallBack})
		}
		function validate() {
			if ($("#apt_clinic_id").val() === "") {
				return false;
			}
			var duration = $('[name="duration"]').val();
			for (var i = 0; i < 1; i++) {//only once
				<?php if(!isset($_GET['startVal'])){?>
				//fixme
				app.sdates[i] = moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('YYYY-MM-DD HH:mm:00');
				<?php } else if(isset($_GET['startVal'])){?>
				app.sdates[i] = moment('<?=$_GET['startVal']?>')./**add(<?= $startOfDayHour?>, 'hours').*/format("YYYY-MM-DD HH:mm:ss");
				<?php }?>
				if(duration !== 0){
					app.edates[i] = moment(app.sdates[i]).add(duration, 'minutes').format('YYYY-MM-DD HH:mm:ss');
				} else {
					app.edates[i] = null;
				}
			}
			app.allDay = true;
			app.freq = "";
			app.patient = $("#patient").val();
			app.resource = "0";
			app.staffs = "<?=$_SESSION['staffID']?>";
			app.clinic = $("#apt_clinic_id").val();
			app.description = "auto-scheduled visit";
			return !($("#patient").val() === null || $("#patient").val().trim() === "");
		}

		
		function updateDisplay() {
			<?php if(isset($_GET['startVal'])){?>
			$('#actualDate').html(moment('<?=$_GET['startVal']?>').add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('dddd, MMMM Do YYYY'));
			<?php } else {?>
			$('#actualDate').html(moment().add(parseInt($('input[name="frequency"]').val()), $('select[name="interval"]').val()).format('dddd, MMMM Do YYYY'));
			<?php }?>
		}

		function refreshSubscribersList(s) {
			if (s.checked) {
				$('.srm').find('option[data-subscribed="false"]').attr('disabled', 'disabled');
			}
			else {
				$('.srm').find('option[data-subscribed="false"]').prop('disabled', false);
			}
			$('.srm').select2("val", "");
		}
		
	</script>
</section>