<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/9/15
 * Time: 11:58 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientSpecialEventDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientSpecialEvent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$dao = new PatientSpecialEventDAO();

if ($_POST) {
	if (isset($_POST['event_description'])) {
		$evt = new PatientSpecialEvent();
		$evt->setPatient(new PatientDemograph($_POST['pid']));
		$evt->setNotedBy(new StaffDirectory($_SESSION['staffID']));
		if (!is_blank($_POST['event_description'])) {
			$evt->setNote($_POST['event_description']);
		} else {
			exit("error:Did you forget the event description?");
		}
		if (!is_blank($_POST['alert_date'])) {
			$evt->setAlertDate($_POST['alert_date']);
		} else {
			$evt->setAlertDate(NULL);
			//exit("error: Please select the Alert date");
		}
		
		if ($dao->add($evt) !== null) {
			exit("success:Saved Special Note");
		}
		
		exit("error:Failed to save Special Note");
	}
	
	if ($_POST['action'] == "read") {
		exit(json_encode($dao->dismiss($_POST['id'])));
	}
	if ($_POST['action'] == "unread") {
		exit(json_encode($dao->undismiss($_POST['id'])));
	}
} else if (isset($_REQUEST['action']) && $_REQUEST['action'] == "new") { ?>
	<div style="width:550px">
		<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>"
		      onsubmit="return AIM.submit(this, {onStart: dd1, onComplete: dd2})">
			<label>
				Event Note
				<textarea name="event_description"></textarea>
			</label>
			<label>
				Alert Date
				<input name="alert_date" type="text" id="alert_date" readonly="readonly">
			</label>
			<div class="btn-block">
				<button class="btn" type="submit">Save</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
			</div>
		</form>
	</div>
	<script type="text/javascript">
		var dd1 = function () {
			//$.blockUI();
		};
		var dd2 = function (data) {
			data = data.split(":");
			if (data[0] === "error") {
				Boxy.alert(data[1])
			} else {
				Boxy.get(".close").hideAndUnload();
			}
		};

		$('#alert_date').datetimepicker({
			format: 'Y-m-d H:i',
			timepicker: true,
			onShow: function (ct) {
				this.setOptions({minDate: new Date().toISOString().split('T')[0]});
			}
		});


	</script>
<?php } else {
	$status = (isset($_GET['hidden']) ? true : false);
	$events = $dao->getForPatient($_GET['pid'], $status);
	
	$patient = (new PatientDemographDAO())->getPatient($_GET['pid'], false, null, null);
	?>
	<div style="width:550px">
		<div class="pull-right"><a id="newEventLnk" href="">Write New</a></div>
		<h6><span><?= count($events) ?></span> Special Events for <?= $patient->getFullname() ?></h6>
		<hr class="border">
		<?php if (count($events) > 0) { ?>
			
			<?php foreach ($events as $evt) {//$evt=new PatientSpecialEvent();?>
				<div style="margin-bottom:10px;box-shadow: 0 1px 4px rgba(0,0,0,0.2); background-color: white;">
				<div class="alert-box<?= $evt->getDismissed() ? ' error strike' : ' warning' ?>">
					
					<?= $evt->getNote() ?>
					<span class="fadedText">[Noted on <?= date("d/m/Y g:ia", strtotime($evt->getDate())) ?>
						by <?= $evt->getNotedBy() ? $evt->getNotedBy()->getFullname() : '[UNKNOWN]' ?>]</span>
					<?php if (!$evt->getDismissed()) { ?>
						<a href="javascript:;" class="dismiss pull-right" data-alert-id="<?= $evt->getId() ?>">Clear</a>
					<?php } else { ?>
						<a href="javascript:;" class="undismiss pull-right" data-alert-id="<?= $evt->getId() ?>">Un-Clear</a>
					<?php } ?>
				</div>
				</div>
			<?php } ?>
		<?php } ?>
		<a href="javascript:void(0)" onclick="Boxy.get(this).hideAndUnload(function(e) {
			Boxy.load('/specialEvents.php?pid=<?= $_GET['pid'] ?>&hidden=true')
			})" class="pull-right">View All Events</a>
	</div>
	<script type="text/javascript">
		$(document).ready(function () {
			$(".dismiss").live('click', function (e) {
				$this = $(this);
				if (!e.handled) {
					$.post('/specialEvents.php', {action: 'read', id: $this.data("alert-id")}).success(function (data) {
						if (data == "true") {
							$this.parent().remove();
							$('h6 span').html($('.alert-box.warning').length);
							if ($('.alert-box.warning').length == 0) {
								$('.abnormal').parents('.pull-right').remove();
							}
						} else {
							Boxy.alert("An error occurred while dismissing message");
						}
					}, 'json').error(function (data) {
						Boxy.alert("Error dismissing message");
					});
					e.handled = true;
				}
			});

			$(".undismiss").live('click', function (e) {
				$this = $(this);
				if (!e.handled) {
					$.post('/specialEvents.php', {action: 'unread', id: $this.data("alert-id")}).success(function (data) {
						if (data == "true") {
							$this.parents('.alert-box').removeClass('error').removeClass('strike').addClass('warning');
							$this.addClass('dismiss').removeClass('undismiss').html('Clear');
							/*$('h6 span').html($('.alert-box.error').length);
							 if ($('.alert-box.error').length == 0) {
							 $('.abnormal').parents('.pull-right').remove();
							 }*/
						} else {
							Boxy.alert("An error occurred while recovering message");
						}
					}, 'json').error(function (data) {
						Boxy.alert("Error recovering message");
					});
					e.handled = true;
				}
			});
			$("#newEventLnk").click(function (evt) {
				if (!evt.handled) {
					Boxy.get(".close").hideAndUnload();
					Boxy.load("/specialEvents.php?pid=<?=$_GET['pid']?>&action=new");
					evt.handled = true;
				}
				evt.stopPropagation();
				return false;
			})
		})
	</script>
<?php } ?>