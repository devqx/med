<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/5/14
 * Time: 10:15 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
function __format($output)
{
	$ret = array('status' => $output);
	return json_encode($ret);
}

if ($_POST) {
	$type = $_POST['msgType'];
	if (empty($type)) {
		exit(__format("error|Message type not selected"));
	}
	//convert the input to array of patient IDs if not empty
	$__recipients = array_filter(explode(",", $_POST['recipients'])); //!empty($_POST['recipients']))?explode(",", $_POST['recipients']):[];
	
	if (sizeof($__recipients) < 1) {
		exit(__format("error|No recipient specified"));
	}
	
	if (empty($_POST['messageText'])) {
		exit(__format("error|Message content is blank"));
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
	$DATA = array();
	$SEND_REPORTS = array();
	for ($i = 0; $i < sizeof($__recipients); $i++) {
		$mDispatchObj = new MessageDispatch();
		$mDispatchObj->setMessage($_POST['messageText']);
		
		$patient = (new PatientDemographDAO())->getPatient($__recipients[$i], false, null, null);
		$mDispatchObj->setPatient($patient);
		$mDispatchObj->setSmsChannelAddress($patient->getPhoneNumber());
		$mDispatchObj->setEmailChannelAddress($patient->getEmail());
		$mDispatchObj->setSmsDeliveryStatus(0);
		$mDispatchObj->setEmailDeliveryStatus(0);
		$mDispatchObj->setVoiceDeliveryStatus(0);
		$mq = (new MessageDispatchDAO())->addItem($mDispatchObj);
		//after adding the items, try to send them
		$to_send = (new MessageDispatchDAO())->getItem($mq->getId());
		$send = (new MessageDispatchDAO())->sendItem($to_send, 1, null);
		$SEND_REPORTS[] = $send;
	}
	exit(json_encode($SEND_REPORTS));
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';
$CHANNELS = (new NotificationOptions())->getAllChannels();
?>
<div style="width: 660px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:start, onComplete:done})">
		<label>Message Type</label>
		<label>
			<select id="msgType" name="msgType">
				<option value=""> - select -</option>
				<?php foreach ($CHANNELS as $gw) {
					echo '<option value="' . $gw->getId() . '"' . (!$gw->getEnabled() ? ' disabled="disabled"' : '') . '>' . $gw->getName() . '</option>';
				} ?>
			</select>
		</label>
		<label class="row-fluid">
			<span class="span10">Recipients</span>
			<span class="span1"><a href="javascript:;" id="resetDist">Reset</a></span>
			<span class="span1"><a href="javascript:;" id="browseDist">Browse</a></span>
		</label>
		<label><input type="hidden" name="recipients" id="recipients"></label>
		<label>
			<textarea maxlength="160" id="messageText" name="messageText" rows="5" placeholder="Message Text . . ."></textarea>
		</label>
		<label></label>
		<div class="btn-block">
			<button class="btn" type="submit">Send</button>
			<button class="btn-link" type="button">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
	}
	function done(a) {
		var s = $.parseJSON(a);

		if (s.status && s.status.indexOf('error') != -1) {
			var data = s.status.split("|");
			Boxy.alert(data[1])
		} else {
			Boxy.get($('.close')).hideAndUnload();
			reportDeliveryStatus(s, 'new');
		}
	}
	$(document).ready(function () {
		$("#resetDist").live('click', function (e) {
			if (e.handled != true) {
				$("#recipients").select2('data', null);
				e.handled = true;
			}
		});
		$("#browseDist").live('click', function (e) {
			if (e.handled != true) {
				Boxy.load('/pages/messaging/boxy.distlist.dialog.php', {title: 'Distribution List'});
				e.handled = true;
			}
		});
		$("#recipients").select2({
			multiple: true,
			allowClear: true,
			minimumInputLength: 4,
			ajax: {
				url: "/api/search_patients.php",
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			formatResult: function (data) {
				return ((data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				return data.fname + " " + data.mname + " " + data.lname;
			},
			id: function (data) {
				return data.patientId;
			},
			width: '100%',
			placeholder: 'Search for patients'
		});
		setTimeout(function () {
			$('#messageText').keyup();
			//$('#messageText').trigger("keyup");
		}, 5);
		$('#messageText').keyup(function () {
			$("#messageText").parent().next('label').html(parseInt($(this).attr('maxlength') - $(this).val().length) + " characters remaining");
		});

		$('button.btn-link').click(function () {
			Boxy.get($('.close')).hideAndUnload();
		})
	});
</script>