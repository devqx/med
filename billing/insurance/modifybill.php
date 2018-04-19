<?php
@session_start();
$scheme = $_POST['sid'];
$amount = $_POST['amount'];
$method = $_POST['method'];
$transaction_type = $_POST['type'];
$payment_reference = $_POST['payment_reference'];

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/PaymentMethod.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PaymentMethodDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/VoucherDAO.php';
$bills = new Bills();
if($transaction_type=="payment"){
    if(!is_numeric($_POST['amount'])){
        exit("error:Invalid amount!--".$_POST['amount']);
    }
    //insert a new payment
    $bill = new Bill();
    $bill->setClinic(new Clinic(1));
    $bill->setReceiver( (new StaffDirectory($_SESSION['staffID'])) );
    $bill->setDescription(ucwords((new PaymentMethodDAO())->get($method)->getName()).' payment');
    $bill->setTransactionType('debit');
    $bill->setPaymentReference($_POST['payment_reference']);
    $bill->setPaymentMethod(new PaymentMethod($_POST['method']));
    $bill->setItem(NULL);
    $bill->setSource( (new BillSourceDAO())->findSourceById(23) );
    $bill->setAmount(0-$_POST['amount']);

    $bill->setBilledTo( new InsuranceScheme($scheme) );

    $b = (new BillDAO())->addBill($bill, 1, NULL, NULL);
    if($b!==NULL){
        ob_clean();
        exit("success:".$b->getId());
    }else {
        exit("error:Failed to save payment");
    }
}else if($transaction_type=="voucher"){
    $voucher = (new VoucherDAO())->getByCode($_POST['voucher_code']);

    $voucher_method = (new PaymentMethodDAO())->get($method)->getType();

    if($voucher_method === "voucher"){
        $voucher_method = "payment";//other types are not invalid
    }
    if($voucher === NULL){
        exit("error:Invalid voucher");
    }
    if($voucher->getUsedDate() !== NULL){
        exit("error:Used voucher");
    }
    if(strtotime($voucher->getBatch()->getExpirationDate()) < strtotime(date("Y-m-d"))){
        exit("error:Expired voucher");
    }

    if($voucher->getBatch()->getType() !== $voucher_method){
        exit("error:Voucher Type mismatch");
    }

    //continue to insert a new voucher-based transaction
    $bill = new Bill();
    $bill->setClinic(new Clinic(1));
    $bill->setReceiver( (new StaffDirectory($_SESSION['staffID'])) );
    $bill->setDescription(ucwords((new PaymentMethodDAO())->get($method)->getName()).' for HMO');
    $bill->setTransactionType($voucher_method === "payment" ? "debit": $voucher_method);
    $bill->setPaymentReference(NULL);
    $bill->setVoucher( $voucher );
    $bill->setPaymentMethod(new PaymentMethod($_POST['method']));
    $bill->setItem(NULL);
    $bill->setSource( (new BillSourceDAO())->findSourceById(23) );
    $voucher_value = $voucher->getBatch()->getAmount();
    $amount = ($voucher_method != "refund") ? 0-$voucher_value: $voucher_value;
    $bill->setAmount( $amount );

    $bill->setBilledTo( new InsuranceScheme($scheme) );

    $b = (new BillDAO())->addBill($bill, 1, NULL, NULL);
    ob_end_clean();

    if($b!==NULL){
        exit("success:".$b->getId().":Voucher of NGN".$voucher->getBatch()->getAmount()." given");
    }else {
        exit("error:Failed to save transaction");
    }
}
else if($transaction_type=="discount"){
    if(!is_numeric($_POST['amount']) || $_POST['amount'] <=0){
        exit( "error:Invalid amount!--".$_POST['amount']);
    }
    //  then insert a new payment as discount
    $bill = new Bill();
    $bill->setClinic(new Clinic(1));
    $bill->setReceiver( (new StaffDirectory($_SESSION['staffID'])) );
    $bill->setPatient( NULL );
    $bill->setDescription("Discount on bill items ");
    $bill->setTransactionType('discount');
    $bill->setPaymentReference(NULL);
    $bill->setPaymentMethod(NULL);
    $bill->setItem(NULL);
    $bill->setSource( (new BillSourceDAO())->findSourceById(23) );
    $bill->setAmount(0-$_POST['amount']);

    $bill->setBilledTo( new InsuranceScheme($scheme) );

    $b = (new BillDAO())->addBill($bill, 1, NULL, NULL);
    if($b!==NULL){
        exit("success");
    }else {
        exit("error:Failed to save discount");
    }
}
exit("error:Error Transaction unknown");
exit;