<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
sessionExpired();
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->records) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) {
	exit($protect->ACCESS_DENIED);
} ?>
<div id="billMenuBar" class="mini-tab menu-line">
	<a class="tab on" href="javascript:" id="appointmentTab">Appointments</a>
	<a class="tab" href="javascript:" id="personalCalendar">Personal Calendar</a>
	<a class="tab" href="javascript:" id="resourceCalendar">Resources Calendar</a>
	<a class="tab" href="javascript:" id="lostFollowupTab">Loss Follow ups</a>
</div>

<div id="appointmentsContent" class="document"></div>

<script type="text/javascript">
	<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
	$resources = (new ResourceDAO())->getResources();
	?>
	var RESOURCES = <?= json_encode($resources, JSON_PARTIAL_OUTPUT_ON_ERROR) ?>;
	$(document).ready(function () {
		loadAppointments();
		$("#lostFollowupTab").click(function () {
			$.get("/appointments/lostFollowUps.php", function (data) {
				$('#appointmentsContent').html(data);
				$('a.tab').removeClass('on');
				$('#lostFollowupTab').addClass('on');
			});
		});

		$("#appointmentTab").click(function () {
			loadAppointments();
		});

		$("#personalCalendar").click(function () {
			loadPersonalAppointments();
		});

		$("#resourceCalendar").click(function () {
			loadResourceAppointments();
		});
	});

	function loadAppointments() {
		$.get("/appointments/appointments.php", function (data) {
			$('#appointmentsContent').html(data);
			$('a.tab').removeClass('on');
			$('#appointmentTab').addClass('on');
		});
	}

	function loadPersonalAppointments(staffId) {
		if (staffId !== "" && staffId !== undefined) {
			url = "/appointments/personal_appointments.php?staff_id=" + staffId;
		} else {
			url = "/appointments/personal_appointments.php";
		}
		$.get(url, function (data) {
			$('#appointmentsContent').html(data);
			$('a.tab').removeClass('on');
			$('#personalCalendar').addClass('on');
			$('[name="staff_id"]').select2({
				placeholder: "Filter appointments by Staff",
				allowClear: true,
				width: '100%',
				minimumInputLength: 3,
				formatResult: function (data) {
					return data.fullname;
				},
				formatSelection: function (data) {
					return data.fullname;
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the staff name or ID or phone or specialization";
				},
				initSelection: function (element, callback) {
					var id;
					id = $(element).val();
					if (id !== "") {
						return $.ajax({
							url: '/api/search_staffs.php',
							type: "GET",
							dataType: "json",
							data: {
								q: id,
								limit: 100,
								asArray: true
							}
						}).done(function (data) {
							callback(data[0]);
						});
					}
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
			}).change(function (thisItem) {
				if (thisItem.val !== "") {
					loadPersonalAppointments(thisItem.val);
				} else {
					loadPersonalAppointments();
				}
			});//End Staff Select2
		});
	}

	function loadResourceAppointments(resourceId) {
		if (resourceId !== "" && resourceId !== undefined) {
			url = "/appointments/resources_appointments.php?resource_id=" + resourceId;
		} else {
			url = "/appointments/resources_appointments.php";
		}
		$.get(url, function (data) {
			$('#appointmentsContent').html(data);
			$('a.tab').removeClass('on');
			$('#resourceCalendar').addClass('on');
			$('[name="resource_id"]').select2({
				placeholder: "Filter by Scheduled Resource",
				allowClear: true,
				width: '100%',
				minimumInputLength: 0,
				formatResult: function (data) {
					return data.name;
				},
				formatSelection: function (data) {
					return data.name;
				},
				initSelection: function (element, callback) {
					var id;
					id = $(element).val();
					if (id !== "") {
						return $.ajax({
							url: '/api/get_resources.php',
							type: "GET",
							dataType: "json",
							data: {
								id: id
							}
						}).done(function (data) {
							callback(data);
						});
					}
				},
				data: function () {
					return {results: RESOURCES, text: 'name'};
				}
			}).change(function (thisItem) {
				if (thisItem.val !== "") {
					loadResourceAppointments(thisItem.val);
				} else {
					loadResourceAppointments();
				}
			});//End Resources Select2
		});
	}
</script>