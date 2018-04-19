<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
//    require $_SERVER['DOCUMENT_ROOT'].   '/api/get_resources.php';

$event = json_decode($_REQUEST['event']);
if (isset($_GET['pid'])) {
	$appoints = (new AppointmentDAO())->getPatientAppointments($_GET['pid'], $event->aid, TRUE);
} else if ($event->aids) {
	$ids = implode(",", $event->aids);
	$appoints = (new AppointmentDAO())->getAppointments($ids, TRUE);
} else {
	$appoints = (new AppointmentDAO())->getAppointments($event->aid, TRUE);
}

/*if (isset($_GET['pid'])) {
	$appoints = (new AppointmentDAO())->getPatientAppointments($_GET['pid'], $event->aid, TRUE);
} else if ($event->count > 1) {
	$appoints = (new AppointmentDAO())->getAppointments($event->aids, TRUE);
} else {
	$appoints = (new AppointmentDAO())->getAppointments($event->aid, TRUE);
}*/
$appoint = $appoints[0];
//error_log(json_encode($appoint->getGroup()->getResource()));
//exit;
if (sizeof($appoints) === 1) {
	?>
	<section style="width: 700px;">
		<div data-name="appDetails" style="margin: 10px 20px">
			<div style="float:left"><strong>Appointment Details:</strong></div>
			<br/>
			<ul class="appDetails">
				<li data-name="title">
					<label>Clinic:</label><span><?= $appoint->getGroup()->getClinic() ? $appoint->getGroup()->getClinic()->getName() : "N/A" ?></span>
				</li>
				<li><label>Scheduled Patient:</label><span><?php if($appoint->getGroup()->getPatient()){?><a href="/patient_profile.php?id=<?= $appoint->getGroup()->getPatient()->getId() ?>" title="View patient Profile"><?= $appoint->getGroup()->getPatient()->getFullname() ?>
								(<?= $appoint->getGroup()->getPatient()->getId() ?>)</a><?php } else {?>- - <?php }?></span></li>
				<li data-name="start"><label>Date:</label><span><?php
						$res = explode(" ", $appoint->getStartTime());
						echo !isset($res[1]) || $res[1] === null || $res[1] === "" || $res[1] == "00:00:00" || $res[1] === "00:00" ? date("M d, Y", strtotime($res[0])) : date("M d, Y H:i", strtotime($appoint->getStartTime()))
						?></span> <span style="display:none"><?= $appoint->getStartTime() ?></span></li>
				<li data-name="duration">
					<label>Duration:</label><span><?php echo date("M d, Y H:i", strtotime(($appoint->getEndTime() === null || $appoint->getEndTime() === "" ? "Not specified" : $appoint->getEndTime()))); ?></span><span style="display:none"><?= $appoint->getEndTime() ?></span>
				</li>
				<li><label>Participants:</label><?php
					if (sizeof($appoint->getGroup()->getInvitees()) > 0) {?>
						<ul>
							<?php foreach ($appoint->getGroup()->getInvitees() as $invitee) { ?>
								<li><a href="javascript:"><?= $invitee->getStaff()->getFullname() ?></a></li>
							<?php } ?>
						</ul>
					<?php } else { ?>
						- -
					<?php } ?>
				</li>
				<li>
					<label>Resource(s): </label>
					<?php if(count($appoint->getGroup()->getResource())>0){?>
						<ul style="margin-left: 17em;">
						<?php foreach ($appoint->getGroup()->getResource() as $res){//$res=new AppointmentResource();?>
						<li class="fadedText"><?= $res->getResource()->getName();?></li>
						<?php }?>
						</ul>
					<?php } else {?>
					- - <?php }?>
				</li>
				<li id="appointment"><label>Status:</label><?= $appoint->getStatus() ?></li>
				<li><label>Description:</label><?= $appoint->getGroup()->getDescription() ?></li>
				<?php if (strtotime($appoint->getStartTime()) > time()) { ?>
					<li style="float:right">
						<a name="edit" data-name="edit" data-id="<?= $appoint->getId() ?>" data-pid="<?= $appoint->getGroup()->getPatient() ? $appoint->getGroup()->getPatient()->getId() :'' ?>" class="btn">Edit</a>
						<a name="cancel" data-name="edit" data-id="<?= $appoint->getId() ?>" data-pid="<?= $appoint->getGroup()->getPatient() ? $appoint->getGroup()->getPatient()->getId() :''?>" class="btn">Cancel
							Schedule</a>
					</li>
				<?php } ?>
				<li class="fadedText"><label>Scheduled by:</label>
				<?= $appoint->getEditor()->getFullname()?>
				</li>
			</ul>
		</div>
	</section>
<?php } else { ?>
	<section style="width: 700px;">
		<div>
			<table class="table table-hover">
				<thead>
				<tr>
					<th>S/N</th>
					<th>Type</th>
					<th>Date</th>
					<th>Patient</th>
					<th>Status</th>
					<th>Action</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($appoints as $key => $appoint) { ?>
					<tr class="ui-bar-d" data-name="title" id="<?= $key + 1 ?>">
						<td><?= $key + 1 ?>.</td>
						<td><?= $appoint->getGroup()->getClinic()->getName() ?></td>
						<td><?= (sizeof(explode(" ", $appoint->getStartTime())) > 1) && explode(" ", $appoint->getStartTime())[1] === null ? date("M d, Y", strtotime(explode(" ", $appoint->getStartTime())[0])) : date("M d, Y H:i", strtotime($appoint->getStartTime())) ?></td>
						<td><?= $appoint->getGroup()->getPatient() ? $appoint->getGroup()->getPatient()->getFullname() : "N/A" ?></td>
						<td><?= $appoint->getStatus() ?></td>
						<td>
							<?php if (strtotime($appoint->getStartTime()) > time()){ ?>
							<a name="edit" data-name="edit" data-id="<?= $appoint->getId() ?>" data-pid="<?= (($appoint->getGroup()->getPatient()) ? $appoint->getGroup()->getPatient()->getId() : '') ?>">Edit</a>
							/
							<a name="cancel" data-name="edit" data-id="<?= $appoint->getId() ?>" data-pid="<?= (($appoint->getGroup()->getPatient()) ? $appoint->getGroup()->getPatient()->getId() : '') ?>">Cancel</a>
						</td>
						<?php } ?>
					</tr>
					<tr data-name="content-<?= $key + 1 ?>">
						<td colspan="6">
							<div data-name="appDetails" style="margin: 10px 20px">
								<div style="float:left"><strong>Appointment Details:</strong></div>
								<br/>
								<ul class="appDetails">
									<li data-name="title">
										<label>Type:</label><span><?= $appoint->getGroup()->getClinic()->getName() ?></span></li>
									<li><label>Scheduled
												Patient:</label><span><i class="icon-globe"></i><a target="_blank" href="/patient_profile.php?id=<?= $appoint->getGroup()->getPatient()->getId() ?>" title="View patient Profile"><?= $appoint->getGroup()->getPatient()->getFullname() ?>
													(<?= $appoint->getGroup()->getPatient()->getId() ?>)</a></span></li>
									<li data-name="start"><label>Date:</label><span><?php
											$res = explode(" ", $appoint->getStartTime());
											echo sizeof($res) > 1 && $res[1] === null || sizeof($res) > 1 && $res[1] === "" || sizeof($res) > 1 && $res[1] == "00:00:00" || sizeof($res) > 1 && $res[1] === "00:00" ? date("M d, Y", strtotime($res[0])) : date("M d, Y H:i", strtotime($appoint->getStartTime()))
											?></span> <span style="display:none"><?= $appoint->getStartTime() ?></span></li>
									<li data-name="duration">
										<label>Duration:</label><span><?php echo date("M d, Y H:i", strtotime(($appoint->getEndTime() === null || $appoint->getEndTime() === "" ? "Not specified" : $appoint->getEndTime()))); ?></span><span style="display:none"><?= $appoint->getEndTime() ?></span>
									</li>
									<li><label>Participants:</label>
										<?php
										if (sizeof($appoint->getGroup()->getInvitees()) > 0) {
											$list = [];
											foreach ($appoint->getGroup()->getInvitees() as $invitee) {
												$list[] = '<a href="javascript:;">' . $invitee->getStaff()->getFullname() . '</a>';
											}
											echo implode(", ", $list);
										} else {
											echo 'N/A';
										}
										?></li>
									<li>
										<label>Resource: </label><?= $appoint->getGroup()->getResource() ? $appoint->getGroup()->getResource()->getName() : "N/A" ?></li>
									<li id="appointment"><label>Status:</label><?= $appoint->getStatus() ?></li>
									<li><label>Description: </label><?= $appoint->getGroup()->getDescription() ?></li>
									<li class="fadedText"><label>Scheduled by:</label>
										<?= $appoint->getEditor()->getFullname()?>
									</li>
								</ul>
							</div>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		</div>
	</section>
<?php } ?>

<!--<script src="/assets/select2_2/select2.min.js"></script>-->
<!--<link rel="stylesheet" type="text/css" href="/assets/select2/select2.css" >-->
<script type="text/javascript">
	$(document).ready(function () {
		$('table tr[data-name*="content"]').hide();
		$('table tr[data-name="content-1"]').show();//show
		$('table tr[data-name="title"]').click(function () {
			$('table tr[data-name*="content"]').hide();//ihde
//            $('table tr[data-name="title"]').hide('slow');
			$('table tr[data-name="content-' + $(this).prop('id') + '"]').slideDown();//show
		});
//        var start = "<?php echo $appoint->getStartTime() ?>";
//        var end = "<?php echo $appoint->getEndTime() ?>";
//        var attendedTime = "<?php echo $appoint->getAttendedTime() ?>";
//
//        start_ = moment(start).format((start.split(" ")[1] === "00:00:00") ? "dddd, MMMM Do YYYY" : "dddd, MMMM Do YYYY, h:mm a");
//        $("#start span").html(start_)
//        ttt = end_ = moment(end).format((end.split(" ")[1] === "00:00:00") ? "dddd, MMMM Do YYYY" : "dddd, MMMM Do YYYY, h:mm a");
//
//        dur = moment(end).from(moment(start), true);
//        $("#duration span").html(end.length < 2 ? "All Day" : dur);

//        diff=moment(end).diff(moment(start), 'days')
//        if(diff===0){
//            diff=moment(end).diff(moment(start), 'days');
//        }
		$('div[data-name="appDetails"]').each(function (i, obj) {
			start = $(this).find('ul li[data-name="start"] span:last').html();
			start_ = moment(start).format((typeof start.split(" ")[1] === "undefined") ? "dddd, MMMM Do YYYY" : "dddd, MMMM Do YYYY, h:mm a");
			$(this).find('ul li[data-name="start"] span:first').html(start_);

			end = $(this).find('ul li[data-name="duration"] span:last').html();
			end_ = moment(end).from(moment(start), true);
			$(this).find('ul li[data-name="duration"] span:first').html(typeof end === "undefined" || end.trim() === "" ? "All Day" : end_);

		});
		$('a[name="edit"]').click(function (e) {
			console.log($(this).data('id'))
			Boxy.load("/appointments/boxy_editAppointment.php?id=" + $(this).data('id'), {title: 'Edit Schedule'});
		});
		$('a[name="cancel"]').click(function (e) {
			aid = $(this).data('id');
			Boxy.confirm("Do you really want to cancel this Schedule.<br>Click 'Ok' to proceed otherwise click 'Cancel'", function () {
				$.ajax({
					url: '/functions/appointment_processor.php?cancelAppointment=true&aid=' + aid,
					type: 'GET',
					dataType: 'json',
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
						Boxy.alert("Sorry, something went wrong!!!");
					}
				}, {title: 'Edit Schedule'});
			});
		});
	});
	var ttt;
</script>
