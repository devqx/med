<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/6/17
 * Time: 11:23 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequestData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemBatch.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Encounter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$_GET['suppress'] = true;

$protect = new Protect();
if (!isset($_SESSION)) {
	session_start();
}
if (!isset($_SESSION ['staffID'])) {
	exit('error:Your session has expired. Please login again');
}


$service_center = (new ServiceCenterDAO())->all('item');
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientItemRequest.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$r_code = (new PatientItemRequest())->generateItemCode($pdo);
	if ((!isset($_GET['id']) && is_blank($_POST['pid'])) && (!isset($_GET['aid']) && is_blank($_POST['inpatient']))) {
		$pdo->rollBack();
		exit("error:Patient info not found");
	}
	
	if (is_blank($_POST['service_center_id'])) {
		$pdo->rollBack();
		exit("error:Select business unit");
	}
	if (is_blank($_POST['generic_id']) && count(array_filter($_POST['item_id'])) <= 0) {
		$pdo->rollBack();
		exit("error:Item or generic name is required");
	}
	
	$inpat = null;
	if (!empty($_POST['pid'])) {
		$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], true, $pdo);
	} else {
		$pat = (new PatientDemographDAO())->getPatient($_GET['id'], true, $pdo);
	}
	if (!empty($_POST['inpatient'])) {
		$inpat = (new InPatientDAO())->getInPatient($_POST['inpatient'], false, $pdo);
		$pat = new PatientDemograph($inpat->getPatient()->getId());
	}
	$note = $_POST['note'];
	$gen = null;
	if (!empty($_POST['generic_id'])) {
		$gen = (new ItemGenericDAO())->get($_POST['generic_id'], $pdo);
	}
	$data = [];
	foreach ($_POST['item_id'] as $index => $it) {
		$item = (new ItemDAO())->getItem($_POST['item_id'][$index], $pdo);
		$data[] = (new PatientItemRequestData())->setItem($item)->setHospId(1)->setGroupCode($r_code)->setQuantity($_POST['qty'][$index])->setStatus('open')->setGeneric($gen);
	}
	unset($it);
	$encounter = (!is_blank(@$_REQUEST['encounter_id']) ? new Encounter(@$_REQUEST['encounter_id']) : null);
	$staff = new StaffDirectory($_SESSION['staffID']);
	$item_request = (new PatientItemRequest())->setServiceCenter($_POST['service_center_id'])->setPatient($pat)->setInpatient($inpat)->setCode($r_code)->setRequestedBy($staff)->setRequestNote($note)->setEncounter($encounter)->setData($data)->add($pdo);
	if ($item_request != null) {
		$pdo->commit();
		exit("success:Your request was successful");
	}
	$pdo->rollBack();
	exit("error:Your request was not successful");
}
?>
<div style="width: 750px;">
	<form method="post" id="new_item_request" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: start, onComplete: done})">
		<h4>Consumable Item</h4>
		<div id="itemData">
			<div>
				<?php if (!isset($_GET['id']) && !isset($_GET['aid'])) { ?>
					<label>
						Patient
						<input type="hidden" name="pid" id="pid" value="<?= @$_GET['id'] ?>"/>
					</label>
				<?php } ?>
				<label>Business Unit/Service Center
					<select id="service_center_id" name="service_center_id" data-placeholder="-- Select Service Center --">
						<option value=""></option>
						<?php foreach ($service_center as $k => $center) { ?>
							<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
						<?php } ?>
					</select></label>
				<label>Consumables Group<input type="text" name="item_group" id="item_group" placeholder="select item group"></label>
				<label>Item Generic Name<input type="text" name="generic_id" id="generic_id" class="generic_id" placeholder="select item generic"></label>
				<label><span class="pull-right"><a class="btn btn-mini add_item"><i class="icon-plus"></i></a> </span> </label>
				<div class="row-fluid request_items">
					<label class="span6">Item<input class="item_id" type="text" name="item_id[]" placeholder="select item"></label>
					<label class="span5">Quantity<input type="number" data-decimals="0" class="qty" name="qty[]" placeholder="Add Type quantity"></label>
				</div>
				<label>
					Note
					<textarea name="note" placeholder="Request Note" id="note" cols="3"></textarea>
				</label>
			</div>
		</div>
		<?php if (isset($_GET['aid'])) { ?>
			<input type="hidden" id="inpatient" name="inpatient" value="<?= $_GET['aid'] ?>"><?php } ?>
		<div class="btn-block">
			<button class="btn" type="submit">Submit</button>
			<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</div>

<script>
	var c;
	function start() {
		$(document).trigger('ajaxSend');
	}
	function done(s) {
		$(document).trigger('ajaxStop');

		var data = s.split(':');
		if (data[0] === 'error') {
			Boxy.warn(data[1]);
		} else if (data[0] === 'success') {
			Boxy.info(data[1], function () {
				Boxy.get($(".close")).hideAndUnload();
			});
			<?php if(isset($_GET['aid'])){?>
			showTabs(20);//inpatient
			<?php } else if(!isset($_GET['aid']) && !isset($_GET['id'])){?>
			goto(0);//main module page
			<?php } else {?>
			showTabs(20);//patient tab
			<?php }?>
		}
	}

	var inPatientContext = true;
	$(document).ready(function () {
		if (inPatientContext) {
			$('.boxy-content [name="pid"]').select2({
				placeholder: "Patient Name (Patient ID [Patient Legacy ID])",
				allowClear: true,
				minimumInputLength: 3,
				width: '100%',
				formatResult: function (data) {
					return data.fullname + " -" + data.id + (data.lid.trim() !== "" ? "[" + data.lid + "]" : "") + ", Phone: " + data.phone;
				},
				formatSelection: function (data) {
					return data.fullname + " -" + data.id + ", " + data.sex + ", " + moment(data.dob).fromNow(true) + " old " + (typeof data.vitalSigns !== "undefined" && typeof data.vitalSigns.weight !== "undefined" ? ", " + data.vitalSigns.weight.value + "kg" : "");
				},
				formatNoMatches: function (term) {
					return "Sorry no record found for '" + term + "'";
				},
				formatInputTooShort: function (term, minLength) {
					return "Please enter the patient name or ID";
				},
				ajax: {
					url: '/api/search_patients.php',
					dataType: 'json',
					data: function (term, page) {
						return {
							q: term, // search term
							limit: 100,
							asArray: true,
							medical: true
						};
					},
					results: function (data, page) {
						return {results: data};
					}
				}
			});
		}
		// block key typing
		$('#item_group').on('keyup', function () {
			Boxy.alert("Select Service Center First");
			$(this).val('');
		});
		$('#generic_id').on('keyup', function () {
			Boxy.alert("Select Service Center First");
			$(this).val('');
		});

		$('.item_id').on('keyup', function () {
			Boxy.alert("Select Service Center First");
			$(this).val('');
		});

		$('#service_center_id').on('change', function (e) {
			if (!e.handled) {
				var id;
				var c = $(this).val();
				if (c !== '') {
					getItemGroup(c);
					getItemGeneric(id, c);
					getItems(id, c);
				}
			}
		});
		$('#item_group').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				if (id !== '') {
					getItemGeneric(id, c);
				}
			}
		});
		$('.generic_id').on('change', function (e) {
			if (!e.handled) {
				var id = $(this).val();
				if (id !== "") {
					getItems(id, c);
				}
			}
		});

	}).on('click', '.add_item', function (e) {
		$('.generic_id').select2('data', null);
		if (!e.handled) {
			var tmpholder = '<div class="row-fluid request_items"><label class="span6">Item<input class="item_id" type="text" name="item_id[]"  required></label><label class="span5">Quantity<input type="number" class="qty" data-decimals="0" name="qty[]" required placeholder="Add Type quantity" required></label><label class="span1" style="margin-top: 25px;"><a class="btn btn-mini remove_item">&minus;</a> </label></div>'
			$('.request_items:last').after(tmpholder);
			$(document).trigger('ajaxStop');
			e.handled = true;
			getItems();
		}
	}).on('click', '.remove_item', function (e) {
		if (!e.handled) {
			if ($('.request_items').length > 1) {
				$(this).parents('.request_items').remove();
			}
			e.handled = true;
		}
	});

	function getItems(g, c) {
		$.ajax({
			url: '/api/get_item.php',
			type: 'POST',
			dataType: 'json',
			data: {gid: g/*, sc_id:c*/},
			success: function (result) {
				setItems(result);
			}
		});
	}

	function setItems(data) {
		$('.item_id').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (result) {
				return result.name;
			},
			formatSelection: function (result) {
				return result.name;
			}
		});
	}


	function getItemGroup(s) {
		$.ajax({
			url: '/api/get_item_group.php',
			type: 'POST',
			dataType: 'json',
			data: {c_id: s},
			success: function (result) {
				setItemsGroups(result);
			}
		});
	}

	function setItemsGroups(data) {
		$('input[name="item_group"]').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item group",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		});
	}

	function getItemGeneric(g, c) {
		$.ajax({
			url: '/api/get_item_generic.php',
			type: 'POST',
			dataType: 'json',
			data: {g_id: g, c_id: c},
			success: function (result) {
				setItemGenerics(result);
			}
		});
	}

	function setItemGenerics(data) {
		$('.generic_id').select2({
			width: '100%',
			allowClear: true,
			placeholder: "select item generic",
			data: function () {
				return {results: data, text: 'name'};
			},
			formatResult: function (source) {
				return source.name;
			},
			formatSelection: function (source) {
				return source.name;
			}
		});
	}

</script>



