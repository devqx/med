<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/18/16
 * Time: 3:42 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCode.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PAuthCodeNote.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$channels = (new NotificationOptions())->getAllChannels();
$patientId = null;
if ($_POST) {
	//if (is_blank($_POST['patient_id']) && is_blank($_POST['id'])) {
	//}
	if (is_blank($_POST['channel_id'])) {
		exit('error:Request Channel is required');
	}
	if (is_blank($_POST['note'])) {
		exit('error:Request Note is required');
	}
	if(!is_blank($_POST['patient_id'])){
		$patientId = $_POST['patient_id'];
	}
	
	if(!is_blank($_POST['pid'])){
		$patientId = $_POST['pid'];
	}
	
	if($patientId == null){
		exit('error:Patient info is required');
		
	}
	
	$note = (new PAuthCodeNote())->setNote($_POST['note']);
	$patient = (new PatientDemographDAO())->getPatient($patientId, false);
	$request = (new PAuthCode())->setScheme( $patient->getScheme() )->setPatient($patient)->setChannel((new NotificationOptions())->get($_POST['channel_id']))->setNotes([$note])->add();
	if ($request != null) {
		exit('success:Request Saved successfully');
	}
	exit('error:Failed to save request');
}
?>
<section style="width: 500px">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: __osdnfuwenmds, onComplete: __saoedfosdf})">
		<label>
			<?php if(!$_GET['id']) { ?>
			Patient <input type="hidden" name="patient_id" id="patient_id" value="<?= @$_GET['id'] ?>"/></label>

		<?php } ?>
		<input type="hidden" name="pid" value="<?= $_GET['id'] ?>">
		<label>Request Channel <select name="channel_id" data-placeholder="-- select request channel --" required>
				<option></option>
				<?php foreach ($channels as $channel) { ?>
					<option value="<?= $channel->getId() ?>"><?= $channel->getDescription() ?> (<?= $channel->getName() ?>)</option>
				<?php } ?>
			</select> </label>
		<label>Request Note
			<textarea name="note"></textarea>
		</label>

		<p class="clear clearBoth"></p>
		<div class="btn-block">
			<button class="btn" type="submit">Submit</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('#patient_id').select2({
			placeholder: "Search and select patient",
			minimumInputLength: 3,
			width: '100%',
			allowClear: true,
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
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:" + data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			}
		});
	});

	var __osdnfuwenmds = function () {
		$(document).trigger('ajaxSend');
	};

	var __saoedfosdf = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if (data[0] == 'error') {
			Boxy.warn(data[1]);
		} else if (data[0] == 'success') {
			Boxy.get($('.close')).hideAndUnload();
			if($("#pa_codes")[0] === undefined){
				showDoc("pa_codes");
			}else {
				$("#pa_codes").click();

			}
		}
	}

</script>