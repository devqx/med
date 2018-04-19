<?php
$_GET['suppress'] = $_REQUEST['appointableTypes'] = TRUE;
require $_SERVER['DOCUMENT_ROOT'] . '/api/get_options.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/get_resources.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';

$apt_clinics = (new AptClinicDAO())->all();
if (!isset($_SESSION)) {
	session_start();
}
?>
<div style="width:700px">
	<!--<strong>Schedule an event/appointment</strong>-->
	<form action="." method="post">
		<hr>
		<ul style="list-style: none;padding:0;margin:0;">
			<li class="row-fluid">
				<span class="span12"><label>Repeat</label></span>
			</li>
			<li class="row-fluid">
				<label class="span3"> <input type="radio" name="repeatType" value="once" checked="checked"> Once</label>
				<label class="span3"> <input type="radio" name="repeatType" value="daily"> Daily</label>
				<!--<label style="width: 100px;display: inline-block"> <input type="radio" name="repeatType" value="weekly"> Weekly</label>-->
				<!--<label style="width: 100px;display: inline-block"> <input type="radio" name="repeatType" value="monthly"> Monthly</label>-->
				<label class="span3 offset3 pull-right"> <input type="checkbox" name="allDay" id="allDay" value="true"> All Day</label>
			</li>
			<li class="row-fluid">
				<label class="span3"><input type="text" name="start" id="start" readonly="readonly" placeholder="Select Start Date" value="<?= @$_REQUEST['startVal'] ?>"/></label>
				<label class="span3"><input type="text" name="stime" id="stime" readonly="readonly" placeholder="Start Time"/></label>
				<label class="span3"><input type="text" name="end" id="end" readonly="readonly" placeholder="Select End Date"/></label>
				<label class="span3"><input type="text" name="etime" id="etime" readonly="readonly" placeholder="End Time"/></label>
			</li>
			<li>
				<div data-class="rt-once" class="row-fluid">
					<label class="span12">Schedule time:
						<input type="text" name="once" id="once" style="width: 100%" readonly="readonly" value=""></label>
				</div>
				<div data-class="rt-daily" class="row-fluid">
					<label class="span12">Daily Schedule: </label>
				</div>
				<div data-class="rt-daily" class="row-fluid">
					<label class="span12"><select name="daily" id="daily" multiple="multiple">
							<option value="Monday">Monday</option>
							<option value="Tuesday">Tuesday</option>
							<option value="Wednesday">Wednesday</option>
							<option value="Thursday">Thursday</option>
							<option value="Friday">Friday</option>
							<option value="Saturday">Saturday</option>
							<option value="Sunday">Sunday</option>
						</select></label>
				</div>
				<div data-class="rt-weekly" class="row-fluid">
					<label class="span12">Weekly Schedule: </label>
					<label class="span12"><select name="daily" id="weekly" style="width: 100%;" multiple="multiple">
							<option value="Monday">Monday</option>
							<option value="Tuesday">Tuesday</option>
							<option value="Wednesday">Wednesday</option>
							<option value="Thursday">Thursday</option>
							<option value="Friday">Friday</option>
							<option value="Saturday">Saturday</option>
							<option value="Sunday">Sunday</option>
						</select></label>
				</div>
				<div data-class="rt-monthly">
					<label>Month Schedule: </label>
					<label><select name="monthly" id="monthly" style="width: 100%" multiple="multiple">
							<option value="1|sun">Every 1st Sunday of the month</option>
							<option value="1|mon">Every 1st Monday of the month</option>
							<option value="1|tus">Every 1st Tuesday of the month</option>
							<option value="1|wed">Every 1st Wednesday of the month</option>
							<option value="1|thu">Every 1st Thursday of the month</option>
							<option value="1|fri">Every 1st Friday of the month</option>
							<option value="1|sat">Every 1st Saturday of the month</option>
							<option value="2|sun">Every 2nd Sunday of the month</option>
							<option value="2|mon">Every 2nd Monday of the month</option>
							<option value="2|tus">Every 2nd Tuesday of the month</option>
							<option value="2|wed">Every 2nd Wednesday of the month</option>
							<option value="2|thu">Every 2nd Thursday of the month</option>
							<option value="2|fri">Every 2nd Friday of the month</option>
							<option value="2|sat">Every 2nd Saturday of the month</option>
							<option value="3|sun">Every 3rd Sunday of the month</option>
							<option value="3|mon">Every 3rd Monday of the month</option>
							<option value="3|tus">Every 3rd Tuesday of the month</option>
							<option value="3|wed">Every 3rd Wednesday of the month</option>
							<option value="3|thu">Every 3rd Thursday of the month</option>
							<option value="3|fri">Every 3rd Friday of the month</option>
							<option value="3|sat">Every 3rd Saturday of the month</option>
							<option value="4|sun">Every last Sunday of the month</option>
							<option value="4|mon">Every last Monday of the month</option>
							<option value="4|tus">Every last Tuesday of the month</option>
							<option value="4|wed">Every last Wednesday of the month</option>
							<option value="4|thu">Every last Thursday of the month</option>
							<option value="4|fri">Every last Friday of the month</option>
							<option value="4|sat">Every last Saturday of the month</option>
						</select></label>
				</div>
			</li>
			<li>Clinic: <span class="pull-right"><a href="javascript:" id="add_apt_clinic">Add Clinic</a></span>
				<label><input name="apt_clinic_id" type="hidden" id="apt_clinic_id" required placeholder="Select Appointment Clinic"></label></li>
			<li data-class="ps-patient"><label for="patient">Patient: </label>
				<label><input type="hidden" name="patient" value="<?= (isset($_GET['pid'])) ? $_GET['pid'] : '' ?>" id="patient" style="width: 100%" class="select2"></label>
			</li>
			<li data-class="ps-staff"><label for="staff">Participants/Attendants: </label>
				<label><input type="hidden" name="staff" id="staff" style="width: 100%" class="select2"></label>
			</li>
			<li data-class="ps-resource"><label for="resource">Resource: </label>
				<label><select multiple name="resource" id="resource" data-placeholder="--- select resource ---" style="width: 100%">
						<?php
						foreach ($resources as $res) {?><option value="<?=$res->getId()?>"><?=$res->getName()?></option>
						<?php }?>
					</select></label></li>
			<li><label for="description">Description/Reason: </label>
				<label><textarea name="description" id="description" style="width: 100%; max-width: 100%"></textarea></label>
			</li>
			<li>

			</li>
		</ul>
		<div class="btn-block" style="float:none">
			<button name="save" class="btn" id="save" type="button">Schedule</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	var clinics = JSON.parse('<?= json_encode($apt_clinics, JSON_PARTIAL_OUTPUT_ON_ERROR)?>');
	$(document).ready(function () {
		$('input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
		$('#save').click(function () {
			save();
		});

		if ($("#start").val().trim() !== "") {
			$('#once').val("Appointment time is in " + moment($("#start").val()).fromNow());
		}

		$("div[data-class*='rt']").hide('fast');
		$("div[data-class='rt-once']").show('fast');

		//$('li[data-class*="ps-"]').hide('fast');

		$('input[name="repeatType"]').change(function () {
			$("#end").val("");
			$("#etime").val("");
			$("div[data-class*='rt']").hide('fast');
			$("div[data-class='rt-" + $(this).val() + "']").show('fast');
		});

		$("#allDay").change(function () {
			if ($(this).is(":checked") && $("input[name='repeatType']:checked").val() === "once") {
				$("#etime, #stime").val("");
			}
		});

		$(function () {
			//7am till 6pm ?
			var times = ['7:00', '8:00', '9:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
			var now = new Date().toISOString().split('T')[0];
			$('#start').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				onShow: function (ct) {
					this.setOptions({minDate: now});
				},
				onChangeDateTime: function (t, e) {
					$('#once').val("Appointment time is " + moment($("#start").val() + " " + ($("#stime").val().trim() !== "" ? $("#stime").val() : "23:59")).fromNow());
					$("#end").val("");
				}
			});

			$('#stime').datetimepicker({
				format: 'H:i',
				formatDate: 'H:i',
        //allowTimes: times,
				//minTime: times[0],
				//maxTime: times[times.length - 1],
				datepicker: false,
				validateOnBlur: false,
				defaultSelect: false,
				onShow: function (ct) {
					this.setOptions({minDate: now});
				},
				onChangeDateTime: function (t, e) {
					$('#once').val("Appointment time is " + moment($("#start").val() + " " + ($("#stime").val().trim() !== "" ? $("#stime").val() : "23:59")).fromNow());
					$('#etime').val("");
				}
			});

			$('#end').datetimepicker({
				format: 'Y-m-d',
				formatDate: 'Y-m-d',
				timepicker: false,
				validateOnBlur: false,
				defaultSelect: false,
				onShow: function (ct) {
					//console.log($("#start").val().trim() !== "" ? getDate() : false)
					this.setOptions({
						minDate: $("#start").val().trim() !== "" ? getDate() : false
					});
				},
				onChangeDateTime: function () {
					if ($("#start").val().trim() !== "") {
						if (moment($("#start").val()).isAfter($("#end").val())) {
							$("#end").val("");
							return;
						}
					}
				}
			});
			$('#etime').datetimepicker({
				format: 'H:i',
				formatDate: 'H:i', 
				//allowTimes: times,
				datepicker: false,
				validateOnBlur: false,
				defaultSelect: false,
				onShow: function (ct) {
					this.setOptions({minTime: $('#stime').val()});
				}
			});
		});//End DateTimePicker Settings

		$("#apt_clinic_id").change(function () {
			Boxy.get($(".close")).center();
		});

		$("#month, #resource, #daily, #weekly, #monthly").select2();
		setTimeout(
			function () {
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
					ajax: {// instead of writing the function to execute the request we use Select2's convenient helper
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
								//$("#patient").prop('readonly', true);
								callback(data);
							});
						}
					}
				});

				$("#apt_clinic_id").select2({
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
				})
			}//End Patient Select2
			, 500);
		$("#staff").select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			minimumInputLength: 3,
			multiple: true,
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "])";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the staff name or ID or phone or specialization";
			},
			ajax: {
				url: '/api/search_staffs.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term, // search term
						limit: 100,
						asArray: true
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		}).change(function (e) {
			if (e.added) {
				$.getJSON('/api/check_staff_appointments_limit.php?staff_id=' + e.added.id, function (f) {
					if (f !== null) {
						Boxy.warn(f.message);
					}
				});
			}
		});//End Staff Select2
		
		$('#resource').change(function(e){
			//if(!e.handled){
			//	if(e.added){
			//
			//	}
			//	e.handled = true;
			//}
		});
	}).on('click', '#add_apt_clinic', function (e) {
		if(!e.handled){
			Boxy.load('/appointments/add_clinic.php', {title: 'Add Appointment Clinic', afterHide: function(){
				reloadClinics();
			}});
			e.handled = true;
		}
	});

	function reloadClinics() {
		$.getJSON('/api/get_apt_clinics.php', function (data) {
			$('#apt_clinic_id').select2("val","");
			clinics = data;
		});
	}
	function getDate() {
		var freq = $('input[type="radio"][name="repeatType"]:checked').val();
		var da = moment($("#start").val());
		if (freq === "once") {
			da = da.format("YYYY-MM-DD");
		} else if (freq === "daily") {
			da.add('days', 7);
			da = da.format("YYYY-MM-DD");
		} else if (freq === "monthly") {
			da.add('months', 1);
			da = da.format("YYYY-MM-DD");
		} else {
			da = false;
		}
		return da;
	}

	function save() {
		if (validate()) {
			$.ajax({
				url: '/functions/appointment_processor.php',
				type: 'POST',
				//dataType: 'json',
				data: {createAppointment: JSON.stringify(app)},
				beforeSend: function () {
				},
				success: function (d) {
					var data = d.split(":");
					if (data[0] === "success") {
						var queueId = data[2];
						if ($('#immediate_check_in').is(':checked')) {
							Boxy.load('/boxy.selectDepartment.php?pid=' + $("#patient").val() + '&qid=' + queueId, {title: 'Select Department'})
						} else {
							Boxy.info(d.split(":")[1], function () {
								Boxy.get($(".close")).hideAndUnload();
								try {
									var element = $("#calendar");
									element.fullCalendar('refetchEvents');
								}catch (exception){}
							});
						}
					} else if (data[0] === "warn") {
						fullIcon = '<img style="width:50px;margin-right:50px" src="data:image/png;base64,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">';
						Boxy.ask(fullIcon+data[1], ["Force Schedule", "Cancel"], function (answer) {
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
				},
				error: function (d) {
					Boxy.alert(d.split(":")[1]);
				}
			});
		}
	}

	function validate() {
		var freq = $('input[type="radio"][name="repeatType"]:checked').val();

		if (freq === "once") {
			if ($('#start').val().trim() !== "") {
				if (!$('#allDay').is(':checked')) {
					if (($('#end').val().trim() === "") || ($('#stime').val().trim() !== "" && $('#etime').val().trim() === "") || ($('#stime').val().trim() === "" && $('#etime').val().trim() !== "")) {
						Boxy.alert("Please select event time otherwise check 'All Day'");
						return false;
					} else if ($('#stime').val().trim() === "" || $('#etime').val().trim() === "") {
						Boxy.alert("Please select event time range otherwise check 'All Day'");
						return false;
					}
				}
				sdates = [];
				sdates[0] = moment($('#start').val()).format("YYYY-MM-DD") + ($('#stime').val().trim() !== "" ? " " + $('#stime').val() : "");
				edates[0] = $('#end').val().trim() === "" ? null : moment($('#end').val()).format("YYYY-MM-DD") + ($('#etime').val().trim() !== "" ? " " + $('#etime').val() : "");
				app.sdates = sdates;
				app.edates = edates;
			} else {
				Boxy.alert("Please select schedule date");
				return false;
			}
		} else if ($('#start').val().trim() !== "" && $('#end').val().trim() !== "") {
			if (($('#stime').val().trim() !== "" && $('#etime').val().trim() === "") || ($('#stime').val().trim() === "" && $('#etime').val().trim() !== "")) {
				Boxy.alert("Please select event time range (start and end time)");
				return false;
			} else {
				if (freq === "daily") {
					if ($('#daily').val() === null || $('#daily').val() === "") {
						Boxy.alert("Please select daily schedule day(s)");
						return false;
					}
					sd = startDate = moment($('#start').val());
					ed = endDate = moment(moment($('#end').val()).format("YYYY-MM-DD"));
					sdates = [];
					edates = [];
					days = $('#daily').val();
					for (var i = 0; i < days.length; i++) {
						sd = startDate = moment($('#start').val());
						di = $.inArray(days[i], allDays); //the index equivalent of the day
						d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
						//console.log(startDate.format("YYYY-MM-DD") + "-----IN------" + di + " ::: " + d.format("YYYY-MM-DD"));
						di = di + 7;
						if (sd.isAfter(d)) {
							d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
							sd = startDate;
							di + 7;
						}

						//console.log("--------------------" + days[i] + "---Start-------------------");
						if (!d.isBefore(endDate)) {
							Boxy.alert("Please extend your date range to at least one week");
							return false;
						}
						if ($('#etime').val().trim() === "") {
							while (d.isBefore(endDate)) {
								sdates[sdates.length] = d.format("YYYY-MM-DD");
								d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
								sd = startDate;
								di + 7;
							}
						} else {
							while (d.isBefore(endDate)) {
								sdates[sdates.length] = d.format("YYYY-MM-DD") + " " + $('#stime').val();
								dd = moment(d.format("YYYY-MM-DD") + " " + $('#stime').val()).add(subtractTime($('#etime').val().trim(), $('#stime').val().trim()));
								edates[edates.length] = dd.format("YYYY-MM-DD HH:mm");
								d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
								sd = startDate;
								di + 7;
							}
						}
						//console.log("--------------------" + days[i] + "---End-------------------" + startDate.format("YYYY-MM-DD"));
					}
					app.sdates = sdates;
					app.edates = edates;
				} else if (freq === "monthly") {
					if ($('#monthly').val() === null) {
						Boxy.alert("Please select monthly schedule day(s)");
						return false;
					}
					//console.log("--------------------MONTHLY-------------------");
					sd = startDate = moment($('#start').val());
					endDate = moment(moment($('#end').val()).format("YYYY-MM-DD"));
					sdates = [];
					days = $('#monthly').val();

					for (var i = 0; i < days.length; i++) {
						sd = startDate = moment($('#start').val());
						di = $.inArray(days[i], allDays); //the index equivalent of the day
						d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
						console.log(startDate.format("YYYY-MM-DD") + "-----IN------" + di + " ::: " + d.format("YYYY-MM-DD"));
						di = di + 7;
						if (sd.isAfter(d)) {
							d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
							sd = startDate;
							di + 7;
						}

						//console.log("--------------------" + days[i] + "---Start-------------------");
						while (d.isBefore(endDate)) {
							sdates[dates.length] = d.format("YYYY-MM-DD");
							d = moment(sd.day(di).format("YYYY-MM-DD")); //The date for the selected day[i]
							sd = startDate;
							di + 7;
						}
						//console.log("--------------------" + days[i] + "---End-------------------" + startDate.format("YYYY-MM-DD"));

					}
				}
			}
		} else {
			Boxy.alert("Please select date range (start and end dates)");
			return false;
		}

		if ($("#apt_clinic_id").val().trim() === "" || $('#apt_clinic_id').val() === "0") {
			Boxy.alert("Please select the appointment Clinic");
			return false;
		} else {
			app.clinic = $('#apt_clinic_id').val();
			app.allDay = $('#allDay').is(':checked');
			var clinicText = $("#apt_clinic_id").select2("data") ? $("#apt_clinic_id").select2("data").name : "" ;

			if ($("#patient").val() === null || $("#patient").val().trim() === "") {
				Boxy.warn("No patient was scheduled.");
				//return false;
				app.patient = "";
			} else {
				app.patient = $("#patient").val();
			}
			app.patient = $("#patient").val();
			app.staffs = $("#staff").select2("data");
			app.resource = $("#resource").val();
		}

		if ($("#description").val().trim() !== "") {
			app.description = $("#description").val();
		} else {
			Boxy.alert("Description required");
			return false;
		}
		return true;
	}

	function subtractTime(h2, h1) {
		h2 = parseInt(h2.split(":")[0]);
		h1 = parseInt(h1.split(":")[0]);
		h = h2 - h1;
		return moment.duration(h, "hours");
	}
	var allDays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
	var edates = [];
	var sdates = [];
	var app = {
		sdates: [],
		edates: [],
		freq: "",
		clinic: "",
		patient: "",
		staffs: [],
		resource: "",
		description: "",
		allDay: false,
		forced: false
	};
</script>
