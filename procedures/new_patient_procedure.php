<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/3/14
 * Time: 4:24 PM
 */
$enforceProcedureOtherCosts = true;
$flexibleBillOption = false; // true for cedarcrest

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ResourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/BodyPartDAO.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/CurrencyDAO.php";

$currency = (new CurrencyDAO())->getDefault();
$body_parts = (new BodyPartDAO())->all();

if (!isset($_SESSION)) {
	session_start();
}
$pros = (new ProcedureDAO())->getProcedures();
$resources = (new ResourceDAO())->getResources();
$referrals = (new ReferralDAO())->all(0, 5000);

$allCentres = (new ServiceCenterDAO())->all('Procedure');
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Resource.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Procedure.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/BodyPart.php';
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	
	$p_procedure = new PatientProcedure();
	if (is_blank($_POST['patient_id'])) {
		exit("error:Select Patient");
	}
	if (is_blank($_POST['procedure_id'])) {
		exit("error:Procedure is required");
	}
	if (is_blank($_POST['service_centre_id'])) {
		exit("error:Procedure Service Centre is required");
	}
	
	$p_procedure->setServiceCentre(new ServiceCenter($_POST['service_centre_id']));
	$p_procedure->setProcedure((new ProcedureDAO())->getProcedure($_POST['procedure_id']));
	$p_procedure->setBodyPart(!is_blank(@$_POST['body_part']) ? new BodyPart(@$_POST['body_part']) : null);
	if (isset($_POST['aid']) && !isset($_POST['source'])) {
		$p_procedure->setInPatient(new InPatient($_POST['aid']));
	}
	$p_procedure->setRequestDate(date("Y-m-d H:i:s", time()));
	$p_procedure->setPatient((new PatientDemographDAO())->getPatient($_POST['patient_id'], false, null, null));
	if (!is_blank(@$_POST['referral_id'])) {
		$p_procedure->setReferral((new ReferralDAO())->get($_POST['referral_id']));
	}
	
	$conditions = array_filter(explode(",", $_POST['condition_ids']));
	
	$p_procedure->setConditions($conditions);
	
	if (!is_blank($_POST['request_note'])) {
		$p_procedure->setRequestNote($_POST['request_note']);
	}
	
	if (isset($_POST['anesthesiologist']) && $_POST['anesthesiologist'] == "true") {
		$p_procedure->setHasAnesthesiologist(true);
	} else {
		$p_procedure->setHasAnesthesiologist(false);
	}
	if (isset($_POST['surgeon']) && $_POST['surgeon'] == "true") {
		$p_procedure->setHasSurgeon(true);
	} else {
		$p_procedure->setHasSurgeon(false);
	}
	$p_procedure->setRequestedBy(new StaffDirectory($_SESSION['staffID']));
	
	if (isset($_POST['source'], $_POST['aid'])) {
		$p_procedure->setSource($_POST['source'])->setSourceInstanceId($_POST['aid']);
	}
	
	$p_procedure->setBilled($_POST['bill_option'] == 'now' ? true : false);
	
	$new = (new PatientProcedureDAO())->add($p_procedure, $_POST['bill_option'] == 'now' ? false : true, null);
	if ($new !== null) {
		exit("success:Patient Procedure has been saved");
	}
	exit("error:Sorry, an error occurred");
}

?>
<section<?php if (isset($_GET['id'])) { ?> style="width: 550px;"<?php } ?>>
    <label class="output well well-small" style="color: #0a0a0a;"></label>
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:__start, onComplete:__done})">
		<?php if (!isset($_GET['id'])) { ?><label>Patient
			<input name="patient_id" id="patient_id" type="text" value="">
		</label><?php } else { ?>
			<input name="patient_id" type="hidden" value="<?= $_GET['id'] ?>">
		<?php } ?>
		<div class="row-fluid">
			<label class="span6"> Business Unit/Service Center
				<select name="service_centre_id" data-placeholder="Select a Receiving Procedure Center">
					<option></option>
					<?php foreach ($allCentres as $center) { ?>
						<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
					<?php } ?>
				</select>
			</label>
			<label class="span6">Referred by
				<select name="referral_id" data-placeholder="Select Referral where applicable">
					<option></option>
					<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
						<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
						)</option><?php } ?>
				</select>
			</label>
		</div>


		<div class="row-fluid">
			<label class="span12">
				Procedure
				<input type="hidden" id="procedure_id" name="procedure_id" placeholder="Select Procedure Service">
			</label>
			<!--		<label class="span6">-->
			<!--			Body part-->
			<!--			<select name="body_part" class="span2" data-placeholder="Select the related body part">-->
			<!--				<option value=""></option>-->
			<!--			</select>-->
			<!--		</label>-->
		</div>

		<label>Primary diagnoses</label><label>
			<input name="condition_ids" type="text" placeholder="Diagnoses">
		</label>

		<label>Request Note
			<textarea name="request_note"></textarea> </label>
		<!--
				<label class="hide">Expected Procedure Dates</label>
				<div class="row-fluid hide">
					<label class="span6">Start<input type="text" class="date" name="date_start" id="date_start"> </label>
					<label class="span6">End<input type="text" class="date" name="date_end" id="date_end" disabled="disabled">
					</label>
				</div>
		-->
		<div class="row-fluid">
			<label class="span3"><input type="checkbox" name="anesthesiologist" value="true" <?php if ($enforceProcedureOtherCosts){ ?>checked="checked" readonly="readonly" disabled<?php } ?>>
				Requires Anesthesiologist </label>
			<label class="span3"><input type="checkbox" name="surgeon" value="true" <?php if ($enforceProcedureOtherCosts){ ?>checked="checked" readonly="readonly" disabled<?php } ?>>
				Requires Surgeon</label>
			<label class="span3" title="The patient will be billed NOW with the configured prices"><input type="radio" name="bill_option" value="now" <?= $flexibleBillOption == false ? 'checked':'' ?>> Bill Now </label>
			<label class="span3" title="You will have to manually charge for this procedure"><input type="radio" name="bill_option" value="later" <?= $flexibleBillOption == false ? 'disabled':'' ?>> Bill Later </label>
		</div>

		<!-- </fieldset>-->
		<div class="btn-block">
			<button class="btn" type="submit">Save</button>
			<button class="btn-link" type="button" onclick="Boxy.get($('.close')).hideAndUnload();">Cancel</button>
		</div>
		<?php if (isset($_GET['aid']) && !isset($_GET['source'])) { ?><input type="hidden" name="aid" value="<?= $_GET['aid'] ?>"> <?php } ?>
		<?php if (isset($_GET['aid'], $_GET['source'])) { ?>
			<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
			<input type="hidden" name="source" value="<?= $_GET['source'] ?>">
		<?php } ?>
	</form>
</section>
<script type="text/javascript">
	function __start() {
		$(document).trigger('ajaxSend');
		$('input:checkbox[disabled][readonly]').prop('disabled', false);
	}
	function __done(_s) {
		$(document).trigger('ajaxStop');
		var s = _s.split(":");
		if (s[0] === "error") {
			Boxy.alert(s[1]);
			$('input:checkbox[readonly]').prop('disabled', true);
		} else if (s[0] === "success") {
			Boxy.info(s[1], function () {
				<?php if(isset($_GET['id'])){?>
				<?php if(!isset($_GET['source'])){?>
				showTabs(8);
				<?php } else {?>
				reloadCurrentTab();
				<?php }?>
				if($(".close")){
					Boxy.get($(".close")).hideAndUnload();
				}<?php }else {?>
				location.reload();
				<?php }?>
			})
		}
	}
	$(document).ready(function () {
		$('.row-fluid > label > input:radio').iCheck({radioClass: 'iradio_square-blue'}).on('ifChanged', function (event) {
			$(event.currentTarget).trigger('change');
		});
		$('select[name="service_centre_id"]').select2({width: '100%'});
		$('select[name="body_part"]').select2({width: '100%', allowClear: true});
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
				return ((data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				return ((data.fname + " " + data.mname + " " + data.lname));
			},
			id: function (data) {
				return data.patientId;
			}
		});
		$('input[name="condition_ids"]').select2({
			placeholder: "Please enter the diagnosis name or ICD 10 code",
			allowClear: true,
			minimumInputLength: 3,
			multiple: true,
			width: '100%',
			formatResult: function (data) {
				return data.name + " (" + data.code + ")";
			}, formatSelection: function (data) {
				return data.name + " (" + data.code + ")";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the diagnosis name or ICD 10 code";
			},
			ajax: {
				url: '/api/get_diagnoses.php',
				dataType: 'json',
				data: function (term, page) {
					return {
						q: term // search term
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			}
		});

		$('#body_part_id').select2({
			allowClear: true,
			width: '100%',
		});

		$('#procedure_id').select2({
			placeholder: $(this).attr("placeholder"),
			minimumInputLength: 0,
			width: '100%',
			multiple: false,
			allowClear: true,
			ajax: {
				url: "/api/get_procedures.php",
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
			var pid = $('[name="patient_id"]').val();
			if (evt.added !== undefined) {
				showInsuranceNotice(pid, evt);
				if ($('#procedure_id').select2("data")) {
					var proc = $('#procedure_id').select2("data");
					var total = parseFloat(proc.basePrice) + parseFloat(proc.priceAnaesthesia) + parseFloat(proc.priceSurgeon) + parseFloat(proc.priceTheatre);
					$("label.output").html("Estimated Procedure cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>").removeClass('alert-success').addClass('alert-success');
				}
			}
		});
		$('[name="referral_id"]').select2({width: '100%', allowClear: true});
		var from = $('#date_start');
		var to = $('#date_end');
		from.datetimepicker({
			onShow: function (ct) {
				this.setOptions({minTime: new Date()});
			}, onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() !== "") {
					to.val('').removeAttr('disabled');
				} else {
					to.val('').attr({'disabled': 'disabled'});
				}
			}
		});
		to.datetimepicker({
			onShow: function (ct) {
				this.setOptions({minTime: from.val() ? from.val() : false});//
				//todo: file a bug: if the plugin is configured with time option, the minTime doesn't work
			}
		});
		$('#resource_id').select2({width: '100%'});
		$("#specializations").select2({
			placeholder: "Staff Name (Specialization [Staff ID])",
			allowClear: true,
			minimumInputLength: 3,
			multiple: true,
			width: '100%',
			formatResult: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "]) " + data.phone;
			},
			formatSelection: function (data) {
				return data.fullname + " (" + (data.specialization === null ? "" : data.specialization.name) + " [" + data.id + "])";
			},
			formatNoMatches: function (term) {
				return "Sorry no record found for '" + term + "'";
			},
			formatInputTooShort: function (term, minLength) {
				return "Please enter the staff name or ID or phone or specialization";
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
					//                    console.log(data)
					return {results: data};
				}
			}
		});//End Staff Select2

	});
</script>
