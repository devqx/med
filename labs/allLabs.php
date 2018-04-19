<?php
//include 'allLabs_style2.php';
//exit;
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/EncounterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabComboDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';

$currency = (new CurrencyDAO())->getDefault();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$specimens = (new LabSpecimenDAO())->getSpecimens();
$referrals = (new ReferralDAO())->all(0, 5000);
$allLabCentres = (new ServiceCenterDAO())->all('Lab');

$DATA = (new LabDAO())->getLabs(false);
$labCategories = (new LabCategoryDAO())->getLabCategories();
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super))
	exit ($protect->ACCESS_DENIED);

if ($_POST) {
	
	if (isset($_POST['lab-reqs']) && !empty($_POST['lab-reqs'])) {
		$request = new LabGroup();
		$encounter = isset($_POST['encounter_id']) ? (new EncounterDAO())->get($_POST['encounter_id'], false, null) : null;
		$request->setPatient((new PatientDemographDAO())->getPatient($_POST['pid'], FALSE));
		$request->setInPatient(isset($_POST['inpatient_id']) && !is_blank($_POST['inpatient_id']) ? new InPatient($_POST['inpatient_id']) : null);
		$request->setRequestedBy($this_user);
		$request->setUrgent( isset($_POST['urgent']) ? TRUE : FALSE );
		$request->setRequestNote($_POST['request_note']);

		$pref_specimens = array();
		$sel_specimens = isset($_POST['specimen_ids']) ? $_POST['specimen_ids'] : [];
		foreach ($sel_specimens as $s) {
			if (!empty($s))
				$pref_specimens[] = (new LabSpecimenDAO())->getSpecimen($s);
		}
		$request->setPreferredSpecimens($pref_specimens);

		//fixme if(!is_blank($_POST['lab-reqs']) && )
		$lab_data = array();
		$tests = array_filter(explode(",", $_POST['lab-reqs']));

		foreach ($tests as $l) {
			$lab_data[] = (new LabDAO())->getLab($l);
		}
		$request->setRequestData($lab_data);
		$request->setServiceCentre((new ServiceCenterDAO())->get($_POST['service_centre_id']));
		$request->setReferral((new ReferralDAO())->get($_POST['referral_id']));
		$request->setEncounter($encounter);
		$data = (new PatientLabDAO())->newPatientLabRequest($request, false);
		if ($data !== null) {
			exit(json_encode($data));
		}
		exit("error:Failed to create the lab request(s)");
	} else {
		echo 'error:No lab test selected!';
	}
	exit;
}
?>
<div style="width:600px">
	<form method="post" action="/labs/allLabs.php"
	      onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
		<label class="output well well-small"></label>
		<label>Business Unit/Service Center <select name="service_centre_id" data-placeholder="Select a receiving lab center">
				<option></option>
				<?php foreach ($allLabCentres as $center) { ?>
					<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
				<?php } ?>
			</select> </label>

		<?php if (!isset($_GET['id'])) { ?> <label>Patient </label><?php } ?>
		<label><input type="hidden" name="pid" value="<?= (isset($_GET['id']) ? $_GET['id'] : '') ?>"></label>
		
		<input type="hidden" name="inpatient_id" value="<?= (isset($_GET['aid'])) ? $_GET['aid'] : '' ?>">
		<input type="hidden" name="encounter_id" value="<?= (isset($_GET['enc_id'])) ? $_GET['enc_id'] : '' ?>">
		
		<label>Referred by
			<select name="referral_id" data-placeholder="Select referring entity where applicable">
				<option></option>
				<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
					<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
					)</option><?php } ?>
			</select>
		</label>
		<label><span class="fadedText required small">Use of the lab combo dropdown is mutually exclusive with the use of the lab categories drop down</span></label>
		<label>Lab Combos <input type="hidden" id="lab-combos"></label>
		<label>Lab Categories <input type="hidden" id="lab_category_ids" placeholder="Browse by Categories"></label>
		<label><input type="checkbox" name="urgent"> Please tick if Urgent</label>
		<label>Lab tests to request:</label>
		<label><input type="hidden" id="labs_to_request" name="lab-reqs"></label>
		<input type="hidden" name="lab-reqs2" value="">

		<label>Preferred Specimen(s) </label>
		<label><select multiple="multiple" name="specimen_ids[]">
				<?php foreach ($specimens as $s) {
					echo '<option value="' . $s->getId() . '">' . $s->getName() . '</option>';
				} ?>
			</select></label>

		<label>Request Note
			<textarea name="request_note" rows="2"></textarea>
		</label>

		<div class="btn-block">
			<button class="btn" type="submit" name="btn">Request &raquo;</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_lab_combos.php';?>
		var labCs = <?= (json_encode($labCombos, JSON_PARTIAL_OUTPUT_ON_ERROR))?>;
		var labs = <?= json_encode($DATA, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		var labCategories = <?=json_encode($labCategories, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		var labsFiltered = [];

		$('.boxy-content #lab-combos').select2({
			placeholder: "Search and select lab combos",
			width: '100%',
			allowClear: true,
			data: {results: labCs, text: 'name'},

			formatResult: function (data) {
				return data.name;
			},
			formatSelection: function (data) {
				return data.name;
			}
		}).change(function (e) {
			if (e.added !== undefined) {
				select = $('.boxy-content #labs_to_request');
				var dataOld = select.select2('data');
				for (var i = 0; i < e.added.combos.length; i++) {
					dataOld.push(e.added.combos[i].lab);
				}
				select.select2("data", dataOld, true);
			}
		});

		$('.boxy-content #lab_category_ids').select2({
			placeholder: "Search and select lab combos",
			width: '100%',
			allowClear: true,
			multiple: true,
			data: {results: labCategories, text: 'name'},

			formatResult: function (data) {
				return data.name;
			},
			formatSelection: function (data) {
				return data.name;
			}
		}).change(function(e){
			//$('.boxy-content #labs_to_request').select2('data', labsFiltered, true);
		});

		$('.boxy-content #labs_to_request').select2({
			placeholder: "Search and select lab",
			minimumInputLength: 0,
			width: '100%',
			multiple: true,
			allowClear: true,
			data: {results: labs, text: 'name'},
			formatResult: function (data) {
				return data.name + " (" + data.category.name + ")";
			},
			formatSelection: function (data) {
				return data.name + " (" + data.category.name + ")";
			}
		}).change(function (evt) {
			var total = 0;
			var pid = $('.boxy-content [name="pid"]').val();
			var request = [];
			if (evt.added !== undefined) {
				showInsuranceNotice(pid, evt);
				getQuantity(evt.added, function(){
					_.each($(evt.target).select2("data"), function (i) {
						request.push({id: i.id, quantity: i.quantity});
					});
					$('[name="lab-reqs2"]').val(JSON.stringify(request));
				});
			} else {
				_.each($(evt.target).select2("data"), function (i) {
					request.push({id: i.id, quantity: i.quantity});
				});
				$('[name="lab-reqs2"]').val(JSON.stringify(request));
			}
			$.each($(this).select2("data"), function () {
				total = parseFloat(this.basePrice) + total;
			});
			$("form label.output").html("selected lab test cost: <?= $currency->getSymbolLeft() ?>" + parseFloat(total).toFixed(2)+"<?= $currency->getSymbolRight() ?>");
		});
		
		<?php if(!isset($_GET['id'])){?>
		$('.boxy-content [name="pid"]').select2({
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
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
				details.push(data.fname ? data.fname : null);
				details.push(data.mname ? data.mname : null);
				details.push(data.lname ? data.lname : null);
				return implode(" ", details);
				//return (("EMR ID:" + data.patientId + " " + data.fname + " " + data.mname + " " + data.lname));
			},
			formatSelection: function (data) {
				var details = [];
				details.push(data.patientId ? "EMR ID:"+data.patientId : null);
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
		<?php } ?>
		function start() {
			$(document).trigger('ajaxSend');
		}
		function done(s) {
			$.unblockUI();
			var returnData = s.split(":");
			if (returnData[0] === "error") {
				Boxy.alert(returnData[1]);
			} else {
				try {
					showTabs(6);
				} catch (exception) {
				}
				Boxy.get($('.close')).hideAndUnload();
			}
		}
		
		$(document).on('change', '#lab_category_ids', function(e){
			if(!_.isEmpty(e.val)){
				labsFiltered = _.filter(labs, function(obj){
					return _.includes(e.val, obj.category.id);
				});
				resetLabSelect(labsFiltered);
			} else {
				resetLabSelect(labs);
			}
			
		});
		
		var resetLabSelect = function(labsFiltered){
			$('#labs_to_request').select2({
				placeholder: "Search and select lab",
				minimumInputLength: 0,
				width: '100%',
				multiple: true,
				allowClear: true,
				data: {results: labsFiltered, text: 'name'},
				formatResult: function (data) {
					return data.name + " (" + data.category.name + ")";
				},
				formatSelection: function (data) {
					return data.name + " (" + data.category.name + ")";
				}
			});
		};
		
		var getQuantity = function (obj, callbackFn) {
			vex.dialog.prompt({
				message: 'Specify the quantity',
				placeholder: '',
				value: 1,
				overlayClosesOnClick: false,
				beforeClose: function (e) {
					e.preventDefault();
				},
				callback: function (value) {
					if (value !== false && value !== '') {
						obj.quantity = value;
					} else {
						obj.quantity = 1;
					}
					if (typeof callbackFn !== "undefined") {
						callbackFn();
					}
				}, afterOpen: function ($vexContent) {
					var $submit = $($vexContent).find('[type="submit"]');
					$submit.attr('disabled', true);
					$('.vex-dialog-prompt-input').attr('autocomplete', 'off');
					$vexContent.find('.vex-dialog-prompt-input').on('input', function () {
						if ($(this).val().trim() !== '') {
							$submit.removeAttr('disabled');
						} else {
							$submit.attr('disabled', true);
						}
					}).trigger('input');
				}
			});
		}
	</script>

