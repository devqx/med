
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DischargedNoteTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$specialties = (new StaffSpecializationDAO)->getSpecializations();
$template = (new DischargedNoteTemplateDAO())->all();
$pid = (new InPatientDAO())->getInPatient($_REQUEST['aid'], FALSE)->getPatient()->getId();

?>
<section style="width: 850px;">
	Discharge Patient:
	<div class="row-fluid">
		<label class="span4 menu-head small"><input name="type_" type="radio" value="normal"> Normal Discharge</label>
		<label class="span4 menu-head small"><input name="type_" type="radio" value="referral"> Refer to another
			facility</label>
		<label class="span4 menu-head small"><input name="type_" type="radio" value="death"> Patient is dead</label>
	</div>
	<hr class="border">
	<form class="hide" data-type="normal" method="post" action="/admissions/ajax.place-discharge.php?aid=<?= $_REQUEST['aid'] ?>&appointment_id=<?= $_REQUEST['appointment_id'] ?>&nextMedication=<?= $_REQUEST['nextMedication'] ?>&reason=<?= $_REQUEST['reason'] ?>" onsubmit="return AIM.submit(this, {onStart: start, onComplete: done});">
		<span id="_output"></span>
		<label>Template<span class="pull-right"><i class="icon-question-sign"></i><a href="javascript:;" class="discharged_note_template_link" data-href="template_help.php">help</a>
		        <i class="icon-plus-sign"></i><a href="javascript:;" class="discharged_note_template_link" data-href="discharge_note_template_new.php">add to list</a>
            </span></label>
		<select id="discharge_template_id" name="discharge_template_id" data-placeholder="select custom discharge text">
			<option></option>

			<?php foreach ($template as $tpl => $t) { ?>
				<option value="<?= $t->getId() ?>" data-text="<?= $t->getContent() ?>"><?= $t->getTitle() ?></option>
			<?php } ?>


		</select>
		<label>
			Discharge note
			<textarea id="discharge_note" name="reason" placeholder="template content here"></textarea>
		</label>
		<label class="menu-head">
			<input type="checkbox" name="express_discharge" id="express_discharge">
			Express Discharge
		</label>
		<div class="row-fluid ">
			<label class="span3 drop-btn" onclick="addPrescription()">
				Add New Medication
			</label>
			<label class="span3 drop-btn" onclick="addAppointment()">
				Book New Appointment
			</label>
		</div>
		
		
		<label><span class="clear"></span></label>
		<div class="btn-block">
			<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
			 <input type="hidden" name="appointment_id">
			 <input type="hidden" name="nextMedication">

			<button class="btn" type="submit">Save</button>
			<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
	<div class="hide" data-type="referral">
		<form method="post" onsubmit="return AIM.submit(this, {onStart : startSaving, onComplete: saveComplete})" action="/boxy.transferPatient.php">
			<div>
				<div class="loader"></div>
				<label>Specialization <select name="specialization_id" data-placeholder="Refer to specialty [if applicable]">
						<option></option>
						<?php foreach ($specialties as $specialty) {?>
							<option value="<?= $specialty->getId()?>"><?= $specialty->getName() ?></option>
						<?php }?>
					</select></label>
				<div class="clear"></div>
				<div class="row-fluid clear">
					<label class="span6"><input type="radio" name="type" value="internal"> Within this facility</label>
					<label class="span6"><input type="radio" name="type" value="external"> To another facility</label>
				</div>
				<label>
					Referral Note:
					<textarea placeholder="type here" name="note_" id="note_"></textarea>
					<input type="hidden" name="pid" value="<?= $_REQUEST['pid'] ?>">
				</label>
				<label class="menu-head">
					<input type="checkbox" name="express_discharge" id="express_discharge_referral">
					Express Discharge
				</label>
				<div class="clear"></div>
			</div>

			<div class="btn-block pull-right_">
				<div align="right_">
					<button class="btn" id="btn$" type="submit">Save</button>
					<button class="btn-link" type="button" onclick="Boxy.get(this).hide()">Cancel</button>
				</div>
			</div>
		</form>
		<script>
			function startSaving() {
				$('.loader').html('<img src="/img/loading.gif"/> Please Wait...');
			}

			function saveComplete(s) {
				var dat = s.split(":");
				if (dat[0] === "error") {
					$('.loader').html('<span class="warning-bar">' + dat[1] + '</span>');
				} else if (dat[0] === "success") {
					if($('#express_discharge_referral').is(':checked')){
						$.post('/admissions/express_discharge.php', {aid: <?= $_REQUEST['aid']?> }, function(data){

						});
					}else{
					$.post('/admissions/ajax.place-discharge.php', {
						reason: 'Referral',
						aid: <?= $_REQUEST['aid'] ?>,
						appointment_id: '',
						nextMedication: ''
					});
			    }
					Boxy.info(dat[1], function () {
						Boxy.get($('.close')).hideAndUnload();
						location.reload();
					});
				}
			}

			$('label.span6 > input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function(event){
				$(event.currentTarget).trigger('change');
			});

			// on select drop down box add the text into textarea
			$('.boxy-content #discharge_template_id').select2().change(function (data) {
				if (data.added !== undefined) {
					var content = $(data.added.element).data("text");
					$('textarea[name="reason"]').code(content).focus();
				} else {
					$('textarea[name="reason"]').code('').focus();
				}
			}).trigger('change');

			$('.boxy-content  a.discharged_note_template_link').click(function () {
				Boxy.load("/admissions/" + $(this).data("href"));
			});

			$(document).ready(function () {
				$('#note_').summernote(SUMMERNOTE_CONFIG);
				$('#discharge_note').summernote(SUMMERNOTE_CONFIG);
			});

			function refreshTemplates() {
				$.ajax({
					url: '/api/get_discharge_note_template.php',
					dataType: 'json',
					complete: function (s) {
						var data = s.responseJSON;
						// console.log(data);
						var str = '<option></option>';
						for (var i = 0; i < data.length; i++) {
							str += '<option value="' + data[i].id + '" data-text="' + data[i].content + '">' + data[i].title + '</option>';
						}
						$('#discharge_template_id').html(str);

					}
				});
			}

		</script>
	</div>
	<form class="hide" data-type="death" action="<?=$_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _deceasePatient, onComplete: _deceasedPatient})">
		<label><input type="checkbox" checked name="deceased"> Mark Patient as Deceased </label>
		<label>Date/Time of Death<input type="text" name="datetime_of_death" required> </label>
		<label>Discharge Note <input type="text" required name="reason"> </label>
		<input type="hidden" name="aid" value="<?= $_REQUEST['aid'] ?>">
		<label class="menu-head">
			<input type="checkbox" name="express_discharge" id="express_discharge_dead">
			Express Discharge
		</label>
		<div class="brn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>

<script type="text/javascript">
	var appt = '';
	var nextmedic = '';
	var reason = '';
	$('input[name="type_"][type="radio"]').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
		$(event.currentTarget).trigger('change');
	});
	
	$('input[name="datetime_of_death"]').datetimepicker({
		format: 'Y-m-d H:i:s',
		formatDate: 'Y-m-d H:i:s'
	});
	$(document).on('change', 'input[name="type_"][type="radio"]', function () {
		var $which = $(this).val();
		$('[data-type]').addClass('hide');
		$('[data-type="' + $which + '"]').removeClass('hide');
	});

	function _deceasedPatient(e) {
		//console.log(e);
		//just a placeholder
	}

	function _deceasePatient() {
		var form = $('form[data-type="death"]');
		$.post('/api/decease_patient.php', {
			patient_id: <?=$pid?>,
			datetime_of_death: $('input[name="datetime_of_death"]').val(),
			aid: <?= $_REQUEST['aid'] ?>
		}, function (data) {
			if (data === 'true') {
				if($('#express_discharge_dead').is(':checked')){
					$.post('/admissions/express_discharge.php', {aid: <?= $_REQUEST['aid'] ?>}, function(data){

					});
				}else{
				$.post('/admissions/ajax.place-discharge.php', {
					reason: form.serializeObject()['reason'],
					aid: <?= $_REQUEST['aid'] ?>,
					appointment_id: '',
					nextMedication: ''
				});
			}
				Boxy.info("Draft Death Certificate has been generated", function () {
					location.reload();
				});
			} else {
				Boxy.alert("Operation failed");
			}
		});
	}
	function start() {
		$('#_output').html('<img src="/img/loading.gif"> Please wait...').attr('class', '')
	}
	function done(str) {
		var s = str.split(":");
		 appt = $('input[name="appointment_id"]').val();
		 nextmedic = $('input[name="nextMedication"]').val() ;
		 reason = $('textarea[name="reason"]').val();
		if (s[0] === "error") {
			$('#_output').html(s[1]).attr('class', 'warning-bar');
		} else if (s[0] === "ok") {
			console.log("appt"+appt);
			console.log("medic"+nextmedic);
			if($('#express_discharge').is(':checked')){
				$.post('/admissions/express_discharge.php', { aid: <?= $_REQUEST['aid'] ?>, reason:reason, appointment_id: appt, nextMedication: nextmedic});
			}
			Boxy.info("Patient is now discharged", function () {
				Boxy.get($(".close")).hideAndUnload();
				
			});
		}
	}
	

	function save_() {
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

	function processReturn(data_){
		var d = data_.replace(/"/g, '');
		var data = d.split(":");
		if (data[0] === "success") {
			Boxy.info(d.split(":")[1], function () {
				Boxy.get($(".close")).hideAndUnload();
				try {
					var element = $("#calendar");
					element.fullCalendar('refetchEvents');
				}catch (exception){}
			});
			processAppendNote(data_);
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

function processAppendNote(id) {
  var d = id.split(":");
  $('input[name="appointment_id"]').val(d[2]);
}

function addPrescription() {
			Boxy.load("boxy.add_regimen.php?id=<?= $pid ?>", {
				title: "Add medication", afterHide: function () {
				}
			});
	
}

function addAppointment() {
			Boxy.load("boxy.addAppointment.php?pid=<?= $pid ?>", {
				title: "Add appointment", afterHide: function () {
				}
			});
}
</script>