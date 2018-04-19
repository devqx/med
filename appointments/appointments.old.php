<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) exit ($protect->ACCESS_DENIED);

$_GET['appointableTypes'] = TRUE;
require $_SERVER['DOCUMENT_ROOT'] . '/api/get_options.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';
$apt_clinics = (new AptClinicDAO())->all();
?>
<div style="/*margin-top: 30px*/">
	<div id='wrap'>
		<div class="row-fluid">
			<label class="span6 offset6"><input id="filter_apt_clinic_id" required placeholder="Filter appointments by Clinic"></label></label>
		</div>
		<div id='calendar'></div>
		<div style='clear:both'></div>
	</div>
	<div id="color">
		<div></div>
	</div>
	<!--<div><iframe src="https://www.google.com/calendar/embed?src=pauldiconline%40gmail.com&ctz=Africa/Lagos" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe></div>-->
</div>

<style>
	#wrap {
		margin: 0 auto;
	}

	#external-events {
		float: left;
		width: 150px;
		padding: 0 10px;
		border: 1px solid #ccc;
		background: #eee;
		text-align: left;
	}

	#external-events h4 {
		font-size: 16px;
		margin-top: 0;
		padding-top: 1em;
	}

	.external-event { /* try to mimick the look of a real event */
		margin: 10px 0;
		padding: 2px 4px;
		background: #3366CC;
		color: #fff;
		font-size: .85em;
		cursor: pointer;
	}

	#external-events p {
		margin: 1.5em 0;
		font-size: 11px;
		color: #666;
	}

	#external-events p input {
		margin: 0;
		vertical-align: middle;
	}

	/*#calendar {*/
	/*float: right;*/
	/*width: 900px;*/
	/*}*/
	.test {
		color: yellow;
	}
</style>
<script>
	var clinics = JSON.parse('<?= json_encode($apt_clinics, JSON_PARTIAL_OUTPUT_ON_ERROR)?>');
	$(document).ready(function () {
		/* initialize the external events
		 -----------------------------------------------------------------*/
		$('#external-events div.external-event').each(function () {
			// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
			// it doesn't need to have a start or end
			var eventObject = {
				title: $.trim($(this).text()), // use the element's text as the event title
				className: 'test',
				textColor: 'black'
			};

			// store the Event Object in the DOM element so we can get to it later
			$(this).data('eventObject', eventObject);
		});


		/* initialize the calendar
		 -----------------------------------------------------------------*/
		var todayDate = moment().startOf('day');
		var YM = todayDate.format('YYYY-MM');
		var YESTERDAY = todayDate.clone().subtract(1, 'day').format('YYYY-MM-DD');
		var TODAY = todayDate.format('YYYY-MM-DD');
		var TOMORROW = todayDate.clone().add(1, 'day').format('YYYY-MM-DD');

		$('#calendar1').fullCalendar({
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,listDay'
			},
			editable: true,
			eventLimit: true, // allow "more" link when too many events
			navLinks: true,
		});
		
		$('#calendar').fullCalendar({
			navLinks: true,
			timeFormat: 'h(:mm)a',
			defaultView: 'month',
			eventLimit: true, // allow "more" link when too many events
			header: {
				left: 'month,agendaDay,agendaWeek,listDay',
				center: 'title',
				right: 'prev,next, today'
			},
			columnFormat: {
				month: 'ddd', // Mon
				week: 'ddd D/M', // Mon 9/7
				day: 'dddd D/M'  // Monday 9/7
			},
			//titleFormat: {
			//	month: 'MMMM YYYY', // September 2009
			//	week: "Do MMM YYYY", // Sep 13 2009
			//	day: 'dddd, Do MMMM YYYY' // Tuesday, Sep 8, 2009
			//},
			buttonText: {
				prev: 'Prev',
				next: 'Next',
				prevYear: 'Prev Year',
				nextYear: 'Next Year',
				today: 'Today',
				month: 'Month',
				week: 'Week',
				day: 'Day'
			},
			weekNumberTitle: 'week',
//            hiddenDays: [0],
			weekends: true, //ex/include Saturday and Sunday in the calandar
			weekMode: 'liquid', //Determines the number of weeks displayed in a month view. Also determines each week's height.
			weekNumbers: true,
			editable: false,
			droppable: true, // this allows things to be dropped onto the calendar !!!
			selectable: true,
			unselectAuto: false,
			selectHelper: true,
			dayClick: function (date, jsEvent, view) {
				x = date;
				y = jsEvent;
				z = view;
				$(this).css('background-color', '#BECEE1');
			},
			eventClick: function (event, jsEvent, view) {
				event.color = "";
				event.source = "";
				Boxy.load('/appointments/boxy_appointmentDetails.php?event=' + JSON.stringify(event)<?php echo(isset($_GET['pid']) ? "+'&pid=" . $_GET['pid'] . "'" : '')?>, {title: 'Appointment Details'});
			},
			select: function (start, end, jsEvent, view) {
				if (moment().isBefore(start) || moment().format("YYYY-MM-DD") === moment(start).format("YYYY-MM-DD")) {
					Boxy.load('/appointments/boxy_createAppointment.php?startVal=' + moment(start).format("YYYY-MM-DD")<?= isset($_GET['pid']) ? '+"&pid=' . $_GET['pid'] . '"' : ''?>, {
						unloadOnHide: true, title: 'Event Scheduler', afterHide: function () {
							try {
								loadAppointments();
							}	catch (except) {
								location.reload();
							}
						}
					});
				}
				var eventData;
				$('#calendar').fullCalendar('unselect');
			},
			drop: function (date) { // this function is called when something is dropped

				// retrieve the dropped element's stored Event Object
				var originalEventObject = $(this).data('eventObject');

				// we need to copy it, so that multiple events don't have a reference to the same object
				var copiedEventObject = $.extend({}, originalEventObject);

				// assign it the date that was reported
				copiedEventObject.start = date;

				// render the event on the calendar
				// the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
				$('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

				// is the "remove after drop" checkbox checked?
				if ($('#drop-remove').is(':checked')) {
					// if so, remove the element from the "Draggable Events" list
					$(this).remove();
				}

			},
			loading: function (bool) {
				$('#loading').toggle(bool);
			},
			eventSources: [
				{
					url: '/functions/appointment_processor.php?getAppointments=true&grouped=true<?= (isset($_GET['pid']) ? '&pid=' . $_GET['pid'] : '') ?>',
					error: function () {
						$('#script-warning').show();
					},
					data: function () { // a function that returns an object
						return {
							clinic_id: $('#filter_apt_clinic_id').val()
						}
					},
					success: function (d) {
						for (var i = 0; i < d.length; i++) {
							var colorCode = d[i].title.replace(/\(\d+\)/g,'') + "|" + d[i].color;
							if ($.inArray(colorCode, colorCodes) === -1) {
								colorCodes[colorCodes.length] = colorCode;
							}
						}
						var html = "";
						$.each(colorCodes, function (i, val) {
							html += "<div class='item'>" + val.split("|")[0] + ": <span style='background-color: " + val.split("|")[1] + ";'>&nbsp;</span></div>";
						});
						$("#color div").html(html);
					}
				}
			]
		});

		$("table.fc-header tbody tr td:first").html();

		$('#filter_apt_clinic_id').select2({
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
		}).change(function(e){
			var element = $("#calendar");
			element.fullCalendar('refetchEvents');
		});

	});
	var ff;
	var colorCodes = [];
</script>