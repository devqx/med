<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/2/16
 * Time: 1:12 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT']. '/protect.php';
if(!isset($_SESSION['staffID'])){
	exit( (new Protect())->ACCESS_DENIED );
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';
$encounters = (new EncounterDAO())->getUnClaimedEncounters($_GET['id']);
$channels = (new NotificationOptions())->getAllChannels();
$data = (new BillDAO())->getBillsForRequestCode($_GET['items']);
if (isset($_GET['encounter_id']) && !is_blank($_GET['encounter_id'])) {
	$diagnoses = [];
	foreach ((new EncounterDAO())->get($_GET['encounter_id'], FALSE)->getDiagnoses() as $diagnosis) {
		$diagnoses[] = $diagnosis->description;
	}
}
$billedTo = null;
//todo check if the bills are from different `billed_to`
if (!empty((array)$data)) {
	$patient = (new PatientDemographDAO())->getPatient($data[0]->patient_id, true);
	$billedTo = $patient->getScheme();
	// $billedTo = (new InsuranceSchemeDAO())->get($data[0]->billed_to, FALSE);
}

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/MessageDispatchDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageDispatch.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCodeNote.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCode.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';
	
	if (is_blank($_POST['patient_id'])) {
		exit('error:Patient info is required');
	}
	if (is_blank($_POST['message_type'])) {
		exit('error:Request Channel is required');
	}
	if (is_blank($_POST['messageText'])) {
		exit('error:Request Note is required');
	}
	
	$note = (new PAuthCodeNote())->setNote($_POST['messageText']);
	$scheme = null;
	$request = (new PAuthCode())
		->setPatient(new PatientDemograph($_POST['patient_id']))
		->setChannel((new NotificationOptions())->get($_POST['message_type']))
		->setScheme((new InsuranceSchemeDAO())->get($_POST['recipient_id']))
		->setChannelAddress($_POST['recipientShow'])
		->setNotes([$note])->add();
	if ($request != null) {
		exit('success:Request Saved successfully');
	}
	exit('error:Failed to save request');
}


$services = [];
foreach ($data as $item) {
	//todo what of items that have no code? `misc` items cause it to fail.
	// This is ok, because you can't get PA code for miscellaneous item
	
	if($item->item_code !== 'MS00001'){
		$services[] = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($item->item_code, FALSE)->getItemDescription();
	}
}
?>
<section style="width: 600px;">
	<?php if (!isset($_GET['encounter_id']) || is_blank($_GET['encounter_id'])) { ?>
		<label>
			Select Encounter
			<select name="encounter_id" id="encounter_id" data-placeholder="select patient encounter">
				<option value=""></option>
				<?php foreach ($encounters as $encounter) { ?>
					<option value="<?= $encounter->getId() ?>"><?= date("d/m/Y g:ia", strtotime($encounter->getStartDate())) ?>
						/<?= $encounter->getDepartment()->getName() ?>/<?= $encounter->getDepartment()->getName() ?>/
					</option>
				<?php } ?>
			</select>
		</label>
		<div class="clear"></div>
		<div class="btn-block">
			<button type="button" class="btn" onclick="continue_()">Continue</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	<?php } else if (empty((array)$data)) { ?>
		<div class="warning-bar">No `un-transfered` bill lines selected</div>
	<?php } else { ?>
		<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: started_transak, onComplete: complete_send_pa})">
			<label>Message Type <select onchange="readyComponents()" name="message_type" data-placeholder="Select Message Type">
					<option></option>
					<?php foreach ($channels as $channel){?>
						<option value="<?= $channel->getId()?>" data-text="<?= strtolower($channel->getName()) ?>"><?= $channel->getDescription() ?> (<?= $channel->getName()?>)</option>
					<?php }?>
				</select></label>
			<label>Recipient
				<input type="text" id="recipientShow" name="recipientShow" value="">

				<span class="pull-right">Scheme Contact [**sms/email]</span><select onchange="readyComponents()" id="recipient_id" name="recipient_id">
					<option value="<?= $billedTo->getId() ?>" data-sms="<?= $billedTo->getPhone() ?>" data-voice="callto:<?= $billedTo->getPhone() ?>" data-email="<?= $billedTo->getEmail() ?>"><?= $billedTo->getName() ?></option>
				</select></label>
			<input type="hidden" name="patient_id" value="<?= $patient->getId() ?>">
			<label>Message Content <span class="pull-right" id="countChars"></span>
				<textarea name="messageText" id="messageContainer" rows="15">Patient: [<?= $patient->getFullname() ?><?= "]\n" ?><?= $patient->getInsurance()->getScheme()->getInsurer()->getName() ?>[<?= $patient->getInsurance()->getEnrolleeId() . "]\n" ?>Diagnoses:
--------------------------<?= "\n" ?>
<?= implode(", ", $diagnoses) . "\n" ?>
-------------------------<?= "\n" ?>
We request for authorization Code for the patient named above for the following services:
--------------------------<?= "\n" ?>
<?php foreach ($services as $service) {
	echo $service . "\n";
} ?>
--------------------------<?= "\n" ?>
Thanks.
    </textarea>
			</label>
			<div class="btn-block">
				<button class="btn" type="submit" id="sendBtn" disabled>SEND</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">CANCEL</button>
			</div>
		</form>
	<?php } ?>
</section>

<script type="text/javascript">
	function continue_() {
		if($("#encounter_id").val() !== ""){
			Boxy.get($(".close")).hideAndUnload(function () {
				Boxy.load("<?=$_SERVER['REQUEST_URI'] ?>&encounter_id=" + $("#encounter_id").val(), {title: 'Request Authorization Code for services Transfer'})
				});
			}else {
			Boxy.alert("Please select patient encounter");
		}
		

	}
	function readyComponents() {
		var type = $('select[name="message_type"] option:selected').data('text');
		var recipient = $('#recipient_id').find('option').data();
		
		if (type !== "" && recipient[type] !== undefined && recipient[type] !== "") {
			$('#sendBtn').removeAttr('disabled');
			//$('#recipientShow').html('(' + recipient[type] + ')');
			$('input[name="recipientShow"]').show().val(recipient[type]);
			//$('input[name="recipientShow"]');
		} else {
			//select type of message to send
			Boxy.alert("No valid recipient specified");
			$('#sendBtn').prop('disabled', true);
			//$('#recipientShow').empty();
			$('input[name="recipientShow"]').val("").hide();
		}
	}

	$(document).ready(function () {
		$('input[name="recipientShow"]').hide();
		setTimeout(function () {
			$('#messageContainer').keyup();
		}, 5);
		$('#messageContainer').keyup(function () {
			$("#countChars").html(parseInt($(this).val().length) + " characters");
		});
	});

	function started_transak() {
		$(document).trigger('ajaxSend');
	}

	function complete_send_pa(s) {
		$(document).trigger('ajaxStop');
		console.log(s);
		var data = s.split(':');
		if(data[0]==='error'){
			Boxy.warn(data[1]);
		} else if(data[0]==='success'){
			Boxy.info(data[1], function () {
				Boxy.get($('.close')).hideAndUnload();
			});
		}
	}
</script>
