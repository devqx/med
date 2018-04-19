<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:00 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysiotherapyItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioItemsRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PhysioItemsRequestDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysioItemsRequestData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysioItemsRequest.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PhysiotherapyItem.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
$centres = (new ServiceCenterDAO())->all('Physiotherapy');
@session_start();
if ($_POST) {
	if (is_blank($_POST['service_centre_id'])) {
		exit("error:Select service centre");
	}
	if (is_blank($_POST['patient_id'])) {
		exit("error:Select Patient");
	}
	if (empty(array_filter(explode(",", $_POST['items'])))) {
		exit("error:Select Request Items");
	}
	if (is_blank($_POST['amount'])) {
		exit("error:Amount is required");
	}
	
	$items_ = json_decode($_POST['items_meta']);
	$items = [];
	
	//    $itemsList = array_filter(explode(",",$_POST['items']));
	//    foreach ($itemsList as $item_) {
	foreach ($items_ as $item_) {
		$item = (new PhysiotherapyItem())->setId($item_->id)->setBasePrice(parseNumber($item_->basePrice))->setCode($item_->code)->setName($item_->name);
		$items[] = (new PhysioItemsRequestData())->setItem($item);
	}
	
	$request = (new PhysioItemsRequest())->setAmount(parseNumber($_POST['amount']))->setServiceCentre(new ServiceCenter($_POST['service_centre_id']))->setPatient(new PatientDemograph($_POST['patient_id']))->setRequester(new StaffDirectory($_SESSION['staffID']))->setItems($items);
	if ((new PhysioItemsRequestDAO())->add($request) !== null) {
		exit("success:Request Saved Successfully");
	}
	exit("error:Sorry, we couldn't save the request");
	
	
}
?>

<section class="document" <?= isset($_REQUEST['patient_id']) ? ' style="width: 650px"' : '' ?>>
	<div class="message"></div>
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: null, onComplete: finishRequest})">
		<label> Business Unit/Service
			Center<select name="service_centre_id" placeholder="Select a Receiving center">
				<option></option>
				<?php foreach ($centres as $center) { ?>
					<option value="<?= $center->getId() ?>"><?= $center->getName() ?></option>
				<?php } ?>
			</select> </label>
		<label<?= isset($_REQUEST['patient_id']) ? ' class="hide"' : '' ?>> Patient
			<input type="hidden" name="patient_id" value="<?= (isset($_REQUEST['patient_id']) ? $_REQUEST['patient_id'] : '') ?>">
		</label>

		<label>Request Items:</label>
		<label><input type="hidden" id="items" name="items"></label>

		<label>Total <input type="number" name="amount" min="0" step="any" readonly></label>
		<input type="hidden" name="items_meta">

		<div class="btn-block">
			<button class="btn" type="submit">Request</button>
			<button class="btn-link" type="reset">Reset</button>
			<?php if (isset($_REQUEST['patient_id'])) { ?>
				<button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload();">
					Cancel
				</button><?php } ?>
		</div>
	</form>

</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('[name="patient_id"]').css({'font-weight': 400}).select2({
			placeholder: "Filter list by patient",
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
			},
			initSelection: function (element, callback) {
				var id = $(element).val();
				if (id !== "") {
					$.ajax("/api/search_patients.php?pid=" + id, {
						dataType: "json"
					}).done(function (data) {
						callback(data);
					});
				}
			}
		});

		$('#items').select2({
			placeholder: "Search and select physiotherapy item",
			minimumInputLength: 3,
			width: '100%',
			multiple: true,
			allowClear: true,
			ajax: {
				url: "/api/get_physiotherapy_items.php",
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
			if (evt.added != undefined) {
				vex.dialog.prompt({
					message: 'Press Cancel if you would use the default price of <span class="naira"></span><span class="price-input">' + evt.added.basePrice + '</span>',
					placeholder: 'Enter new Price',
					value: evt.added.basePrice,
					overlayClosesOnClick: false,
					callback: function (value) {
						if (value !== false && value !== '') {
							evt.added.basePrice = value;
						}
						$this.trigger('change');
					}
				});
				//Boxy.input('Press Cancel if you would use the default price of <span class="naira"></span>'+evt.added.basePrice);
			}
			$.each($this.select2("data"), function (index, obj) {
				total += parseFloat(obj.basePrice);
			});
			$('input[name="amount"]').val(total);
			$('input[name="items_meta"]').val(JSON.stringify($('#items').select2('data')));

		});

		$('select[name="service_centre_id"]').select2({width: '100%'});
	});

	var finishRequest = function (s) {
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1])
		} else if (data[0] === "success") {
			Boxy.info(data[1]);
			<?php if(isset($_REQUEST['patient_id'])){?>showTabs(17, 2);
			Boxy.get($(".close")).hideAndUnload();<?php }else {?>
			$('a[data-url="items/requests_list_open.php"]').click();<?php } ?>
		}
	};
</script>