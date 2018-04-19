<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/23/16
 * Time: 5:28 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$schemes = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$bill = (new BillDAO())->getBill($_GET['id'], TRUE);

//Mark original transfer-credit line on insurance scheme to invisible.
//if part was written-off generate credit write-off amount to HMO and mark invisible
//Generate transfer-debit sum of 1 and 2 for insurance and mark invisible.
//Generate transfer-credit for sum 1 and 2 (which is equal to default price) for patient.

if ($_POST) {
	$pdo = (new MyDBConnector())->getPDO();
	$pdo->beginTransaction();
	$bill = (new BillDAO())->getBill(base64_decode($_POST['item_id']), TRUE, $pdo);
	$billCopy = (new BillDAO())->getBill(base64_decode($_POST['item_id']), TRUE, $pdo);
	$ipId = $bill->getInPatient() ? $bill->getInPatient()->getId() : null;
	$bill1 = $bill2 = null;
	// get the hospital price to apply
	if (is_null($bill->getItemCode()) || is_blank($bill->getItemCode())) {
		$pdo->rollBack();
		exit("error:Failed to determine item for rewrite action");
	}

	$writeOffPart = null;
	$parent = (new BillDAO())->getParentBill(base64_decode($_POST['item_id']), $pdo);
	$Parts = $parent ? (new BillDAO())->getRelatedBills($parent->bill_id, $pdo, false) : [];
	foreach ($Parts as $part) {
		if ($part->transaction_type == 'write-off') {
			$writeOffPart = $part;
			$bil3 = new Bill();
			$bil3->setPatient($bill->getPatient());
			$bil3->setDescription("Diff: " . getItem($bill->getItemCode(), $pdo)->getName());
			$bil3->setItem(getItem($bill->getItemCode(), $pdo));//$item
			$bil3->setSource($bill->getSource());
			$bil3->setTransactionType("credit");
			$bil3->setTransactionDate(date("Y-m-d H:i:s"));
			$bil3->setAmount(abs($part->amount)); // amount just like a credit
			$bil3->setDiscounted(null);
			$bil3->setDiscountedBy(null);
			$bil3->setClinic(new Clinic(1));
			$bil3->setBilledTo((new InsuranceScheme(1)));
			$bil3->setAuthCode(!is_blank($part->auth_code) ? $part->auth_code : null);
			$bil3->setReviewed(TRUE);
			$bil3->setParent($bill);
			$bil3->setCancelledOn(date('Y-m-d H:i:s'));
			$bil3->setCancelledBy(new StaffDirectory($_SESSION['staffID']));

			if ((new BillDAO())->addBill($bil3, $bill->getQuantity(), $pdo, $bill->getInPatient() ? $bill->getInPatient()->getId() : null) === null) {
				$pdo->rollBack();
				exit('error:Action has failed');
			}
			break;
		}
	}

	//generate transfer-debit to hmo, mark invisible, for insurance amount = $bill->getAmount()+writeOff
	$bil = new Bill();
	$bil->setPatient($bill->getPatient());
	$bil->setDescription("" . getItem($bill->getItemCode(), $pdo)->getName());
	$bil->setSource($bill->getSource());
	$bil->setItem(getItem($bill->getItemCode(), $pdo));//$item
	$bil->setTransactionType("transfer-debit");
	$bil->setTransactionDate(date("Y-m-d H:i:s"));
	$bil->setAmount((float) (0 - ($bill->getAmount() + ($writeOffPart ? abs($writeOffPart->amount) : 0))));
	$bil->setDiscounted(null);
	$bil->setDiscountedBy(null);
	$bil->setClinic(new Clinic(1));
	$bil->setBilledTo($bill->getBilledTo());
	$bil->setAuthCode(!is_blank($bill->getAuthCode()) ? $bill->getAuthCode() : null);
	$bil->setReviewed(!is_blank($bill->getAuthCode()) ? TRUE : FALSE);
	$bil->setParent($bill);
	$bil->setTransferred(TRUE);
	//$bil->setCancelledOn(date('Y-m-d H:i:s'));
	//$bil->setCancelledBy(new StaffDirectory($_SESSION['staffID']));
	$bil->add( $bill->getQuantity(), $bill->getInPatient() ? $bill->getInPatient()->getId() : null, $pdo );

	$itemAsDefault = (new InsuranceItemsCostDAO())->getInsuranedItemCostByCode($bill->getItemCode(), 1, TRUE, FALSE, $pdo);
	//$amount = $bill->getQuantity() * $itemAsDefault->{$bill->getPriceType()};
	$amount = $bil->getAmount();

	if ($bill->getTransactionType() == "transfer-credit") {
		$bill1 = $bill->setAmount(0 - abs($amount))->setTransactionType("transfer-debit")->setTransactionDate(date('Y-m-d H:i:s'))->add($bill->getQuantity(), $ipId, $pdo);
		$bill2 = $bill->setAmount(abs($amount))->setTransactionType("transfer-credit")->setBilledTo($bill->getBilledTo())->setTransactionDate(date('Y-m-d H:i:s'))->setTransferred(FALSE)->setBilledTo(new InsuranceScheme(1))->add($bill->getQuantity(), $ipId, $pdo);
	} else if ($bill->getTransactionType() == 'credit') {
		$bill1 = $bill->setAmount(0 - abs($amount))->setTransactionType("debit")->setTransactionDate(date('Y-m-d H:i:s'))->add($bill->getQuantity(), $ipId, $pdo);
		$bill2 = $bill->setAmount(abs($amount))->setTransactionType("credit")->setBilledTo($bill->getBilledTo())->setTransactionDate(date('Y-m-d H:i:s'))->setTransferred(FALSE)->setBilledTo(new InsuranceScheme(1))->add($bill->getQuantity(), $ipId, $pdo);
	}

	if ($bill1 === null || $bill2 === null || $billCopy->setCancelledOn(date('Y-m-d H:i:s'))->setCancelledBy(new StaffDirectory($_SESSION['staffID']))->update($pdo) === null) {
		$pdo->rollBack();
		exit("error:Action failed");
	}
	$pdo->commit();
	exit("success:Success");
}
?>

<section style="width: 500px">
	<div class="row-fluid">
		<label class="fadedText span4">Transaction Details: </label>
		<label class="span8"><?= $bill->getDescription() ?></label>
	</div>
	<div class="row-fluid">
		<label class="fadedText span4">Charged to </label>
		<label class="span8"><?= $bill->getPatient()->getFullname() ?></label>
	</div>
	<div class="row-fluid">
		<label class="fadedText span4">Date:</label>
		<label class="span8"><?= date(MainConfig::$dateTimeFormat, strtotime($bill->getTransactionDate())) ?></label>
	</div>

	<form action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {onStart: st$_, onComplete: st$__ })">
		<label class="hide">Payer
			<select name="payer_id" data-placeholder="Select Payer" title="Select Payer" required disabled>
				<option></option>
				<?php foreach ($schemes as $scheme) { ?>
					<option value="<?= $scheme->getId() ?>" <?= $scheme->getId() == $bill->getBilledTo()->getId() ? " selected " : "" ?>><?= $scheme->getName() ?></option>
				<?php } ?>
			</select></label>
		<input type="hidden" name="item_id" value="<?= base64_encode($_GET['id']) ?>">
		<div class="clear" style="margin-bottom: 20px;"></div>
		<div class="btn-block">
			<button type="submit" class="btn">OK</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>

	</form>
</section>
<script type="text/javascript">
	var st$_ = function () {
		$(document).trigger("ajaxSend");
	};
	var st$__ = function (s) {
		// console.log(s);
		$(document).trigger("ajaxStop");
		var data = s.split(":");
		if (data[0] == "success") {
			Boxy.info(data[1]);
			Boxy.get($(".close")).hideAndUnload();
		} else if (data[0] == "error") {
			Boxy.warn(data[1]);
		}
	}
</script>
