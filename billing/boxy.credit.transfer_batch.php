<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/28/16
 * Time: 3:13 PM
 */
$lockWriteOff = false;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';

$patient = (new PatientDemographDAO())->getPatient($_GET['id'], TRUE);
$bill_sources = (new BillSourceDAO())->getBillSources();

$return = (object)null;

$protect = new Protect();
$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true);
if (!$staff->hasRole($protect->accounts) && !$staff->hasRole($protect->hmo_officer)) {
	exit($protect->ACCESS_DENIED);
}
if (isset($_GET['items']) && isset($_SESSION['checked_bill_all'])) {
	$_SESSION['checked_bill_all'][] = explode(',', $_GET['items']);
}

$items = array_flatten($_SESSION['checked_bill_all']);
$_GET['items'] = isset($_SESSION['checked_bill_all']) ? implode(',', $items) : '';

$data = (new BillDAO())->getBillsToTransfer($_GET['items']);
if ($_POST) {
	if (!$staff->hasRole($protect->accounts) && !$staff->hasRole($protect->hmo_officer)) {
		$return->status = "error";
		$return->message = "You do not have access to this function";
		exit(json_encode($return));
	}

	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/MiscellaneousItem.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$pat = (new PatientDemographDAO())->getPatient($_POST['patient_id'], FALSE, $pdo, null);

	$scheme = new InsuranceScheme($_POST['scheme_id']);
	foreach ($_POST['bill'] as $bill_id) {
		$thisBill = (new BillDAO())->getBill($bill_id, TRUE, $pdo);
		if ($thisBill->getTransferred()) {
			$pdo->rollBack();
			$return->status = "error";
			$return->message = "Bill Line already transferred! [" . $thisBill->getDescription() . "]";
			exit(json_encode($return));
		}
		if (is_blank($_POST['item_code'][$bill_id])) {
			$pdo->rollBack();
			$return->status = "error";
			$return->message = "Item Code not available! [" . $thisBill->getDescription() . "]";
			exit(json_encode($return));
		}
		$transferAmount = $_POST['tr_amount'][$bill_id];
		$item = getItem($_POST['item_code'][$bill_id], $pdo);
		$item->setName((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getDescription());

		if ($item == null) {
			$pdo->rollBack();
			$return->status = "error";
			$return->message = "Cannot determine item for transfer (" . $_POST['item_code'][$bill_id];
			exit(json_encode($return));
		}
		$src = (new BillDAO())->getSourceId($item);

		// charge the patient's hmo for this item
		$bil = new Bill();
		$bil->setPatient($pat);
		$bil->setDescription("" . $item->getName());
		$bil->setSource((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getSource());
		$bil->setItem($item);//$item
		$bil->setTransactionType("transfer-credit");
		$bil->setTransactionDate(date("Y-m-d H:i:s"));
		$bil->setDueDate((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getTransactionDate());
		$bil->setAmount(floatval($transferAmount));
		$bil->setDiscounted(null);
		$bil->setDiscountedBy(null);
		$bil->setClinic($staff->getClinic());
		$bil->setBilledTo($scheme);
		$bil->setAuthCode(!is_blank($_POST['auth_code']) ? $_POST['auth_code'] : null);
		$bil->setReviewed(!is_blank($_POST['auth_code']) ? TRUE : FALSE);
		$bil->setParent(new Bill($bill_id));
		$bil->setTransferred(TRUE);
		$bil->setCostCentre((new BillDAO())->getBill($bill_id, true, $pdo)->getCostCentre());

		//patient part [like payment]
		$bil2 = new Bill();
		$bil2->setPatient($pat);
		$bil2->setDescription("" . $item->getName());
		$bil2->setItem($item); // $item
		$bil2->setSource((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getSource());
		$bil2->setTransactionType("transfer-debit");
		$bil2->setTransactionDate(date("Y-m-d H:i:s"));
		$bil2->setDueDate((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getTransactionDate());
		$bil2->setAmount(0 - floatval($transferAmount));
		$bil2->setDiscounted(null);
		$bil2->setDiscountedBy(null);
		$bil2->setClinic($staff->getClinic());
		$bil2->setBilledTo((new InsuranceScheme(1)));
		$bil2->setAuthCode(!is_blank($_POST['auth_code']) ? $_POST['auth_code'] : null);
		$bil2->setReviewed(TRUE);
		$bil2->setParent(new Bill($bill_id));
		$bil2->setTransferred(TRUE);
		$bil2->setCostCentre((new BillDAO())->getBill($bill_id, true, $pdo)->getCostCentre());
		$bil2->setCancelledOn(date('Y-m-d H:i:s'));
		$bil2->setCancelledBy(new StaffDirectory($_SESSION['staffID']));
		//if there's no amount to be left, hide the main line
		/*if(isset($_POST['write_off'][$bill_id])) {
			$bil2->setCancelledOn(date('Y-m-d H:i:s'));
			$bil2->setCancelledBy(new StaffDirectory($_SESSION['staffID']));
		}*/

		$bill = (new BillDAO())->addBill($bil, $_POST['quantity'][$bill_id], $pdo, null);
		$bill2 = (new BillDAO())->addBill($bil2, $_POST['quantity'][$bill_id], $pdo, null);

		if (isset($_POST['write_off'][$bill_id]) && isset($_POST['difference'][$bill_id]) && $_POST['difference'][$bill_id] > 0) {
			// then write off the difference - positive difference
			$writeOffAmount = $_POST['difference'][$bill_id];

			$bil3 = new Bill();
			$bil3->setPatient($pat);
			$bil3->setDescription("" . $item->getName());
			$bil3->setItem($item);//$item
			$bil3->setSource((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getSource());
			$bil3->setTransactionType("write-off");
			$bil3->setTransactionDate(date("Y-m-d H:i:s"));
			$bil3->setDueDate((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getTransactionDate());
			$bil3->setAmount(0 - $writeOffAmount); // amount just like a debit
			$bil3->setDiscounted(null);
			$bil3->setDiscountedBy(null);
			$bil3->setClinic($staff->getClinic());
			$bil3->setBilledTo((new InsuranceScheme(1)));
			$bil3->setAuthCode(!is_blank($_POST['auth_code']) ? $_POST['auth_code'] : null);
			$bil3->setReviewed(TRUE);
			$bil3->setParent(new Bill($bill_id));
			$bil3->setTransferred(TRUE);
			$bil3->setCostCentre((new BillDAO())->getBill($bill_id, true, $pdo)->getCostCentre());
			$bil3->setCancelledOn(date('Y-m-d H:i:s'));
			$bil3->setCancelledBy(new StaffDirectory($_SESSION['staffID']));

			$bill3 = (new BillDAO())->addBill($bil3, $_POST['quantity'][$bill_id], $pdo, null);
			if (is_null($bill3)) {
				$pdo->rollBack();
				$return->status = "error";
				$return->message = "Write-Off action failed.1";
				exit(json_encode($return));
			}
		}
		if (isset($_POST['write_off'][$bill_id]) && isset($_POST['difference'][$bill_id]) && $_POST['difference'][$bill_id] < 0) {
			// then write off the difference - negative difference: if the transfer amount is higher than what was charged
			$writeOffAmount = $_POST['difference'][$bill_id];

			$bil4 = new Bill();
			$bil4->setPatient($pat);
			$bil4->setDescription("Diff: " . $item->getName());
			$bil4->setItem($item);//$item
			$bil4->setSource((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getSource());
			$bil4->setTransactionType("credit");
			$bil4->setTransactionDate(date("Y-m-d H:i:s"));
			$bil4->setDueDate((new BillDAO())->getBill($bill_id, FALSE, $pdo)->getTransactionDate());
			$bil4->setAmount(abs($writeOffAmount)); // amount just like a debit
			$bil4->setDiscounted(null);
			$bil4->setDiscountedBy(null);
			$bil4->setClinic($staff->getClinic());
			$bil4->setBilledTo((new InsuranceScheme(1)));
			$bil4->setAuthCode(!is_blank($_POST['auth_code']) ? $_POST['auth_code'] : null);
			$bil4->setReviewed(TRUE);
			$bil4->setParent(new Bill($bill_id));
			$bil4->setTransferred(TRUE);
			$bil4->setCostCentre((new BillDAO())->getBill($bill_id, true, $pdo)->getCostCentre());
			//$bil4->setCancelledOn(date('Y-m-d H:i:s'));
			//$bil4->setCancelledBy(new StaffDirectory($_SESSION['staffID']));

			$bill4 = (new BillDAO())->addBill($bil4, $_POST['quantity'][$bill_id], $pdo, null);
			if (is_null($bill4)) {
				$pdo->rollBack();
				$return->status = "error";
				$return->message = "Write-Off action failed.2";
				exit(json_encode($return));
			}
		}
		// then mark this item as `transferred`
		if (is_null($bill) || is_null($bill2) || is_null($thisBill->setTransferred(TRUE)->setActiveBill('not_active')->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->setCancelledOn(date('Y-m-d H:i:s'))->update($pdo))) {
			$pdo->rollBack();
			$return->status = "error";
			$return->message = "Sorry, transfer failed";
			exit(json_encode($return));
		}
	}
	$pdo->commit();
	$return->status = "success";
	$return->message = "Credit Transferred";
	exit(json_encode($return));
}
?>

<section style="width: 1200px">
	<?php if (!$patient->getInsurance()->getActive()) { ?>
		<div class="alert-box notice">Patient is not eligible for transfers (Patient's insurance has expired!)</div>
	<?php } else { ?>
		<span>Charges have been filtered appropriately</span>
		<form name="transferForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">
			<div class="row-fluid">
				<label class="span6">Patient <select name="patient_id" required="required">
						<option value="<?= $patient->getId() ?>"><?= $patient->getFullname() ?></option>
					</select></label>
				<label class="span6">Insurance <select name="scheme_id" required="required">
						<option value="<?= $patient->getScheme()->getId() ?>"><?= $patient->getScheme()->getName() ?></option>
					</select></label>
			</div>
			<hr class="border">
			<table class="table table-striped">
				<thead>
				<tr class="menu-head">
					<th>*</th>
					<th>Date</th>
					<th>Description</th>
					<th class="amount">Qty</th>
					<th class="amount">HMO Price</th>
					<th class="amount">CoPay(%)</th>
					<th nowrap class="amount">Transfer</th>
					<th class="amount" nowrap>Hospital Price</th>
					<th class="amount">Diff</th>
					<th nowrap><label><input <?= $lockWriteOff ?'disabled':'' ?> type="checkbox" id="writeOffAll" onclick="checkWriteOff(this)"> WriteOff</label></th>
				</tr>
				</thead>
				<?php foreach ($data as $b) { ?>
					<tr>
						<td>
							<label><input type="checkbox" onchange="enableLine(this)" name="bill[]" data-id="<?= $b->bill_id ?>" value="<?= $b->bill_id ?>" id="bill<?= $b->bill_id ?>"></label>
						</td>
						<td nowrap>
							<label for="bill<?= $b->bill_id ?>"><?= date("Y/m/d ga", strtotime($b->transaction_date)) ?></label></td>
						<td><label for="bill<?= $b->bill_id ?>"><?= $b->description ?>
								<input type="hidden" name="item_code[<?= $b->bill_id ?>]" value="<?= $b->item_code ?>"></label></td>
						<td class="">
							<label for="bill<?= $b->bill_id ?>"><input min="0" type="text" disabled data-editable readonly class="price" data-id="<?= $b->bill_id ?>" name="quantity[<?= $b->bill_id ?>]" onchange="updateAmount(this)" value="<?= $b->quantity ?>" step="0.01"></label>
						</td>
						<td class="" nowrap>
							<label for="_bill<?= $b->bill_id ?>" style="text-align: right">
								<?= (is_null((new InsuranceItemsCostDAO())->getInsuranceItem($b->item_code, $b->patient_id)) ? '<i class="fa fa-exclamation-triangle" style="color:#ffa03d;" title="Item is not covered for patient under the current scheme"></i>' : '') ?>
								<input min="0" type="text" disabled data-editable class="price" data-id="<?= $b->bill_id ?>" name="unit_price[<?= $b->bill_id ?>]" onchange="updateAmount(this)"
								       value="<?= (new InsuranceItemsCostDAO())->getInsuranceItem($b->item_code, $b->patient_id) ? (new InsuranceItemsCostDAO())->getInsuranceItem($b->item_code, $b->patient_id)->{$b->price_type} : '' ?>" step="0.01"></label>
						</td>
						<td class="">
							<label for="_bill<?= $b->bill_id ?>">
								<input type="text" min="0" max="100" step="0.01" disabled data-editable class="price" data-id="<?= $b->bill_id ?>" name="co_pay[<?= $b->bill_id ?>]" value="0" onchange="updateAmount(this)">
							</label>
						</td>
						<td class="">
							<label for="_bill<?= $b->bill_id ?>">
								<input min="0" step="0.01" type="text" disabled data-editable class="price" readonly
								       data-id="<?= $b->bill_id ?>" name="tr_amount[<?= $b->bill_id ?>]" onchange="updateAmount(this)"
								       value="<?= (new InsuranceItemsCostDAO())->getInsuranceItem($b->item_code, $b->patient_id) ? (new InsuranceItemsCostDAO())->getInsuranceItem($b->item_code, $b->patient_id)->{$b->price_type} : '' ?>">
							</label></td>
						<td class="">
							<label for="bill<?= $b->bill_id ?>"><input min="0" step="0.01" type="text" disabled class="price" readonly data-id="<?= $b->bill_id ?>" name="amount[<?= $b->bill_id ?>]" onchange="updateAmount(this)" value="<?= $b->amount ?>"></label>
						</td>
						<td class="">
							<label for="bill<?= $b->bill_id ?>"><input min="0" step="0.01" type="text" class="price" disabled data-editable readonly data-id="<?= $b->bill_id ?>" name="difference[<?= $b->bill_id ?>]" onchange="updateAmount(this)" value="0"></label>
						</td>
						<td>
							<label class="text-center"><input type="checkbox" name="write_off[<?= $b->bill_id ?>]" disabled data-editable data-id="<?= $b->bill_id ?>"></label>
						</td>
					</tr>
				<?php } ?>
			</table>
			<div class="row-fluid">
				<label class="span9">Auth. Code <input name="auth_code" type="text" required="required"> </label>
				<label class="span3 border" style="margin-top: 25px;"><input type="checkbox" name="reviewed"> To be
					reviewed</label>
			</div>

			<div class="btn-block">
				<button class="btn" type="submit"> Transfer</button>
				<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
			</div>
		</form>
	<?php } ?>
</section>
<script type="text/javascript">
	$(document).ready(function () {
		$('.price').number(true, 2);
		//$('#writeOffAll').trigger('change');
	}).on('change', 'input[name="reviewed"]', function (e) {
		if ($('input[name="reviewed"]').is(":checked")) {
			$('input[name="auth_code"]').prop('required', false);
		} else {
			$('input[name="auth_code"]').prop('required', true);
		}
	}).on('submit', 'form[name="transferForm"]', function (e) {
		var form = this;
		if (!e.handled) {
			<?php if($lockWriteOff){?>
			$('input[name^="write_off"]:disabled[data-editable][data-id]:checked').prop('disabled', false).iCheck('update');
			<?php }?>showPinBox(function () {
				$.ajax({
					url: form.action,
					data: $(form).serialize(),
					type: "POST",
					complete: function (xhr, status) {
						_form_saveHandler(xhr.responseText);
						
						<?php if($lockWriteOff){?>$('input[name^="write_off"][data-editable][data-id]:checked').prop('disabled', true).iCheck('update');
						<?php }?>
					}
				});
			});
			e.handled = true;
			return false;
		}
	});
	function updateAmount(element) {
		var key = $(element).data("id"), difference, quantity, unitPrice, coPay, tr_amount, totalAmount, writeOff;

		difference = $('input[name^="difference"][data-id="' + key + '"]');
		quantity = $('input[name^="quantity"][data-id="' + key + '"]').val();
		unitPrice = $('input[name^="unit_price"][data-id="' + key + '"]').val();
		coPay = $('input[name^="co_pay"][data-id="' + key + '"]').val();
		totalAmount = $('input[name^="amount"][data-id="' + key + '"]').val();
		tr_amount = $('input[name^="tr_amount"][data-id="' + key + '"]');
		writeOff = $('input[name^="write_off"][data-id="' + key + '"]');

		difference.val((parseFloat(totalAmount)).toFixed(2) - parseFloat(quantity * unitPrice));
		tr_amount.val(parseFloat(((100 - coPay) / 100) * quantity * unitPrice).toFixed(2));
		if (parseFloat(difference.val()) != 0) {
			writeOff.prop({"disabled": false, "checked": true});
		} else if (parseFloat(difference.val()) == 0) {
			writeOff.prop({"disabled": true, "checked": false});
		}
	}

	function enableLine(element) {
		var key = $(element).data("id");
		var difference = $('input[name^="difference"][data-id="' + key + '"]');
		$('input[name^="unit_price"][data-id="' + key + '"]').trigger('change');
		var writeOff = $('input[name^="write_off"][data-id="' + key + '"]');
		if (parseFloat(difference.val()) != 0) {
			writeOff.prop("disabled", false);
		} else if (parseFloat(difference.val()) == 0) {
			writeOff.prop("disabled", true);
		}
		if ($(element).is(":checked")) {
			$.each($('input[data-id="' + key + '"][data-editable]'), function (i, obj) {
				$(obj).prop({"disabled": false, "checked": true}).iCheck('update');
				<?php if($lockWriteOff){?>if($(obj).is('[name^="write_off"]')){
					$(obj).prop({"disabled": true, "checked": true}).iCheck('update');
				}<?php }?>
			});
		} else {
			$.each($('input[data-id="' + key + '"][data-editable]'), function (i, obj) {
				$(obj).prop({"disabled": true, "checked": false}).iCheck('update');
			});
		}
	}

	function _form_saveHandler(s) {
		var data = JSON.parse(s);
		if (data.status === "error") {
			$('section > span').html(data.message).removeClass('warning-bar').addClass('warning-bar');
			$(".boxy-content").animate({scrollTop: 0}, "slow");
		} else if (data.status === "success") {
			Boxy.info(data.message);
			Boxy.get($(".close")).hideAndUnload();
			<?php if(isset($_GET['aid'])){?>showTabs(13);
			<?php } else {?>showTabs(7);<?php }?>
			//the tab will auto-refresh because this window was invoked passing the `afterHide` option
		}
	}
	function checkWriteOff(element) {
		if ($(element).is(":checked")) {
			$.each($('input[name^="write_off"]:not(:disabled)'), function (i, obj) {
				$(obj).prop("checked", true).iCheck('update');
			});
		} else {
			$.each($('input[name^="write_off"]:not(:disabled)'), function (i, obj) {
				$(obj).prop("checked", false).iCheck('update');
			});
		}
	}
</script>