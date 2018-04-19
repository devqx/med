<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/LabGroup.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabComboDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';

$currency = (new CurrencyDAO())->getDefault();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$specimens = (new LabSpecimenDAO())->getSpecimens();
$referrals = (new ReferralDAO())->all();
$allLabCentres = (new ServiceCenterDAO())->all('Lab');
$DATA = (new LabDAO())->getLabs(false);

$labCategories = (new LabCategoryDAO())->getLabCategories();
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->lab) && !$this_user->hasRole($protect->lab_super))
	exit ($protect->ACCESS_DENIED);

if ($_POST) {
	if (isset($_POST['lab-reqs']) && !empty(array_filter($_POST['lab-reqs']))) {
		if(is_blank($_POST['pid'])){exit('error:Please select a patient');}
		
		$request = new LabGroup();
		$request->setPatient((new PatientDemographDAO())->getPatient($_POST['pid'], false));
		$request->setInPatient(isset($_POST['inpatient_id']) && !is_blank($_POST['inpatient_id']) ? new InPatient($_POST['inpatient_id']) : null);
		$request->setRequestedBy($this_user);
		
		$request->setRequestNote($_POST['request_note']);
		
		$pref_specimens = array();
		$sel_specimens = isset($_POST['specimen_ids']) ? $_POST['specimen_ids'] : [];
		foreach ($sel_specimens as $s) {
			if (!empty($s))
				$pref_specimens[] = (new LabSpecimenDAO())->getSpecimen($s);
		}
		$request->setPreferredSpecimens($pref_specimens);
		
		$lab_data = array();
		//$tests = array_filter(explode(",", $_POST['lab-reqs']));
		$tests = array_filter($_POST['lab-reqs']);
		foreach ($tests as $l) {
			$lab_data[] = (new LabDAO())->getLab($l);
		}
		$request->setRequestData($lab_data);
		$request->setServiceCentre((new ServiceCenterDAO())->get($_POST['service_centre_id']));
		$request->setReferral((new ReferralDAO())->get($_POST['referral_id']));
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
<div style="width:700px">
	<script type="text/javascript">
		<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_lab_combos.php';?>
		var labCs = <?=(json_encode($labCombos, JSON_PARTIAL_OUTPUT_ON_ERROR))?>;
		var labs = <?= json_encode($DATA, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;
		var labCategories = <?=json_encode($labCategories, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;

		setInterval(function () {
			//reset checkboxes in hidden categories
			_.each($('label.hide[data-id] :checkbox'), function (obj) {
				$(obj).prop('checked', false).iCheck('update');
			});
		}, 100);
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

		$('.boxy-content #labs_to_request').select2({
			placeholder: "Search and select lab",
			minimumInputLength: 0,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_labs.php",
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
			/*var total = 0;
			 var pid = $('.boxy-content [name="pid"]').val();
			 if (evt.added != undefined) {
			 showInsuranceNotice(pid, evt);
			 }
			 $.each($(this).select2("data"), function () {
			 total = parseFloat(this.basePrice) + total;
			 });
			 $("form label.output").html("Estimated Test cost: &#x20a6;" + parseFloat(total).toFixed(2));*/
		});

		$('.lab-categories:checkbox').live('change', function (evt) {
			var options = '';
			$('label[data-id]').addClass('hide');
			_.each($('.lab-categories:checkbox:checked'), function (obj) {
				var catId = $($(obj).get(0)).data('categoryId');

				_.each(_.filter(labs, function (lab) {
					return Number(lab.category.id) === catId;
				}), function (filtered) {
					$('label[data-id="' + filtered.id + '"]').removeClass('hide');
					//options += '<label><input class="checkboxes" data-code="' + filtered.code + '" data-basePrice="' + filtered.basePrice + '" type="checkbox" name="lab-reqs[]"> ' + filtered.name + ' (' + filtered.category.name + ')</label>';
				})
			});
			//$('#labsList').html(options);


			$('#reset-requests').click(function () {
				$('.checkboxes:checkbox:checked').prop('checked', false).iCheck('update');
			});
			$('.checkboxes:checkbox').live('change', function (evt) {
				if (!evt.handled) {
					var total = 0;
					var pid = $('.boxy-content [name="pid"]').val();
					if (evt.target !== undefined && evt.target.checked) {
						evt.added = $(evt.target);
						evt.added.code = $(evt.target).data('code');
						showInsuranceNotice(pid, evt);
					}
					_.each($('.checkboxes:checkbox:checked'), function (obj) {
						total += parseFloat(obj.dataset.baseprice);
					});

					$("form label.output").html("Estimated Test cost: <?= $currency->getSymbolLeft() ?>" + $.number(total)+"<?= $currency->getSymbolRight() ?>");
					evt.handled = true;
				}
			});

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
	</script>
	<form method="post" action="/labs/allLabs.php" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">
		<label class="output well well-small"></label>
		<div class="row-fluid">
			<label class="span6">Business Unit/Service Center <select name="service_centre_id" data-placeholder="Select a receiving lab center">
					<option></option>
					<?php foreach ($allLabCentres as $center) { ?>
						<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
					<?php } ?>
				</select> </label>
			<label class="span6">Referred by
				<select name="referral_id" data-placeholder="Select referring entity where applicable">
					<option></option>
					<?php foreach ($referrals->data as $ref) {/*$ref = new Referral();*/ ?>
						<option value="<?= $ref->getId() ?>"><?= $ref->getName() ?> (<?= $ref->getCompany()->getName() ?>
						)</option><?php } ?>
				</select>
			</label>
		</div>
		
		<?php if (!isset($_GET['id'])) { ?><label>Patient </label><?php } ?>
		<label><input type="hidden" name="pid" value="<?= (isset($_GET['id']) ? $_GET['id'] : '') ?>"></label>
		<input type="hidden" name="inpatient_id" value="<?= (isset($_GET['aid'])) ? $_GET['aid'] : '' ?>">

		<label class="hide">Lab Combos <input type="hidden" id="lab-combos"></label>
		<!--<label>Lab tests to request:</label>
		<label><input type="hidden" id="labs_to_request" name="lab-reqs"></label>-->
		<div class="row-fluid">
			<div class="overscrollLabDiv span5">
				<label class="fadedText">Lab Categories</label>
				<?php foreach ($labCategories as $category) {//$lab=new Lab()?>
					<label><input class="lab-categories" data-category-id="<?= $category->getId() ?>" type="checkbox"> <?= $category->getName() ?></label>
				<?php } ?>
			</div>
			<div class="overscrollLabDiv span7">
				<div class="fadedText">Labs
					<div class="pull-right"><a href="javascript:;" id="reset-requests"><i class="icon icon-remove"></i> Reset Selections</a></div>
				</div>
				<div id="labsList"></div>
				<?php foreach ($DATA as $lab) {//$lab=new Lab()?>
					<label class="hide" data-id="<?= $lab->getId() ?>"><input class="checkboxes" data-code="<?= $lab->getCode() ?>" data-basePrice="<?= $lab->getBasePrice() ?>" type="checkbox" name="lab-reqs[]" value="<?= $lab->getId() ?>"> <?= $lab->getName() ?>
						(<?= $lab->getCategory()->getName() ?>)</label>
				<?php } ?>
			</div>
		</div>


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