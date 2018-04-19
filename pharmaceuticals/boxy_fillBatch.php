<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/27/14
 * Time: 12:14 PM
 */
@session_start();
include_once "../protect.php";
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$_REQUEST['code'] = (isset($_REQUEST['pCode']) ? $_REQUEST['pCode'] : $_REQUEST['code']);
$_REQUEST['pCode'] = (isset($_REQUEST['code']) ? $_REQUEST['code'] : $_REQUEST['pCode']);
// we have mixed up things a little. some requests contain `pCode`, some just `code`

$press = (new PrescriptionDAO())->getPrescriptionByCode($_REQUEST['pCode'], true);

$bills = new Bills();

$pat = (new PatientDemographDAO())->getPatient($press->getPatient()->getId(), false, null, null);

$_ = $bills->_getPatientPaymentsTotals($pat->getId()) + $bills->_getPatientCreditTotals($pat->getId());
$creditLimit = (new CreditLimitDAO())->getPatientLimit($pat->getId())->getAmount();
$selfOwe = $_ > 0 ? $_ : 0;

if ($_POST) {
	$valid = array();
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientQueue.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CreditLimitDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
	
	if ($_POST['action'] == "fill") {
		
		$pdo = null;
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = (new MyDBConnector())->getPDO();
			$pdo->beginTransaction();
		} catch (PDOException $e) {
			exit('error: Database connectivity error (' . $e->getMessage() . ')');
		}

		foreach ($_POST['drug_id'] as $index => $drug) {
			// allow 0 quantity to be filled? yes. we used `BEFORE UPDATE` trigger to re-open the 0 value ones
			if ($_POST['quantity'][$index] >= 0 && !empty($drug) && filter_var($_POST['pres_id'][$index], FILTER_VALIDATE_INT)) {
				$valid[] = $drug;
			}
		}
		if (sizeof($valid) != sizeof($_POST['drug_id'])) {
			$pdo->rollBack();
			exit("error: Post Data is incomplete");
		}

		$get_active_enrollment = (new AntenatalEnrollmentDAO())->getActiveInstance($_REQUEST['pid'], false, $pdo);


		//done validating the separate elements, now submit
		foreach ($_POST['drug_id'] as $index => $drugId) {
			if (!$pdo->inTransaction()) {
				exit("error:System error 1a" . $index);
			}
			$staff = new StaffDirectory($_SESSION['staffID']);
			
			$price = 0;
			$desc = '';
			$newdesc = '';
			$pat = (new PatientDemographDAO())->getPatient($_REQUEST['pid'], false, $pdo);
			$qty = $_POST['quantity'][$index];
			$batch_id = $_POST['batch_id'][$index];
			$comment = $_POST['comment'][$index];
			$dao = new DrugDAO();
			$drug = $dao->getDrug($_POST['drug_id'][$index], true, $pdo);
			$pres = (new PrescriptionDataDAO())->getPrescriptionDatum($_POST['pres_id'][$index], false, $pdo);
			$ip = (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getInPatient();
			$ipId = ($ip) ? $ip->getId() : null;
			$pres->setDrug($drug);
			$pres->setQuantity($qty);
			$pres->setRefillDate(isset($_POST['refill_date'][$_POST['pres_id'][$index]]) ? $_POST['refill_date'][$_POST['pres_id'][$index]] : null);
			$pres->setStatus("filled");
			$pres->setFilledBy($staff);
			$pres->setComment($comment);
			$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($drug->getCode(), $_POST['pid'], true, $pdo);
			if ($price === null) {
				//TODO if null return error and prompt the user to supply the price for the item
				$price = 0;
			}
			$desc .= $qty . ' ' . pluralize($drug->getStockUOM(), $qty) . ' of ' . $drug->getGeneric()->getName() . ' (' . $drug->getName() . ') at ' . number_format((float)$price, 2) . ' each';
			
			$batch = (new DrugBatchDAO())->getBatch($batch_id, $pdo);
			if (empty($batch_id)) {
				$pdo->rollBack();
				exit("error:Sorry, but no batch was selected");
			}
			$pres->setBatch($batch);
			if ($qty > $batch->getQuantity()) {
				$pdo->rollBack();
				exit("error:Not enough quantity in selected batch for " . $drug->getName() . ". <br> Quantity remaining; " . $batch->getQuantity());
			}
			
			if (strtotime($batch->getExpirationDate()) < strtotime(date("Y-m-d"))) {
				$pdo->rollBack();
				exit("error:The selected batch for " . $drug->getName() . " has expired.");
			}
			
			if (!$pdo->inTransaction()) {
				exit("error:System error 1");
			}
			
			// we need to bill here
			// so that we simplify calls to methods
			$bil = new Bill();
			$bil->setPatient($pat);
			$bil->setInPatient($ip); //?
			$bil->setDescription($desc);
			$bil->setItem($drug);
			$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
			if (!$pdo->inTransaction()) {
				exit("error:System error 3");
			}
			$bil->setTransactionType("credit");
			$bil->setAmount($price * $qty);
			$bil->setDiscounted(null);
			$bil->setDiscountedBy(null);
			$bil->setClinic($staff->getClinic());
			$bil->setBilledTo($pat->getScheme());
			
			//Cost center is based on the requested service center
			$bil->setCostCentre((new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre() ? (new PrescriptionDAO())->getPrescriptionByCode($pres->getCode(), false, $pdo)->getServiceCentre()->getCostCentre() : null);
			if (!$pdo->inTransaction()) {
				exit("error:System error 4");
			}
			$bill = null;
			
			$bill = (new BillDAO())->addBill($bil, $qty, $pdo, $ipId);
			if (!$pdo->inTransaction()) {
				exit("error:System error 5");
			}
			
			$pres->setBill($bill);
			$pres_ = (new PrescriptionDataDAO())->fillPrescription($pres, $pdo);
			if (!$pdo->inTransaction()) {
				exit("error:System error 2");
			}
			
			if ($pres_) {
				
				//$pdo->commit();
			} else {
				$pdo->rollBack();
				exit("error:Unable to fill the prescription");
			}
			if ($bill === null) {
				$pdo->rollBack();
				//TODO un-fill the prescription
				exit("error:Unable to bill the prescription");
			}
		}
		if ($pdo->inTransaction()) {
			$pdo->commit();
		}
		
		exit("ok");
	} else {
		$pdo_ = null;
		
		try {
			include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo_ = (new MyDBConnector())->getPDO();
			$pdo_->beginTransaction();
		} catch (PDOException $e) {
			exit('ERROR: Database connectivity error (' . $e->getMessage() . ')');
		}
		
		$press = (new PrescriptionDAO())->getPrescriptionByCode($_REQUEST['code'], false, $pdo_);
		if ($selfOwe - $creditLimit > 0) {
			exit("error:Patient has an outstanding balance");
		}
		$result_ = [];
		foreach ($press->getData() as $p) {
			$staff = new StaffDirectory($_SESSION['staffID']);
			
			$p->setCompletedBy($staff);
			$result_[] = (new PrescriptionDataDAO())->completePrescription($p, $pdo_);
		}
		if (in_array(true, $result_)) {
			$pdo_->commit();
			exit("ok2");
		} else {
			$pdo_->rollBack();
			exit("error:Complete failed");
		}
	}
}
$total = 0;
?>
<div style="width: 1050px;">
	<table class="table">
		<tbody>
		<tr>
			<td rowspan="3" style="width:100px">
				<img style="height:100px" src="<?= $press->getPatient()->getPassportPath() ?>">
			</td>
			<td><span class="fadedText ">ID:</span>
				<a href="/patient_profile.php?id=<?= $press->getPatient()->getId() ?>" target="_blank"><?= $press->getPatient()->getId() ?></a>
			</td>
			<td>
				<span class="fadedText ">Date of Birth:</span> <?= date("jS M, Y", strtotime($press->getPatient()->getDateOfBirth())) ?>
			</td>
		</tr>
		<tr>
			<td>
				<span class="fadedText ">Name:</span> <?= $press->getPatient()->getFullName() ?>
			</td>
			<td>
				<span class="fadedText ">Insurance:</span> <?= (new PatientDemographDAO())->getPatient($press->getPatient()->getId(), false)->getScheme()->getName() ?>
			</td>
		</tr>
		<tr>
			<td>
				<span class="fadedText ">Sex:</span> <?= ucwords($press->getPatient()->getSex()) ?>
			</td>
			<td><span class="fadedText ">Last Weight:</span>
				<?php
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
				$in_patient_id = (isset($ip) && ($ip !== null)) ? $ip->getId() : null;
				$vitals = (new VitalSignDAO())->getPatientLastVitalSigns($press->getPatient()->getId(), $in_patient_id, false, ["Weight"]);
				?>
				<?php foreach ($vitals as $v) {/*$v=new VitalSign();*/ ?>
					<em class="fadedText"><?= $v->getValue() ? $v->getValue() . 'kg' : 'N/A' ?></em><?php } ?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div class="<?= ($selfOwe <= 0) ? "notify-bar" : "warning-bar" ?>">
					<span class="fadedText block">Outstanding Balance</span><?= $currency ?><span> <?= number_format($selfOwe, 2); ?></span>
				</div>
			</td>
		</tr>
		</tbody>
	</table>
	<div id="tab-container" class="tab-container">
		<ul>
			<li><a href="#prescriptions_instructions">Prescription Instructions</a></li>
			<li><a href="/patient_profile.php?id=<?= $press->getPatient()->getId() ?>&view=allergens" data-target="#allergies">Allergies</a></li>
		</ul>
		
		<div id="prescriptions_instructions">

			<div class="box">Prescription: <?= $press->getCode() ?>,
				Requested by <?= $press->getRequestedBy()->getFullname() ?>
				on <?= date("d M, Y H:i A", strtotime($press->getWhen())) ?>

				<div class="dropdown pull-right" >
					<button class="drop-btn dropdown-toggle" data-toggle="dropdown">Action <span class="caret"></span></button>
					<ul class="img dropdown-menu" role="menu" aria-labelledby="dLabel">
						<li>
							<a class="btn btn-small1" href="/pharmaceuticals/print.label.php?pcode=<?= $press->getCode() ?>" target="_blank" >Label</a>
						</li>
						<li>
							<a class="btn btn-small1" href="/pharmaceuticals/print_prescription2.php?pcode=<?= $press->getCode() ?>" target="_blank" title="Print this prescription">Print</a>
						</li>
						<li>
							<a class="printReceipt btn btn-small1" href="javascript:;" data-href="/pharmaceuticals/boxy.select_printer.php?pCode=<?= $press->getCode() ?>" title="Print  prescription packing  slip" ></i> Slip</a>
						</li>
					</ul>
				</div>

				</div>
			<div class="box">
				<span class="fadedText">Notes</span>: <br><?= $press->getNote() ?>
			</div>
			<form method="post" name="fillForm" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart:_begin, onComplete:_end})">
				<input type="hidden" name="code" value="<?= $press->getCode() ?>">
				<table class="table table-striped" id="presList">
					<thead>
					<tr class="ui-bar-d">
						<th width="92px"></th>
						<th>Prescription</th>
						<th>Status</th>
						<th>Drug/Generic</th>
						<th>Price</th>
						<th>Quantity</th>
						<th></th>
					</tr>
					</thead>
					<?php
					$need_to_fill = false;
					$need_to_complete = false;
					foreach ($press->getData() as $d) {
						//$d = new PrescriptionData();
						if ($d->getStatus() == "open") {
							$need_to_fill = true;
						}
						if ($d->getStatus() == "filled") {
							$need_to_complete = true;
						} ?>
						<tr>
							<td class="fadedText">
								<a class="_p_action btn btn-small1 pull-left-" href="javascript:void(0)" data-action="hide"
								   data-id="<?= $d->getId() ?>" title="Hide this line now"><i
										class="icon-external-link"></i></a>
								<?php if (in_array($d->getStatus(),["open", "filled"])) { ?>
								<a class="_p_action btn btn-small1 pull-left-" href="javascript:void(0)" data-action="cancel"
								   data-id="<?= $d->getId() ?>" title="Cancel this prescription">
										<i class="icon-trash"></i></a><?php } ?>
								
								<?php if (in_array($d->getStatus(),["open"])) { ?>
								<a class="_p_action btn btn-small1 pull-left-" href="javascript:void(0)" data-action="substitute"
								   data-id="<?= $d->getId() ?>" title="Substitute this line">
										<i class="icon-resize-small"></i></a><?php } ?>
							</td>
							<td>
								<?= $d->getDose() . " " . $d->getGeneric()->getForm() . (($d->getDose() != 1) ? 's' : '') . " of " . ($d->getDrug() != null ? '[' . $d->getDrug()->getName() . '] ' : '') . $d->getGeneric()->getName() . " (" . $d->getGeneric()->getWeight() . ") " . $d->getFrequency() ?>
								for <?= $d->getDuration() ?> days
								<label title="Click on the field to Add/Edit comment"><textarea <?= (!in_array($d->getStatus(), ["open"]) ? ' disabled' : '') ?> id="comment_id_<?= $d->getId() ?>" data-id="<?= $d->getId() ?>"  name="comment[]" readonly> <?= $d->getComment() ? $d->getComment() : ' ' ?></textarea></label>
							</td>
							<td nowrap="nowrap"><?= ucwords($d->getStatus()) ?> <?php if (in_array($d->getStatus(), ['filled', 'cancelled', 'completed', 'substituted'])) { ?>
								<a href="javascript:" title="<?= ($d->getFilledBy() != null) ? 'Filled By: ' . $d->getFilledBy()->getShortname() : '' ?> <?= ($d->getSubstitutedBy() != null) ? '<br>Substituted By: ' . $d->getSubstitutedBy()->getShortname() . ' <br>(***' . $d->getSubstitutionReason() . ')' : '' ?> <?= ($d->getCompletedBy() != null) ? '<br>Completed By: ' . $d->getCompletedBy()->getShortname() : '' ?> <?= ($d->getCancelledBy() != null) ? '<br>Cancelled By: ' . $d->getCancelledBy()->getShortname() . ' (***' . $d->getCancelNote() . ')' : '' ?>">
										Details</a><?php } ?>
                            </td>
							<td>
								<input type="hidden" <?= (!in_array($d->getStatus(), ['open']) ? ' disabled' : '') ?> name="pres_id[]" value="<?= $d->getId() ?>"> <?= ($d->getDrug() != null ? $d->getDrug()->getName() . '<input type="hidden" name="drug_id[]" value="' . $d->getDrug()->getId() . '" ' . (in_array($d->getStatus(), ['filled', 'cancelled', 'history', 'substituted']) ? ' disabled="disabled"' : '') . '>' : '<input type="hidden" data-generic="' . $d->getGeneric()->getId() . '" id="drug_id_' . $d->getGeneric()->getId() . '" name="drug_id[]" ' . (in_array($d->getStatus(), ["cancelled","history", "substituted"]) ? ' disabled' : '') . '>') ?>
							</td>
							<td>
								<label><input class="amount" style="width: 75px" disabled type="number" name="price[]" id="price_<?= $d->getId() ?>" value="<?= ($d->getDrug() != null ? ((new InsuranceItemsCostDAO())->getItemPriceByCode($d->getDrug()->getCode(), $_GET['pid'])) : '0') ?>"></label>
							</td>
							<td>
								<label><select<?= (!in_array($d->getStatus(), ["open"]) ? ' disabled' : '') ?> id="batch_id_<?= $d->getId() ?>" data-id="<?= $d->getId() ?>" name="batch_id[]" data-placeholder="Source Batch...">
										<option></option>
										<?php if ($d->getDrug() != null) { ?>
											<?php foreach ($d->getDrug()->getBatches() as $batch) { ?>
												<?php if (($batch->getServiceCentre() != null) && $press->getServiceCentre()->getId() == $batch->getServiceCentre()->getId()) { ?>
													<?php if ($batch->getQuantity() > 0 && strtotime($batch->getExpirationDate()) > time() && $d->getStatus() == "open") { ?>
														<option value="<?= $batch->getId() ?>" data-quantity="<?= $batch->getQuantity() ?>" data-expiry="<?= strtotime($batch->getExpirationDate()) ?>"><?= $batch->getName() ?></option>
													<?php } else if ($d->getStatus() != "open") { ?>
														<option <?php if ($d->getBatch() !== null){
														        if ($d->getBatch()->getId() == $batch->getId()){ ?>selected<?php }
														} ?>><?= $batch->getName() ?></option>
													<?php } ?>
												<?php } ?>
											<?php } ?>
										<?php } ?>
									</select></label>
								<label><input style="margin-bottom:-2px;display: inline; width:90px" <?= (!in_array($d->getStatus(), ["open"]) ? ' disabled' : '') ?> id="quantity_<?= $d->getId() ?>" type="number" name="quantity[]" title="<?= $d->getStatus() ?>" value="<?= (in_array($d->getStatus(), ["filled", "completed"])) ? $d->getQuantity() : "0" ?>" min="0" max="0" required="required">
									<em class="stock_uom fadedText"><?= ($d->getDrug() != null) ? $d->getDrug()->getStockUOM() : '' ?></em></label>
								 <?php  if ($d->getRefillable() == true && $d->getRefillNumber() > 0) {  ?> <span><input title="Set Refill Date" type="text" class="refill_date" <?= !in_array($d->getStatus(), ['open']) ? 'disabled': '' ?> value="<?= date('Y-m-d', strtotime($d->getRefillDate()))?>" style="border-color: #e62c46 !important;
    box-shadow: 0px 0px 5px rgba(173, 33, 19, 0.8) !important;" name="refill_date[<?= $d->getId() ?>]" placeholder="Refill Date" ></span>  <?php }?>
							</td>
							<td>
								<label title="Force Availability of stock quantity">
									<input <?= (!in_array($d->getStatus(), ["open"]) ? ' disabled' : '') ?> type="checkbox" data-sid="<?= $press->getServiceCentre()->getId() ?>"   data-id="" name="force_quantity[<?= $d->getId() ?>]" onclick="newBatch()">
								</label></td>
						</tr>
					<?php } ?>
					<tr>
						<td colspan="5">TOTAL PRICE:</td>
						<td id="console">0.00</td>
						<td></td>
					</tr>
				</table>
				<div class="btn-block">
					<?php if (!$press->getExternal()) { ?>
						<?php if ($need_to_fill) { ?>
							<input type="hidden" name="action" value="fill">
							<button type="submit" class="btn">Fill</button>
						<?php } else if ($need_to_complete) { ?>
							<input type="hidden" name="action" value="complete">
							<button class="btn" type="submit" <?= ($selfOwe - $creditLimit > 0 ? ' disabled="disabled"' : "") ?>>
								Complete
							</button>
						
						<?php } ?>
					<?php } else { ?>
						<span class="fadedText"><i class="fa fa-warning"></i> Prescription is External</span>
					<?php } ?>

					<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
						Cancel
					</button>
				</div>
				<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
			</form>

		</div>
		<div id="allergies"></div>
	</div>
</div>
<script type="text/javascript">
	var selEl;
	function newBatch() {
		$('input[name^="force_quantity"]').on('change', function () {
			var $id = $(this).data("id");
			var $sid = $(this).data("sid");
			if($(this).prop("checked")){
				Boxy.load('new_batch.php?d_id='+$id+'&s_id='+$sid, function (s) {
				})
			}
		})
	}
	
	$("#tab-container").easytabs({
		animate: false,
		tabsClass: "nav nav-tabs",
		tabClass: "tabClass",
		updateHash: false,
		cache: false
	});
	//$("#tab-container").easytabs('select', '#prescriptions_instructions');
	
	var total = 0;
	var drugData = [];
	updateTotal();
	$('input[name="quantity[]"]').on('keyup change', function () {
		updateTotal();
	});
	
	

	function saved(s){
		var data = s.split(":");
		if (data[0] === "error") {
			Boxy.alert(data[1]);
		}else{
			Boxy.get($(".close")).hideAndUnload();
			addToBatchOption(s);
		}

	}
	
	function addToBatchOption(data) {
		var obj = JSON.parse(data);
			if (new Date(obj.expirationDate) > new Date() && obj.quantity > 0) {
				$('<option value="' + obj.id + '" data-quantity="' + obj.quantity + '" data-expiry="' + obj.expirationDate + '">' + obj.name + '</option>').appendTo(selEl);
			}

	}
	
	
	$('textarea[name="comment[]"]').on('click', function (e) {
		 var obj = $(this);
		 if(obj.attr('readonly')){
			 Boxy.ask("Do you want to Add/Edit this comment?", ['Yes', 'No'], function (choice) {
				 if(choice == "Yes"){
					 obj.removeAttr('readonly');
				 }
			 })
		 }
	})

	$('[name="batch_id[]"]').select2().change(function (data) {
		var $id = $(this).data('id');
		if (data.added !== null) {
			var quantity = $(data.added.element).data("quantity");
			var expiry = $(data.added.element).data("expiry");
			//if not expired: else, set the max to be `0'
			var diff = (expiry - Math.round(new Date().getTime() / 1000));
			if (diff <= 0) {//batch has expired
				quantity = 0;
				$("#notify").notify("create", {text: '<img src="/img/check48.png"> The selected batch has expired. You cannot fill from it'}, {expires: 5000});
			}
			if (quantity === 0) {
				$("#notify").notify("create", {text: '<img src="/img/check48.png"> The selected batch has nothing in stock. You cannot fill from it'}, {expires: 5000});
			}
			//todo if `force availability` is enabled, do not enforce the max attribute
			$("#quantity_" + $id).attr({max: quantity});
			if (quantity > 0) {
				$("#quantity_" + $id).val(0);
			} else {
				$("#quantity_" + $id).val(0);
			}
		} else {
			//todo if `force availability` is enabled, do not enforce the max attribute
			$("#quantity_" + $id).val(0).attr({max: 0}).trigger('change');
		}
	});

	function updateTotal() {
		var tempTotal = 0;
		$('input[name="quantity[]"]').each(function () {
			tempTotal += $(this).val() * $(this).parents('td').prev().children().children().val();
		});
		$('#console').html('<?= $currency ?>' + $.number(tempTotal, 2));
	}

	function reInit(data, element) {
		//does not work when `element` is a `<select>`
		$(element).select2({
			placeholder: "select drug",
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
	$('input[data-generic]').select2({
		placeholder: "select drug",
		width: '100%',
		query: function (options) {
			var el = options.element;
			$.ajax({
				url: "/api/get_drugs.php?gid=" + $(el).data("generic"),
				dataType: "json",
				complete: function (e) {
					drugData = (e.responseJSON);
					$(el).select2("destroy");
					reInit(drugData, $(el));
					$(el).select2("open");
				}
			});
		}
	});
	var drug = {};
	var el;
	$('input[id*="drug_id_"]').change(function (evt) {
		el = this;
		selEl = "#" + $(this).parent().next().next().find('select').attr('id');
		var selEls =  $(this).parent().next().next().next().find('input');
		var fid = $(selEls).attr('data-id',$(this).val());
		$.ajax({
			url: "/api/get_batches.php?did=" + $(this).val(),
			dataType: "json",
			complete: function (data) {
				$(selEl).empty();
				for (var i = 0; i < data.responseJSON.length; i++) {
					if (new Date(data.responseJSON[i].expirationDate) > new Date() && data.responseJSON[i].quantity > 0) {
						$('<option value="' + data.responseJSON[i].id + '" data-quantity="' + data.responseJSON[i].quantity + '" data-expiry="' + data.responseJSON[i].expirationDate + '">' + data.responseJSON[i].name + '</option>').appendTo(selEl);
					}
				}
				//use the basePrice of the drug for now, since this applies mostly to self-pay patients
				$(el).parent('td').next().children().children('input').val($(el).select2("data").basePrice);
				$(el).parents('tr').find('td em.stock_uom').html($(el).select2("data").stockUOM);

				updateTotal();
			}
		});
		showInsuranceNotice('<?= $press->getPatient()->getId()?>', evt);
	});
	function _begin() {
	}
	function _end(s) {
		if (s === "ok" || s === "ok2") {
			Boxy.info("Success", function () {
			    Boxy.ask('Will you like to print the prescription packing slip?', ['Yes', 'No'], function (choice) {
                    if(choice === 'Yes'){
                        Boxy.load('/pharmaceuticals/boxy.select_printer.php?pCode=<?= $press->getCode() ?>');
                    }
                });
				Boxy.get($(".close")).hideAndUnload();
				//aTab(1);
			});
		} else {
			var data = s.split(":");
			Boxy.alert(data[1]);
		}
	}
	//$("#presList").tableScroll({height:300});

	//$('[name^="force_quantity"]:checkbox').iCheck({checkboxClass: 'icheckbox_square-blue'}).on('ifChanged', function (event) {
	//	$(event.currentTarget).trigger('change');
	//});
	//$('.refill_date').datetimepicker({
	//	timepicker: false,
	//	format: "Y-m-d",
	//	startDate: new Date()
	//});
	
	

</script>
