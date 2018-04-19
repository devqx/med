<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/25/14
 * Time: 3:06 PM
 */

$return = (object)null;
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/WardDAO.php';
$protect = new Protect();
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);
if (!$staff->hasRole($protect->accounts) && !$staff->hasRole($protect->bill_auditor) || !$staff->hasRole($protect->records)) {
	exit($protect->ACCESS_DENIED);
}
$billSources = (new BillSourceDAO())->getBillSources();
$serviceCentres = (new ServiceCenterDAO())->all();
$wards = (new WardDAO())->getWards();
if ($_POST) {
	if($_POST['amount'] < 0){
		$return->status = "error";
		exit(json_encode($return));
	}
	if (!$staff->hasRole($protect->accounts)) {
		$return->status = "error";
		$return->message = $protect->ACCESS_DENIED;
		exit(json_encode($return));
	}
	if (is_blank($_POST['service_centre_id'])) {
		$return->status = "error";
		$return->message = 'Service Centre is required';
		exit(json_encode($return));
	}
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MiscellaneousItem.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], FALSE, null, null);
	$item = (new MiscellaneousItem())->setCode('MS00001')->setId(1)->setName('Miscellaneous Item');
	$bil = new Bill();
	$bil->setPatient($pat);
	$bil->setDescription($_POST['description']);
	$bil->setItem($item);
	//$bil->setSource((new BillSourceDAO())->findSourceByName("misc"));
	$bil->setSource((new BillSourceDAO())->getBillSource($_POST['bill_source_id']));
	$bil->setTransactionType("credit");
	$bil->setTransactionDate(date("Y-m-d H:i:s"));
	$bil->setInPatient(!is_blank(@$_POST['aid']) ? new InPatient(@$_POST['aid']) : null);
//    if($_POST['t_Type']==="refund") {
//        $bil->setAmount(0-$_POST['amount']);
//    } else {
	$bil->setAmount($_POST['amount']);
//    }
	$bil->setDiscounted(null);
	$bil->setDiscountedBy(null);
	$bil->setClinic($staff->getClinic());
	$bil->setBilledTo(new InsuranceScheme(1));
	$bil->setMiscellaneous(TRUE);
	$bil->setCostCentre( $_POST['service_center_type']== 'service_center' ? (new ServiceCenterDAO())->get($_POST['service_centre_id'])->getCostCentre() : (new WardDAO())->getWard($_POST['service_centre_id'])->getCostCentre() );
	//$bil->setBilledTo(new InsuranceScheme($_POST['']));

	$bill = (new BillDAO())->addBill($bil, 1);

	if (is_null($bill)) {
		$return->status = "error";
		$return->message = "Sorry, billing failed";
		exit(json_encode($return));
	} else {
		$return->status = "success";
		$return->message = "Bill added successfully";
		exit(json_encode($return));
	}
}

if (isset($_GET['id'])) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	$pat = (new PatientDemographDAO())->getPatient($_GET['id'], FALSE, null, null);
} else {
	$pat = null;
} ?>
<div style="width: 500px">
	<form id="miscChargeForm" method="post" action="<?= $_SERVER['PHP_SELF'] ?>" onsubmit="return confirmTransfer(this)">
		Add Miscellaneous. Bill/Charge <span></span>
		<?php if ($pat != null && $pat->getScheme()->getType() != 'self') { ?>
			<div class="well well-small">
				Patient is insured. Bill might be charged to &laquo;<?= $pat->getScheme()->getName() ?>&raquo;
			</div>
		<?php } ?>
		<div class="well well-small" style="display: none" id="insuranceStatus">
			Patient is insured. Bill might be charged to &laquo;&raquo;
		</div>
		<label><?php if (!isset($_GET['id'])) { ?>Patient<?php } ?>
			<input type="hidden" name="pid" <?= isset($_GET['id']) ? ' value="' . $_GET['id'] . '"' : '' ?>> </label>
		<?php if (isset($_GET['aid'])) { ?><input type="hidden" name="aid" value="<?= $_GET['aid'] ?>"><?php } ?>
		<!--<label>Transaction Type
		<select name="t_Type">
				<option value="refund">REFUND</option>
				<option value="credit" selected="selected">CHARGE</option>
		</select></label>-->
		<label>Description
			<textarea name="description" required></textarea></label>
		<label>Amount <input type="number" name="amount"  > </label>
		<label>Revenue Category <select name="bill_source_id">
				<?php foreach ($billSources as $billSource) {?>
				<option value="<?= $billSource->getId()?>"><?= ucwords(str_replace('_',' ', $billSource->getName()))?></option>
				<?php }?>
			</select></label>
		<label>Service Center <select name="service_centre_id" data-placeholder="Service Center" required="">
				<option></option>
				<?php foreach ($serviceCentres as $serviceCentre){//$serviceCentre=new ServiceCenter();?>
					<option data-type="service_center"  value="<?= $serviceCentre->getId()?>"><?=$serviceCentre->getName() ?> [<?=ucwords($serviceCentre->getType())?>]</option>
				<?php }?>
				<?php foreach ($wards as $ward){?>
					<option data-type="ward" value="<?=$ward->getId()?>"><?= $ward->getName()?> [Ward]</option>
				<?php }?>
			</select></label>
		<input type="hidden" name="service_center_type">
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
		<input type="hidden">
	</form>
</div>
<script>
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
			return ((data.fname + " " + data.mname + " " + data.lname));
		},
		formatSelection: function (data) {
			return ((data.fname + " " + data.mname + " " + data.lname));
		},
		id: function (data) {
			return data.patientId;
		}
	}).change(function () {
		var patient = $(this).select2("data");
		if (patient && patient.insurance.scheme.payType != "self") {
			$("#insuranceStatus").html("Patient is insured. Bill will be charged to &laquo;" + patient.insurance.scheme.name + "&raquo;").show();
		} else {
			$("#insuranceStatus").hide();
		}
	});
	<?php } ?>
	
	$('[name="service_centre_id"]').select2().change(function (e) {
		if(e.added !== undefined ){
			$('[name="service_center_type"]').val(e.added.element[0].dataset.type);
		}
	});

	function confirmTransfer(form) {
		Boxy.ask("Are you sure to charge " + $('input[name="amount"]').val() + "?", ["Yes", "No"], function (choice) {
			if (choice == "Yes") {
				$.ajax({
					url: $(form).attr("action"),
					type: "post",
					data: $(form).serialize(),
					beforeSend: function () {
						$('form > span').html('<img src="/img/loading.gif">');
					},
					complete: function () {
						$('form > span').html('');
					},
					success: function (s) {
						console.log(s);
						$('form > span').html('');
						s = JSON.parse(s);
						if (s.status == "error") {
							Boxy.alert(s.message);
						} else if (s.status == "success") {
							Boxy.info(s.message, function () {
								try {
									showTabs(7);
								} catch (exception) {
								}
								Boxy.get($('.close')).hideAndUnload();
							});
						}
					},
					error: function (d) {
						$("#notify").notify("create", {text: "Sorry action failed"}, {expires: 5000});
					}
				});
			}
		});
		return false;
	}

	function start() {

	}
	function done(s) {
		$('form > span').html('');
		s = JSON.parse(s);
		if (s.status == "error") {
			Boxy.alert(s.message);
		} else if (s.status == "success") {
			Boxy.info(s.message, function () {
				try {
					showTabs(7);
				} catch (exception) {
				}
				Boxy.get($('.close')).hideAndUnload();
			});
		}
	}
</script>