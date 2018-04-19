<?php
$_GET['suppress'] = $_REQUEST['appointableTypes'] = TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_options.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_resources.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
$appoint = (new AppointmentDAO())->getAppointment($_REQUEST['id'], TRUE);
?>
<?php
$startTime = explode(" ", $appoint->getStartTime());
$endTime = explode(" ", $appoint->getEndTime()); ?>
<div>
	<div class="row-fluid">
		
		<div class="span6">From:
			<div class="row-fluid">
				<label class="span8"><input type="text" name="start" id="start" readonly="readonly" placeholder="Select Start Date" value="<?= $startTime[0] ?>"/></label>
				<label class="span4"><input type="text" name="stime" id="stime" readonly="readonly" placeholder="Start Time" value="<?= ((sizeof($startTime) > 1 && $startTime[1] !== null && $startTime[1] !== "") ? (sizeof(explode(":", $startTime[1])) === 3 ? explode(":", $startTime[1])[0] . ":" . explode(":", $startTime[1])[1] : $startTime[1]) : "") ?>"/></label>
			</div>
		</div>

		<div class="span6">To:
			<div class="row-fluid">
				<label class="span8"><input type="text" name="end" id="end" readonly="readonly" placeholder="Select End Date" value="<?= $endTime[0] ?>"/></label>
				<label class="span4"><input type="text" name="etime" id="etime" readonly="readonly" placeholder="End Time" value="<?= ((sizeof($endTime) > 1 && $endTime[1] !== null && $endTime[1] !== "") ? (sizeof(explode(":", $endTime[1])) === 3 ? explode(":", $endTime[1])[0] . ":" . explode(":", $endTime[1])[1] : $endTime[1]) : "") ?>"/></label>
			</div>
		</div>
	</div>
</div>
<label>
	<input type="checkbox" name="allDay" id="allDay" disabled="disabled" checked="<?= $appoint->getGroup()->isAllDay() == 1 ? "checked" : "" ?>">
	All Day
</label>
<ul class="appDetails">
	<li data-name="start">
	
	</li>
	<li>
		</li>
	<?php if ($appoint->getGroup()->getType() !== "Meeting") { ?>
		<li><label>Scheduled Patient:</label><?= $appoint->getGroup()->getPatient()->getFullname() ?>
			(<?= $appoint->getGroup()->getPatient()->getId() ?>)
		</li>

	<?php } ?>
	<li data-name="title"><label>Clinic:</label><?= $appoint->getGroup()->getClinic()->getName() ?>

	</li>
	<!--<li data-name="duration"><label>Resource:</label><span><?php echo date("M d, Y H:i", strtotime(($appoint->getEndTime() === null || $appoint->getEndTime() === "" ? "Not specified" : $appoint->getEndTime()))); ?></span><span style="display:none"><?= $appoint->getEndTime() ?></span> </li>-->
	<li><label>Participants:</label><?php
		if (sizeof($appoint->getGroup()->getInvitees()) > 0) {
			foreach ($appoint->getGroup()->getInvitees() as $invitee) {
				echo '<a href=javascript:">' . $invitee->getStaff()->getFullname() . '</a>, ';
			}
		} else {
			echo 'N/A';
		}
		?></li>
	<li id="appointment"><label>Status:</label><?= $appoint->getStatus() ?></li>
	<li><label>Description:</label><?= $appoint->getGroup()->getDescription() ?></li>
	<li><input type="hidden" name="aid" id="aid" value="<?= $_REQUEST['id'] ?>">
		<button name="save" class="btn" id="edit" type="button" onclick="saveEdit()">Save</button>
		<button name="save" class="btn-link" type="reset" onclick="Boxy.get(this).hide()">Cancel</button>
	</li>

</ul>
<script type="text/javascript">
	$(document).ready(function () {
		$('a[data-name="edit"]').click(function () {
		});
		$(function () {
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
					this.setOptions({
						minDate: $("#start").val().trim() !== "" ? $("#start").val().trim() : false
					});
				},
				onChangeDateTime: function () {
					if ($("#start").val().trim() !== "") {
						if (moment($("#start").val()).isAfter($("#end").val())) {
							$("#end").val("");
							return;
						}
						//                       $.growlUI("Please select Insurance Scheme first");
						//                        window.location.href="/pm/reporting/bill.php?from="+$("#from").val()+"&to="+$("#to").val();
					}
				},
			});
			$('#etime').datetimepicker({
				format: 'H:i',
				formatDate: 'H:i',
				datepicker: false,
				validateOnBlur: false,
				defaultSelect: false,
				onShow: function (ct) {
					this.setOptions({minTime: $('#stime').val()});
				},
			});
		});//End DateTimePicker Settings

	});

	function saveEdit() {
		if (validateEdit()) {
			others = "&aid=" + $('#aid').val().trim();
			others = others + "&start=" + $('#start').val().trim() + (($('#stime').val().trim() === "") ? "" : " " + $('#stime').val().trim());
			others = others + (($('#end').val().trim() === "") ? "" : "&end=" + ($('#end').val().trim() + (($('#etime').val().trim() === "") ? "" : " " + $('#etime').val().trim())));
			$.ajax({
				url: '/functions/appointment_processor.php?editAppointment=true' + others,
				type: 'GET',
				dataType: 'json',
				beforeSend: function () {
//                    alert(others)
				},
				success: function (d) {
					if (d.indexOf("success") !== -1) {
						Boxy.info(d.split(":")[1], function () {
							window.location.href = ".";
						});
					} else {
						Boxy.alert(d.split(":")[1]);
					}
				},
				error: function (d) {
					Boxy.alert("Sorry internal error occured");
				}
			});
		}
	}

	function validateEdit() {
		if ($('#start').val().trim() !== "") {
			if (!$('#allDay').is(':checked')) {
				if (($('#end').val().trim() === "") || ($('#stime').val().trim() !== "" && $('#etime').val().trim() === "") || ($('#stime').val().trim() === "" && $('#etime').val().trim() !== "")) {
					Boxy.alert("Please select event time");
					return false;
				} else if ($('#stime').val().trim() === "" || $('#etime').val().trim() === "") {
					Boxy.alert("Please select event time range");
					return false;
				}
			} else {
				if ($('#end').val().trim() !== "") {
					if (($('#stime').val().trim() !== "" && $('#etime').val().trim() === "") || ($('#stime').val().trim() === "" && $('#etime').val().trim() !== "")) {
						Boxy.alert("Please select event time");
						return false;
					}
				} else {
//                    if(($('#stime').val().trim() !== "" && $('#etime').val().trim() === "") || ($('#stime').val().trim() === "" && $('#etime').val().trim() !== "")){
//                        Boxy.alert("Please select event time");
//                        return false;
//                    }
				}
			}
		} else {
			Boxy.alert("Please select schedule date");
			return false;
		}
		return true;
	}
</script>
