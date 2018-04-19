<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/30/15
 * Time: 11:03 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicalTaskDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientAntenatalUsages.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAntenatalUsagesDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackageItemsDAO.php';
	
	$pdo = null;
	try {
		include_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$pdo->beginTransaction();
	} catch (PDOException $e) {
		exit('error: Database connectivity error');
	}
	$taskData = (new ClinicalTaskDataDAO())->getTaskDatum($_POST['taskDataId'], true, $pdo);
	$pid = $taskData->getClinicalTask()->getPatient()->getId();
	$pat = (new PatientDemographDAO())->getPatient($pid, false, $pdo, null);
	$staff = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID'], true, $pdo);
	
	$drug = (isset($_POST['drug_id'])) ? (new DrugDAO())->getDrug($_POST['drug_id'], true, $pdo) : $taskData->getDrug();
	
	$price = (new InsuranceItemsCostDAO())->getItemPriceByCode($drug->getCode(), $pid, true, $pdo);
	
	$bil = new Bill();
	$bil->setPatient($pat);
	$bil->setDescription($_POST['quantity'] . " " . $drug->getGeneric()->getForm() . " of " . $drug->getName() . " [O/P.Task]");
	$bil->setItem($drug);
	$bil->setSource((new BillSourceDAO())->findSourceById(2, $pdo));
	$bil->setTransactionType("credit");
	$bil->setAmount($price * $_POST['quantity']);
	$bil->setDiscounted(null);
	$bil->setDiscountedBy(null);
	$bil->setClinic($staff->getClinic());
	$bil->setBilledTo($pat->getScheme());
	
	$get_active_enrollment = (new AntenatalEnrollmentDAO())->getActiveInstance($pid, false, $pdo);
	//if($get_active_enrollment != null && $get_active_enrollment->getPackage() != null){
	//    $qty = $_POST['quantity'];
	//
	//    $usages = new PatientAntenatalUsages();
	//    $usages->setType('Drug');
	//    $usages->setPatient($pat);
	//    $usages->setAntenatal($get_active_enrollment);
	//    $usages->setItem($drug->getId());
	//
	//    $get_usages = (new PatientAntenatalUsagesDAO())->getItemUsed($usages, $pdo);
	//    $number_used = count($get_usages);
	//    $item_details = (new AntenatalPackageItemsDAO())->getItemByPackage($usages->getAntenatal()->getPackage()->getId(), $drug->getId(), $pdo);
	//    if($item_details!=NULL) {
	//        $quantity_remaining = $item_details->getUsage() - $number_used;//12-4=8-5=3
	//        if ($quantity_remaining > 0) {
	//            $quantity_to_bill = $qty - $quantity_remaining;//5-8=-3 //4-3=1
	//            if($quantity_to_bill <= 0){
	//                for ($i = 0; $i < $qty; $i++) {
	//                    $bill = (new PatientAntenatalUsagesDAO())->addItem($usages, $pdo);
	//                }
	//            }
	//            else {
	//                for ($i = 0; $i < $quantity_remaining; $i++) {
	//                    $bill = (new PatientAntenatalUsagesDAO())->addItem($usages, $pdo);
	//                }
	//                $bil->setDescription($_POST['quantity'] . " ".$drug->getGeneric()->getForm(). " of ".$drug->getName()." [O/P.Task]");
	//                $bil->setAmount($price * $quantity_to_bill);
	//                $bill=(new BillDAO())->addBill($bil, $quantity_to_bill, $pdo, NULL);
	//            }
	//        }
	//        else {
	//            $bill=(new BillDAO())->addBill($bil, $quantity_remaining, $pdo, NULL);
	//        }
	//    }
	//    else {
	//        $bill=(new BillDAO())->addBill($bil, $qty, $pdo, NULL);
	//    }
	//}
	//else {
	//    $bill=(new BillDAO())->addBill($bil, $_POST['quantity'], $pdo, NULL);
	//}
	$bill = (new BillDAO())->addBill($bil, $_POST['quantity'], $pdo, null);
	//    $bill=(new BillDAO())->addBill($bil, 0, $pdo, NULL);
	
	$deplete = (new DrugBatchDAO())->depleteStock(new DrugBatch($_POST['batch_id']), $_POST['quantity'], $pdo);
	
	if ((isset($bill)) && $bill !== null && $deplete !== null && (new ClinicalTaskDataDAO())->setTaskBilled($_POST['taskDataId'], $pdo)) {
		$pdo->commit();
		exit("success:Bill added");
	}
	$pdo->rollBack();
	exit("error:Bill not added");
	
}

require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';
$work = (new ClinicalTaskDataDAO())->getTaskDatum($_GET['id']);
if ($work->getDrug() != null) {
	$batches = (new DrugBatchDAO())->getDrugBatches($work->getDrug());
}
?>


<section style="width: 650px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: begin, onComplete: complete})">
		<div class="well">
			<strong>TASK</strong>: Give <?= $work->getDose() ?> <?= ($work->getDrug() == null) ? $work->getGeneric()->getForm() : $work->getDrug()->getGeneric()->getForm() ?>
			of <?= ($work->getDrug() == null) ? $work->getGeneric()->getName() : $work->getDrug()->getName() ?> <?= ($work->getDrug() == null) ? $work->getGeneric()->getWeight() : $work->getDrug()->getGeneric()->getWeight() ?>
			every <?= convert_minutes_to_readable($work->getFrequency()) ?> for <?= $work->getTaskCount() ?> times.
		</div>

		<div><?= ($work->getDrug() == null) ? 'BRAND: ' . $work->getGeneric()->getName() : 'DRUG: ' . $work->getDrug()->getName() ?></div>
		<?php if ($work->getDrug() == null) {
			$drugs = (new DrugDAO())->getDrugsByGeneric($work->getGeneric()->getId());
			?>
			<div class="row-fluid">
				<div class="span12">
					<label>
						Drug:
						<select name="drug_id" id="drug_id" required="required" placeholder="Select a drug">
							<option value="" selected="selected"></option>
							<?php foreach ($drugs as $d) { ?>
								<option value="<?= $d->getId() ?>"><?= $d->getName() ?></option>
							<?php } ?>
						</select></label>
				</div>
			</div>
		<?php } ?>
		<div class="row-fluid">
			<div class="span5">
				<label>
					<select name="batch_id" id="batch_id" required="required" placeholder="Select a store's batch">
						<option value="" selected="selected"></option>
						<?php if (isset($batches)) {
							foreach ($batches as $b) {/*$b=new DrugBatch();*/
								if (strtotime($b->getExpirationDate()) > time() && $b->getQuantity() > 0) { ?>
									<option data-quantity="<?= $b->getQuantity() ?>" value="<?= $b->getId() ?>"><?= $b->getName() ?></option>
								<?php }
							}
						} ?>
					</select></label>
			</div>
			<div class="span5">
				<label>
					<input name="quantity" type="number" required="required" min="1">
					<span class="fadedText pull-left">Estimate quantity to use throughout the task period</span>
				</label>
			</div>
			<span class="span2"><?= ($work->getDrug() == null) ? $work->getGeneric()->getForm() : $work->getDrug()->getGeneric()->getForm() ?></span>
		</div>
		<div class="btn-block">
			<input type="hidden" name="taskDataId" value="<?= $_GET['id'] ?>">
			<button class="btn" type="submit">Add Charge</button>
			<button class="btn-link" onclick="Boxy.get(this).hideAndUnload()" type="button">Close</button>
		</div>
	</form>

</section>
<script type="text/javascript">
	function begin() {
	}
	function complete(s) {
		console.log(s);
		if (s.split(":")[0] === "error") {
			Boxy.alert(s.split(":")[1])
		} else if (s.split(":")[0] === "success") {
			Boxy.get($(".close")).hideAndUnload()
		} else {
			Boxy.alert("A server error occurred");
		}
	}

	$(document).ready(function () {
		$('select[name="batch_id"]').select2().change(function (e) {

			if (typeof e.added !== "undefined") {
				$("input[name='quantity']").attr("max", $(e.added.element[0]).data("quantity"));
			} else {
				$("input[name='quantity']").attr("max", 0);
			}

		}).trigger("change");

		var el;
		$('#drug_id').change(function () {
			el = this;
			$.ajax({
				url: "/api/get_batches.php?did=" + $(this).val(),
				dataType: "json",
				beforeSend: function () {
					$('#batch_id').html('<option value="" selected="selected"></option>');
				},
				complete: function (data) {
					if (data.responseJSON != null) {
						for (var i = 0; i < data.responseJSON.length; i++) {
							if (new Date(data.responseJSON[i].expirationDate) > new Date() && data.responseJSON[i].quantity > 0) {
								$('<option value="' + data.responseJSON[i].id + '" data-quantity="' + data.responseJSON[i].quantity + '" data-expiry="' + data.responseJSON[i].expirationDate + '">' + data.responseJSON[i].name + '</option>').appendTo('#batch_id');
							}
						}
					}
				}
			});
		});
	});
</script>
