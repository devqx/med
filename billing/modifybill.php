<?php
@session_start();

$patient = $_POST['pid'];
$method = $_POST['method'];
$transaction_type = $_POST['type'];
$dueDate = @$_POST['due_date'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PaymentMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
$bills = new Bills();
//validate patient? not necessary!
if ($transaction_type == "payment") {
	if (!is_numeric(parseNumber($_POST['amount']))) {
		exit("error:Invalid amount!--" . $_POST['amount']);
	}
	//insert a new payment
	$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], false, null, null);
	$bill = new Bill();
	$bill->setClinic(new Clinic(1));
	$bill->setReceiver((new StaffDirectory($_SESSION['staffID'])));
	$bill->setPatient($pat);
	$bill->setDescription(ucwords((new PaymentMethodDAO())->get($method)->getName()) . ' payment made by patient');
	$bill->setTransactionType('debit');
	$bill->setDueDate(date(MainConfig::$mysqlDateTimeFormat, strtotime($dueDate)));
	$bill->setPaymentReference($_POST['payment_reference']);
	$bill->setPaymentMethod(new PaymentMethod($_POST['method']));
	$bill->setItem(null);
	$bill->setSource((new BillSourceDAO())->findSourceById(23));
	$bill->setAmount(0 - parseNumber($_POST['amount']));
	
	$bill->setBilledTo(new InsuranceScheme($pat->getScheme()->getId()));
	//$billed_to = $pat->getScheme()->getId();
	//$billed_to = 1;// if the payment is coming from a patient, then it's most likely to be for a "self-charged" bill
	if ($pat->getScheme()->getType() != "self") {
		$bill->setBilledTo(new InsuranceScheme(1));//scheme with id 1 must be a `self-pay' scheme
	}
	$b = (new BillDAO())->addBill($bill, 1, null, null);
	ob_end_clean();
	
	if ($b !== null) {
		exit("success:" . $b->getId());
	} else {
		exit("error:Failed to save payment");
	}
}
if ($transaction_type == "voucher") {
	$voucher = (new VoucherDAO())->getByCode($_POST['voucher_code']);
	$voucherDesc = $voucher->getBatch()->getDescription();
	
	$voucher_method = (new PaymentMethodDAO())->get($method)->getType();
	
	if ($voucher_method === "voucher") {
		$voucher_method = "payment";//other types are not invalid
	}
	if ($voucher === null) {
		exit("error:Invalid voucher");
	}
	if ($voucher->getUsedDate() !== null) {
		exit("error:Used voucher");
	}
	if (strtotime($voucher->getBatch()->getExpirationDate()) < strtotime(date("Y-m-d"))) {
		exit("error:Expired voucher");
	}
	
	//exit("error:".$voucher_method."/".$voucher->getBatch()->getType());
	
	if ($voucher->getBatch()->getType() !== $voucher_method) {
		exit("error:Voucher Type mismatch");
	}
	
	//continue to insert a new voucher-based transaction
	$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], false, null, null);
	$bill = new Bill();
	$bill->setClinic(new Clinic(1));
	$bill->setReceiver((new StaffDirectory($_SESSION['staffID'])));
	$bill->setPatient($pat);
	$bill->setDescription(ucwords((new PaymentMethodDAO())->get($method)->getName()) . ' for patient ('.$voucherDesc.')');
	$bill->setTransactionType($voucher_method === "payment" ? "debit" : $voucher_method);
	//voucher object
	$bill->setVoucher($voucher);
	$bill->setPaymentReference(null);
	$bill->setPaymentMethod(new PaymentMethod($_POST['method']));
	$bill->setItem(null);
	$bill->setSource((new BillSourceDAO())->findSourceById(23));
	
	$voucher_value = $voucher->getBatch()->getAmount();
	$amount = ($voucher_method != "refund") ? 0 - $voucher_value : $voucher_value;
	$bill->setAmount($amount);
	
	$bill->setBilledTo(new InsuranceScheme($pat->getScheme()->getId()));
	//$billed_to = $pat->getScheme()->getId();
	//$billed_to = 1;// if the payment is coming from a patient, then it's most likely to be for a "self-charged" bill
	if ($pat->getScheme()->getType() != "self") {
		$bill->setBilledTo(new InsuranceScheme(1));//scheme with id 1 must be a `self-pay' scheme
	}
	$b = (new BillDAO())->addBill($bill, 1, null, null);
	ob_end_clean();
	
	if ($b !== null) {
		exit("success:" . $b->getId() . ":Voucher of NGN" . $voucher->getBatch()->getAmount() . " given");
	} else {
		exit("error:Failed to save transaction");
	}
} else if ($transaction_type == "discount") {
	if (!is_numeric($_POST['amount']) || $_POST['amount'] <= 0) {
		exit("error:Invalid amount!--" . $_POST['amount']);
	}
	//  then insert a new payment as discount
	$pat = (new PatientDemographDAO())->getPatient($_POST['pid'], false, null, null);
	$bill = new Bill();
	$bill->setClinic(new Clinic(1));
	$bill->setReceiver((new StaffDirectory($_SESSION['staffID'])));
	$bill->setPatient($pat);
	$bill->setDescription("Discount on bill items ");
	$bill->setTransactionType('discount');
	$bill->setPaymentReference(null);
	$bill->setPaymentMethod(null);
	$bill->setItem(null);
	$bill->setSource((new BillSourceDAO())->findSourceById(23));
	$bill->setAmount(0 - $_POST['amount']);
	
	$bill->setBilledTo(new InsuranceScheme($pat->getScheme()->getId()));
	//$billed_to = $pat->getScheme()->getId();
	//$billed_to = 1;// if the payment is coming from a patient, then it's most likely to be for a "self-charged" bill
	if ($pat->getScheme()->getType() != "self") {
		$bill->setBilledTo(new InsuranceScheme(1));//scheme with id 1 must be a `self-pay' scheme
	}
	$b = (new BillDAO())->addBill($bill, 1, null, null);
	ob_end_clean();
	
	if ($b !== null) {
		exit("success");
	} else {
		exit("error:Failed to save discount");
	}
}
exit("error:Error Transaction unknown");