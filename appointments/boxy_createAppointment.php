<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/22/14
 * Time: 9:34 AM
 *
 * moment().add( 1, 'days').format('YYYY-MM-DD HH:mm:ss')
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
	<form method="post" name="addAppointmentSimple" action="." onsubmit="return AIM.submit( this, {onStart: save(), onComplete:null})">
        <label class="output well well-small"></label>
        <ul class="appointmentNewContainer">
			<li><label><input type="checkbox" name="immediate_check_in" id="immediate_check_in" checked="checked"> Check in
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
					<!--<input type="checkbox" name="followUp"> Follow-Up consultation-->
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
				$('#immediate_check_in').prop('checked', false).iCheck('update').trigger('change');
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

		function save() {
			if (validate()) {
				if ($('#immediate_check_in').is(':checked')) {
					$('select[name="room_id"]').prop('required', false);
					$('#department_id').prop('required', false);
					$.post('/appointments/one-way-check-in.php', {
						patient_id: $('#patient').val(),
						staff_id: '<?= $_SESSION['staffID']?>',
						appointment: JSON.stringify(app),
						did: $('#department_id').val(),
						room_id: $('select[name="room_id"]').val(),
						followUp: $('[name="checkin_type"]').val()==="followUp",
						review: $('[name="checkin_type"]').val()==="review",
						referrer_id: $('select[name="referrer_id"]').val()
					}, function (data) {
						processReturn(data);
					})
				} else {
					$('select[name="room_id"]').prop('required', true);
					$('#department_id').prop('required', true);
					$.ajax({
						url: '/functions/appointment_processor.php',
						type: 'POST',
						data: {createAppointment: JSON.stringify(app)},
						beforeSend: function () {
						},
						success: function (d) {
							processReturn(d);
						},
						error: function (d) {
							Boxy.alert(d.split(":")[1]);
						}
					});
				}

			} else {
				Boxy.alert("Failed to create appointment");
			}
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

		function processReturn(data_){
			var d = data_.replace(/"/g, '');
			var data = d.split(":");
			if (data[0] === "success") {
				location.reload();
				Boxy.info(d.split(":")[1], function () {
					Boxy.get($(".close")).hideAndUnload();
					try {
						var element = $("#calendar");
						element.fullCalendar('refetchEvents');
					}catch (exception){}
				});
			} else if (data[0] === "warn") {
				fullIcon = '<img style="width:50px;margin-right:50px" src="data:image/png;base64,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">';
				Boxy.ask(fullIcon + data[1], ["Force Schedule", "Cancel"], function (answer) {
					if (answer !== "Cancel") {
						app.forced = true;
						save();
					} else {
						Boxy.get($(".close")).hideAndUnload();
					}
				})
			} else if (data[0] === "error") {
				Boxy.alert(data[1]);
			}
		}

//		$('select[name="room_id"]').on('change', function () {
//            if($(this).val !== " "){
//                var id = $(this).val;
//                $.ajax({
//                   url : "/api/get_consultantsPriceByCode.php",
//                    type: 'post',
//                    data: {'id': id},
//
//                    complete: function (data) {
//                        console.log(data);
//                    }
//                });
//
//            }
//        })
	</script>
</section>