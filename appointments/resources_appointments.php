<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/13/17
 * Time: 10:26 AM
 */
?>
<div class="row-fluid">
	<h4 class="span6">Resources Calendar</h4>
	<label class="span6"><input type="text" name="resource_id" value="<?= isset($_REQUEST['resource_id']) ? $_REQUEST['resource_id'] : "" ?>"></label>
</div>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) exit ($protect->ACCESS_DENIED);

$_GET['appointableTypes'] = TRUE;
require $_SERVER['DOCUMENT_ROOT'] . '/api/get_options.php';
?>
<div style="/*margin-top: 30px*/">
	<div id='wrap'>
		<div id='calendar'></div>
		<div style='clear:both'></div>
	</div>
	<div id="color">
		<div></div>
	</div>
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
	#color{margin-top: 20px;}
	
	#color span{
		min-width: 50px;
		display: inline-block;
	}
	#color > div > .item {
		display: inline-block !important;
		margin-right: 20px;
	}
</style>
<script>

	$(document).ready(function () {
		/* initialize the calendar
		 -----------------------------------------------------------------*/

		$('#calendar').fullCalendar({
			header: {
				left: 'prev,next, today',
				right: 'month,agendaWeek,agendaDay,listDay',
				center: 'title'
			},
			eventLimit: true, // allow "more" link when too many events
			navLinks: true,
			timeFormat: 'h(:mm)t',
			defaultView: 'month',
			buttonText: {
				today: 'Today',
				month: 'Month',
				week: 'Week',
				day: 'Day',
				list: 'List'
			},
			slotDuration: '00:15:00',
			weekNumberTitle: 'wk',
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
//                        $(this).css('background-color', 'red');
			},
			eventClick: function (event, jsEvent, view) {
				event.color = "";
				event.source = "";
				Boxy.load('/appointments/boxy_appointmentDetails.php?event=' + JSON.stringify(event)<?php echo(isset($_GET['pid']) ? "+'&pid=" . $_GET['pid'] . "'" : '')?>, {title: 'Appointment Details'});
			},
			select: function (start, end, jsEvent, view) {
				if (moment().isBefore(start) || moment().format("YYYY-MM-DD") === moment(start).format("YYYY-MM-DD")) {
					Boxy.load('/appointments/boxy_createAppointment.php?startVal=' + moment(start).format("YYYY-MM-DD")<?= isset($_GET['pid']) ? '+"&pid=' . $_GET['pid'] . '"' : ''?>, {
						unloadOnHide: true,
						title: 'Event Scheduler',
						afterHide: function () {
							try {var element = $('#calendar');element.fullCalendar('refetchEvents');}catch (exception){}
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
				if(bool){
					$(document).trigger('ajaxSend');
				} else {
					$(document).trigger('ajaxStop');
				}
				//$('#loading').toggle(bool);
			},
			eventSources: [
				{
					url: '/functions/appointment_processor.php?getResourcesAppointments=true<?= (isset($_GET['resource_id']) ? '&resource_id=' . $_GET['resource_id'] : '') ?>',
					error: function () {
						$('#script-warning').show();
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
							html = html + val.split("|")[0] + ": <span style='background-color: " + val.split("|")[1] + ";'>&nbsp;</span> ";
						});
						$("#color div").html(html);
					}
				}
			]
		});
		$("table.fc-header tbody tr td:first").html();
	});
	var ff;
	var colorCodes = [];
</script>