<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$encounter = null;
if ($_POST) {
	if (!isset($_SESSION)) @session_start();
	if (empty($_POST['patient_id'])) {
		exit("error:Select Patient");
	}
	if (count($_POST['request_id']) < 1) {
		exit("error:At least one scan is required");
	}
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientScan.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	
	
	$scan = new PatientScan();
	$scan->setPatient((new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, null, null));

	
	$scanId = (new ScanDAO())->getScan($_POST['request_id']);
	$scan->setScan($scanId);
	$scan->setRequestDate(date("Y-m-d H:i:s"));
	if (!is_blank($_POST['referral_id'])) {
		$scan->setReferral((new ReferralDAO())->get($_POST['referral_id']));
	}
	if (!is_blank($_POST['request_note'])) {
		$scan->setRequestNote($_POST['request_note']);
	} else {
		exit("error:Please enter a request note");
	}
	if (!is_blank($_POST['scan_service_centre_id'])) {
		$scan->setServiceCentre( (new ServiceCenterDAO())->get($_POST['scan_service_centre_id']) );
	} else {
		exit("error:Please select service centre");
	}
	if(!is_blank($_POST['encounter_id'])){
		$encounter = isset($_POST['encounter_id']) ? (new EncounterDAO())->get($_POST['encounter_id'], false, null) : null;
		
	}
	
	
	$scan->setRequestedBy((new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], FALSE));
	$scan->setEncounter($encounter);

	$newScan = (new PatientScanDAO())->addScan($scan, false);

	if ($newScan !== null) {
		exit("success:Scan added successfully");
	}

	exit("error:Failed to add scan request");
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$scanTypes = (new ScanDAO())->getScans();
$ServiceCentres = (new ServiceCenterDAO())->all('Imaging');

$referrals = (new ReferralDAO())->all(0, 5000);

?>
<div style="width: 500px">
<!--	<span class="output"></span> -->
	<form method="post" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':stop});" enctype="multipart/form-data" name="addScanForm" id="addScanForm" action="<?= $_SERVER['SCRIPT_NAME'] ?>">
		<label>Business Unit/Service Centre <select name="scan_service_centre_id" data-placeholder="-- Select --">
				<option></option>
				<?php foreach ($ServiceCentres as $center) { ?>
					<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
				<?php } ?>
			</select> </label>
		<?php if (isset($_GET['pid']) && !is_blank($_GET['pid'])) { ?>
			<input type="hidden" name="patient_id" value="<?= (isset($_GET['pid']) ? $_GET['pid'] : '')  ?>">
		<?php } else { ?>
			<label>Patient:
				<input id="patient_id" name="patient_id" style="width: 100%" type="hidden" placeholder="patient EMR #">
			</label>
		
		<?php } ?>
		<input type="hidden" name="encounter_id" value="<?= (isset($_GET['enc_id']) ? $_GET['enc_id'] : '')  ?>">

		<label>Referred by
			<select name="referral_id" data-placeholder="Select referring entity where applicable">
				<option></option>
				<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
					<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
					)</option><?php } ?>
			</select>
		</label>
		<label>
			Scan to Request:</label><label>
			<input type="hidden" id="request_id" name="request_id" placeholder="Select a scan" required="required">

			<!--<select id="request_id" name="request_id" data-placeholder="Select a scan" required="required">
				<option data-price="0"></option>
				<?php
/*				foreach ($scanTypes as $scan_type) {//$scan_type = new Scan();
					echo '<option value="' . $scan_type->getId() . '" data-price="' . $scan_type->getBasePrice() . '">' . $scan_type->getName() . ' (' . $scan_type->getCategory()->getName() . ')</option>';
				}
				*/?>
			</select>-->
		</label>
		<label>Request Note/Reason: <textarea name="request_note"></textarea></label>
		<button class="btn" type="submit">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</form>
</div>
<script type="text/javascript">
	function start() {
		$('.output').html('<img src="/img/ajax-loader.gif"> please wait... ').removeClass('alert-error');
	}
	function stop(s) {
		var data = s.split(":");
		if (data[0].trim() === "success") {
			<?php if(isset($_GET['pid'])){
					if(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='patient_antenatal_profile.php'){ ?>showTabs(10);
			<?php } else if(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='inpatient_profile.php'){?>showTabs(8);
			<?php } else { ?>showTabs(11);
			<?php }
			} else {?>$("#scanHomeMenuLinks a:first-child").click();<?php }?>
			Boxy.get($(".close")).hideAndUnload()
		} else if (data[0].trim() === "error") {
			Boxy.alert(data[1]);
			$('.output').html(data[1]).removeClass('alert-error').addClass('alert-error');
		}
	}
	$('#patient_id').select2({
		placeholder: "Search and select patient",
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
			return "EMR: " + data.patientId + " " + data.fullname;
		},
		formatSelection: function (data) {
			return "EMR: " + data.patientId + " " + data.fullname;
		},
		id: function (data) {
			return data.patientId;
		}
	});
	$("#request_id").select2({
		placeholder: $(this).attr("placeholder"),
		minimumInputLength: 3,
		width: '100%',
		//multiple: false,
		allowClear: true,
		ajax: {
			url: "/api/get_scans.php",
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
			return data.name + " (" + data.category.name + ")";
		},
		formatSelection: function (data) {
			return data.name + " (" + data.category.name + ")";
		}
	}).change(function (evt) {
		var pid = $('.boxy-content [name="patient_id"]').val();
		if (evt.added != undefined) {
			showInsuranceNotice(pid, evt);
			if ($('#request_id').select2("data")) {
				total = $('#request_id').select2("data").basePrice;
				$("span.output").html("Estimated Scan cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>").removeClass('alert-success').addClass('alert-success');
			}
		}
	});
</script>