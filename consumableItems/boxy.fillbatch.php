<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/9/17
 * Time: 11:19 AM
 */

@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();


$protect = new Protect();
if (!isset($_SESSION)) {
	session_start();
}
if (!isset($_SESSION ['staffID'])) {
	exit('error:Your session has expired. Please login again');
}
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->pharmacy)) {
	exit ($protect->ACCESS_DENIED);
}


$_REQUEST['code'] = (isset($_REQUEST['pCode']) ? $_REQUEST['pCode'] : $_REQUEST['code']);
$_REQUEST['pCode'] = (isset($_REQUEST['code']) ? $_REQUEST['code'] : $_REQUEST['pCode']);
$it_r = (new PatientItemRequestDAO())->getItemByCode_($_REQUEST['pCode'], false, null);
$bills = new Bills();
$pat = (new PatientDemographDAO())->getPatient($it_r->getPatient()->getId(), false, null, null);
$b_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$patOwe = $b_ > 0 ? $b_ : 0;
$total = 0;
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
	require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
	
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	
	if (count(array_filter($_POST['batch_id'])) == 0) {
		$pdo->rollBack();
		exit("error:Batch is required");
	}
	if (count(array_filter($_POST['item_id'])) == 0) {
		$pdo->rollBack();
		exit("error:Item is required");
	}
	if (count(array_filter($_POST['quty'])) == 0) {
		$pdo->rollBack();
		exit("error:Quantity can not be empty");
	}
	$filled_qty = 0;
	$rem_qty = 0;
	foreach ($_POST['item_id'] as $index => $it) {
		$f_qty = $_POST['quty'][$index];
		$batch = (new ItemBatchDAO())->getBatch($_POST['batch_id'][$index], $pdo);
		$batch_ = (new ItemBatch($_POST['batch_id'][$index]));
		$item = (new ItemDAO())->getItem($_POST['item_id'][$index], $pdo);
		$fil = (new PatientItemRequestDataDAO())->getRequestDatum($_POST['request_id'][$index], false, $pdo);
		$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($fil->getItem()->getCode(), $pat->getId(), true, $pdo);
		$ip = (new PatientItemRequestDAO())->getItemByCode_($fil->getGroupCode(), false, $pdo);
		$ipId =  $ip->getInPatient() ? $ip->getInPatient()->getId() : null;
		
		if ($f_qty <= $batch->getQuantity()) {
			$rem_qty = $batch->getQuantity() - $f_qty;
			$filled_qty = $f_qty;
		} else {
			$pdo->rollBack();
			exit("error:You don't have enough item in the stock");
		}
		
		if (strtotime($batch->getExpirationDate()) < time()) {
			$pdo->rollBack();
			exit("error:Selected batch has expired");
		}
		$bill = new Bill();
		$bill->setPatient($pat);
		$bill->setInPatient($it_r->getInpatient());
		$bill->setDescription("Consumable item: " . $item->getName());
		$bill->setClinic($this_user->getClinic());
		$bill->setItem($item);
		$bill->setSource((new BillSourceDAO())->findSourceById(11, $pdo));
		$bill->setTransactionType('credit');
		$bill->setAmount(floatval($price) * $filled_qty);
		$bill->setDiscounted(null);
		$bill->setDiscountedBy(null);
		$bill->setBilledTo($pat->getScheme());
		
		$bil = (new BillDAO())->addBill($bill, $filled_qty, $pdo, $ipId);
		if (!$pdo->inTransaction()) {
			exit("error:System error 5");
		}
		// update batch item
		$bt = new ItemBatch();
		$bt->setQuantity($rem_qty);
		$bt->setId($batch->getId());
		$batch_update = (new ItemBatchDAO())->update($bt, $pdo);
		if ($batch_update === null) {
			$pdo->rollBack();
			exit("error:Unable to update stock");
		}

		$fil->setId($_POST['request_id'][$index]);
		$fil->setItem($item);
		$fil->setBatch($batch_);
		$fil->setFilledQuantity($filled_qty);
		$fil->setFilledBy($_SESSION['staffID']);
		$fill = (new PatientItemRequestDataDAO())->fillRequest($fil, $pdo);
		if ($fill == false) {
			$pdo->rollBack();
			exit("error:Could not fill request");
		}
		
	}
	
	if ($pdo->inTransaction()) {
		$pdo->commit();
		exit("success:Your request has been filled");
	}
	
	
}
?>

<div style="width: 1050px;">
	<table class="table">
		<tbody>
		<tr>
			<td rowspan="3" style="width:100px">
				<img style="height: 100px" src="<?= $it_r->getPatient()->getPassportPath() ?>">
			</td>
			<td>
				<span class="fadedText">ID:</span>
				<a href="/patient_profile.php?id=<?= $it_r->getPatient()->getId() ?>"
				   target="_blank"><?= $it_r->getPatient()->getId() ?></a>
			</td>
			<td>
				<span class="fadedText ">Date of Birth:</span><?= date("jS M, Y", strtotime($it_r->getPatient()->getDateOfBirth())) ?>
				(<?= ($it_r->getPatient()->getAge()) ?>)
			</td>
		</tr>
		<tr>
			<td>
				<span class="fadedText ">Name:</span> <?= $it_r->getPatient()->getFullName() ?>
			</td>
			<td>
				<span class="fadedText ">Insurance:</span> <?= (new PatientDemographDAO())->getPatient($it_r->getPatient()->getId(), false)->getScheme()->getName() ?>
			</td>
		</tr>
		<tr>
			<td>
				<span class="fadedText ">Sex:</span> <?= ucwords($it_r->getPatient()->getSex()) ?>
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div class="<?= ($patOwe <= 0) ? "notify-bar" : "warning-bar" ?>">
					<span class="fadedText block">Outstanding Balance</span><?= $currency ?><span> <?= number_format($patOwe, 2); ?></span>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div id="tab-container" class="tab-container">
		<div id="prescriptions_instructions">
			<div class="box">Item Request: <?= $it_r->getCode() ?>,
				Requested by <?= $it_r->getRequestedBy()->getFullname() ?>
				on <?= date("d M, Y H:i A", strtotime($it_r->getRequestDate())) ?>
				<span class="pull-right"><i class="fa fa-print"></i><a
						href="/consumableItems/print_item_request.php?pcode=<?= $it_r->getCode() ?>" target="_blank"
						title="Print this Request">Print</a> </span>

			</div>
			<div class="box">
				<span class="fadedText">Request Notes</span>: <br><?= $it_r->getRequestNote() ?>
			</div>
			<form method="post" name="fillForm" action="<?= $_SERVER['REQUEST_URI'] ?>"
			      onsubmit="return AIM.submit(this, {onStart:start, onComplete:done})">
				<input type="hidden" name="code" value="<?= $it_r->getCode() ?>">
				<table class="table table-striped" id="presList">
					<thead>
					<tr class="ui-bar-d">
						<th width="92px"></th>
						<th>Generic</th>
						<td>Item</td>
						<th>Price</th>
						<th>Batch</th>
						<th>Quantity</th>
						<th></th>
					</tr>
					</thead>
					<?php
					
					$need_to_fill = false;
					$need_to_complete = false;
					foreach ($it_r->getData() as $it_) {
					$itm = $it_->getItem() ? $it_->getItem()->getId() : '';
					$batches = (new ItemBatchDAO())->getBatchesByItem($itm);
					if (in_array($it_->getStatus(), ["open"])) {
						$need_to_fill = true;
					} else if (in_array($it_->getStatus(), ['filled'])) {
						$need_to_complete = true;
					} ?>
					<tr>
						<?php if (!in_array($it_->getStatus(), ['cancelled', 'completed'])) { ?>
							<td class="fadedText">
								<a class="cancel_action btn btn-small1 pull-left-" href="javascript:void(0)"
								   data-action="cancel"
								   data-id="<?= $it_->getId() ?>" title="Cancel this Request">
									<i class="icon-trash"></i></a>
							</td>
							<td class="fadedText">
								<?= $it_->getGeneric() ? $it_->getGeneric()->getName() : '...' ?>
							</td>
							<td>
								<?= $it_->getItem() ? '<input type="text" name="item_id[]"  value="' . $it_->getItem()->getName() . '" disabled="disabled"><input type="hidden" name="item_id[]" value="' . $it_->getItem()->getId() . '">' : '<input type="hidden" data-generic-id="' . $it_->getGeneric()->getId() . '" id="item_id_' . $it_->getGeneric()->getId() . '" name="item_id[]" >' ?>
								<input type="hidden" name="request_id[]" value="<?= $it_->getId() ?>">
							</td>
							<td>
								<input class="amount" style="width: 75px"  readonly type="number" name="price[]"
								       id="price_<?= $it_->getId() ?>"
								       value="<?= $it_->getItem() !== null ? $it_->getItem()->getBasePrice() : '0' ?>">
							</td>
							<td>
								<select name="batch_id[]" id="batch_<?= $it_->getId() ?>"
								        placeholder="select item batch">
									<option></option>
									<?php foreach ($batches as $batch_) { ?>
										<option value="<?= $batch_->getId() ?>"<?= $it_->getBatch() && $it_->getBatch()->getId() == $batch_->getId() ? 'selected="selected"' : '' ?>><?= $batch_->getName() ?></option>
									<?php } ?>
								</select>

							</td>
							<td>
								<input
									style="margin-bottom:-2px;display: inline; width:90px" <?= (in_array($it_->getStatus(), ["cancelled", "completed"]) ? 'disabled ' : '') ?>
									id="quantity_<?= $it_->getId() ?>" type="number" data-decimals="0"
									name="quty[]" title="<?= $it_->getStatus() ?>"
									value="<?= $it_->getQuantity() ? $it_->getQuantity() : '0' ?>"
									min="0" max="0">
							</td>
						<?php } ?>
						<?php } ?>
					</tr>
					<tr>
						<td colspan="5">TOTAL PRICE:</td>
						<td id="console">0.00</td>
					</tr>
				</table>
				<div class="btn-block">
					<?php if (!in_array($it_->getStatus(), ['cancelled', 'filled', 'completed'])) { ?>
						<button type="submit" class="btn">Fill</button>
					<?php } ?>
					<?php if (!in_array($it_->getStatus(), ['cancelled', 'completed', 'open'])) { ?>
						<input type="button" data-id="<?= $it_r->getCode() ?>" class="btn" value="complete"
						       id="complete">
					<?php } ?>
					<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
						Cancel
					</button>
				</div>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
			</form>

		</div>
	</div>
	<script type="text/javascript">
		var tempTotal = 0;
		$(document).ready(function () {

			$('input[name="quty[]"]').each(function () {
				tempTotal += $(this).val() * $(this).parents().prev().prev().children().val();
			});
			$('#console').html('<?= $currency ?>' + $.number(tempTotal, 2));

			$('.cancel_action').click(function (e) {
				var action = $(this).data("action");
				var item_id = $(this).data("id");
				if (action === "cancel") {
					cancelRequestItem(item_id);
				}
				e.preventDefault();
			});


			$('input[data-generic-id]').select2({
				placeholder: "select item",
				width: '100%',
				query: function (options) {
					var el = options.element;
					$.ajax({
						url: '/api/get_item.php',
						type: 'POST',
						dataType: 'json',
						data: {gid: el.data("generic-id")},
						complete: function (result) {
							var result_ = result.responseJSON;
							if (result_ != null) {
								$(el).select2("destroy");
								loadItems(result_, $(el));
								$(el).select2("open");
							} else {
								Boxy.alert("No item on this generic");
							}
						}
					});
				}
			}).on('change', function (e) {
				var selEl = "#" + $(this).parent().next().next().find('select').attr('id');
				$(selEl).select2('data', '');
				var price_input = "#" + $(this).parent().next().find('input').attr('id');
				$(price_input).val(e.added.basePrice);
				var qty = "#" + $(this).parent().next().next().next().find('input').attr('id');
				tempTotal += e.added.basePrice * $(qty).val();
				$('#console').html('&#8358;' + $.number(tempTotal, 2));
				$.ajax({
					url: '/api/get_item_batch.php',
					dataType: 'json',
					data: {id: e.val},
					complete: function (data) {
						for (var i = 0; i < data.responseJSON.length; i++) {
							$('<option value="' + data.responseJSON[i].id + '">' + data.responseJSON[i].name + '</option>').appendTo(selEl);
						}
					},

				});

			});

			$('input[name="quty[]"]').on('keyup change', function () {
				updateTotal();
			});

		});


		function loadItems(data, element) {
			$(element).select2({
				placeholder: "select item",
				width: '100%',
				data: data,
				formatResult: function (data) {
					return data.name;
				},
				formatSelection: function (data) {
					return data.name;
				}
			});
		}
		function updateTotal() {
			var tempTotal = 0;
			$('input[name="quty[]"]').each(function () {
				tempTotal += $(this).val() * $(this).parents().prev().prev().children().val();
			});
			$('#console').html('&#8358;' + $.number(tempTotal, 2));
		}

		function start() {
		}
		function end(s) {
			if (s === "ok") {
				Boxy.info("Success", function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			}
			else {
				var data = s.split(":");
				Boxy.alert(data[1]);
			}
		}

		function cancelRequestItem(id) {
			if (confirm("Are you sure you want to cancel this request?")) {
				vex.dialog.prompt({
					message: 'Please enter your reason for cancellation',
					placeholder: 'Request Cancellation note',
					value: null,
					overlayClosesOnClick: false,
					beforeClose: function (e) {
						e.preventDefault();
					},
					callback: function (value) {
						if (value !== false && value !== '') {
							$.ajax({
								url: '/api/item.php',
								data: {action: 'cancel', id: id, reason: value},
								type: 'POST',
								complete: function (xhr, status) {
									if (status === "success" && xhr.responseText === "true") {
										$('[data-id="' + id + '"]').prev().html("cancelled");
										$('[data-id="' + id + '"]').parents('tr').find('td input').prop('disabled', true);
										$('[data-id="' + id + '"]').parents('tr').find('td select').prop('disabled', true);
										$('[data-id="' + id + '"]').remove();
										Boxy.get($(".close")).hideAndUnload();
									}
								}
							});

						} else {

						}
					}
				});
			}
		}
		function start() {
			$(document).trigger('ajaxSend');
		}
		function done(s) {
			$(document).trigger('ajaxStop');

			var data = s.split(':');
			if (data[0] == 'error') {
				Boxy.warn(data[1]);
			} else if (data[0] === 'success') {
				Boxy.info(data[1], function () {
					Boxy.get($(".close")).hideAndUnload();
				});
			}
		}

		$("#complete").click(function () {
			if (confirm("Are you sure you want to complete this request?")) {
				var code = $(this).data("id");
				$.ajax({
					url: '/api/item.php',
					type: 'POST',
					data: {action: 'complete', code: code},
					complete: function (status) {
						if (status.responseText == 'true') {
							Boxy.info("Request completed successfully");
							Boxy.get($(".close")).hideAndUnload();
						}
					}
				});
			}
		})

	</script>

