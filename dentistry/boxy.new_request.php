<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$serviceCenters = (new ServiceCenterDAO())->all('dentistry');
if ($_POST) {
	@session_start();
	if (is_blank($_POST['patient_id'])) {
		exit("error:Select Patient");
	}
	if (count($_POST['request_ids']) < 1) {
		exit("error:At least one scan is required");
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDentistry.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	$request = new PatientDentistry();
	if(is_blank($_POST['service_centre_id'])){
		exit('error:Service center is Required');
	} else {
		$request->setServiceCenter( (new ServiceCenterDAO())->get($_POST['service_centre_id']) );
	}
	
	$request->setPatient((new PatientDemographDAO())->getPatient($_POST['patient_id'], false, null, null));
	
	$items_ = json_decode($_POST['items_meta']);
	$items = [];
	
	$request->setServices($items_);
	$request->setRequestDate(date("Y-m-d H:i:s"));
	if (!is_blank($_POST['referral_id'])) {
		$request->setReferral((new ReferralDAO())->get($_POST['referral_id']));
	}
	if (!is_blank($_POST['request_note'])) {
		$request->setRequestNote($_POST['request_note']);
	}
	if (!is_blank($_POST['request_note'])) {
		$request->setRequestNote($_POST['request_note']);
	}
	$request->setRequestedBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], false));
	
	$newDentistry = (new PatientDentistryDAO())->add($request);
	
	if ($newDentistry !== null) {
		exit("success:Dentistry added successfully");
	}
	
	exit("error:Failed to save request");
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
$referrals = (new ReferralDAO())->all();

?>
<div style="width: 500px">
	<span class="output"></span>
	<form method="post" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':stop});"
	      enctype="multipart/form-data" name="addDentistryForm" id="addDentistryForm" action="<?= $_SERVER['SCRIPT_NAME'] ?>">
		<label>Business Unit/Service Center <select required name="service_centre_id" data-placeholder="Service Center">
				<option></option>
				<?php foreach ($serviceCenters as $center){?>
					<option value="<?=$center->getId()?>"><?=$center->getName()?></option> <?php }?>
			</select> </label>
		<?php if (isset($_GET['pid'])) { ?>
			<input type="hidden" name="patient_id" value="<?= $_GET['pid'] ?>">
		<?php } else { ?>
			<label>Patient:
				<input id="patient_id" name="patient_id" class="select2" style="width: 100%" type="hidden" placeholder="Patient EMR #">
			</label>
		<?php } ?>
		<label>Referred by
			<select name="referral_id" data-placeholder="Select referring entity where applicable">
				<option></option>
				<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
					<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
					)</option><?php } ?>
			</select>
		</label>
		<label>Services to Request:</label>
		<label>
			<input type="hidden" id="request_ids" name="request_ids[]" multiple required>
		</label>
		<label>Request Note/Reason: <textarea name="request_note"></textarea></label>
		<button class="btn" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		<input name="items_meta" type="hidden">
	</form>
</div>
<script type="text/javascript">
	function start() {
		$(document).trigger('ajaxSend');
		$('.output').html('<img src="/img/ajax-loader.gif"> Please wait... ').removeClass('alert-error');
	}

	function stop(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(":");
		if (data[0].trim() === "success") {
			<?php if(isset($_GET['pid'])){
			if(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'patient_antenatal_profile.php'){ ?>showTabs(16);
			<?php }
			else if(basename(strstr($_SERVER['HTTP_REFERER'], "?", true)) == 'inpatient_profile.php'){ ?><?php }
			else { ?>showTabs(16);
			<?php }
			} else {
			?>$("#scanHomeMenuLinks a:first-child").click();<?php }?>
			Boxy.get($(".close")).hideAndUnload()
		} else if (data[0].trim() === "error") {
			Boxy.alert(data[1]);
			$('.output').html(data[1]).removeClass('alert-error').addClass('alert-error');
		}
	}

	$('#patient_id').select2({
		minimumInputLength: 3,
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
			return "EMR: " + data.patientId + "- " + data.fullname;
		},
		formatSelection: function (data) {
			return "EMR: " + data.patientId + "- " + data.fullname;
		},
		id: function (data) {
			return data.patientId;
		}
	});

	$('#request_ids').select2({
		placeholder: "Search and select service",
		minimumInputLength: 0,
		width: '100%',
		multiple: true,
		allowClear: true,
		ajax: {
			url: "/api/get_dentistry_services.php",
			dataType: 'json',
			data: function (term, page) {
				return {
					search: term
				};
			},
			results: function (data, page) {
				return {results: data};
			}
		},
		formatResult: function (data) {
			return data.name;
		},
		formatSelection: function (data) {
			return data.name;
		}
	}).change(function (evt) {
		var $this = $(this);
		var total = 0;
		if (evt.added !== undefined) {
			vex.dialog.prompt({
				message: 'Order Quantity ',
				placeholder: 'Enter quantity for the requested service',
				value: 1,
				overlayClosesOnClick: false,
				callback: function (value) {
					if (value !== false && value !== '' && !isNaN(value)) {
						evt.added.orderQuantity = value;
					}
					else {
						evt.added.orderQuantity = 1;
					}
					$this.trigger('change');
				},
				afterOpen: function () {
					$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
				}
			});
		}
		$.each($this.select2("data"), function (index, obj) {
			obj.orderQuantity = (typeof obj.orderQuantity !== "undefined") ? obj.orderQuantity : 1;
			total += parseFloat(obj.basePrice * obj.orderQuantity);
		});
		$("span.output").html("*Estimated cost: " + total.toFixed(2)).removeClass('alert-error');
		$('input[name="items_meta"]').val(JSON.stringify($('#request_ids').select2('data')));

	});
</script>