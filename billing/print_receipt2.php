<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/26/15
 * Time: 11:44 AM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.printer.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InvoiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';

$bills = new Bills();
$billID = (isset($_GET['billId']))? $_GET['billId'] : '';
$printer = (isset($_GET['printer']))? $_GET['printer'] : '';
$type = (isset($_GET['type']))? $_GET['type'] : '';
$mode = (isset($_GET['mode']) && $_GET['mode']!='')? $_GET['mode'] : NULL;
$reprint = (isset($_GET['reprint']))? $_GET['reprint'] : '';
$count = (isset($_REQUEST['count']))? $_REQUEST['count'] : '1';
$clinic = (new ClinicDAO())->getClinic(1, TRUE);
$phone = $clinic->getPhoneNo();
$msg= "";
if(date('m')=='12' && date('j')>='25'){ $msg .= '<br /><h4 align="center">Merry Christmas</h4>'; }
if(date('n-j')=='1-1'){$msg .= '<br /><h4 align="center">Happy New Year</h4>';}

switch($type){
    case 'invpay':
        $ids = explode(',', $billID);
        $invoice_id = $ids[1];
        $invoice = (new InvoiceDAO())->get($invoice_id);
        $patient = (new PatientDemographDAO())->getPatient($invoice->getPatient()->getId(), TRUE);

        $received_from = ($invoice->getPatient() !== NULL) ? $invoice->getPatient()->getFullname().' ('.$invoice->getPatient()->getId().')' : $invoice->getScheme()->getName();
        $bill_description = $bill_amount = $bill_lines = array();
        foreach ($invoice->getLines() as $line) {
            $bill_description[] = $line->getBill()->getDescription();
            $bill_amount[] = $line->getBill()->getAmount();
            $bill_lines[] = $line->getBill()->getId();
        }
        $bill_lines = implode(",", $bill_lines);
        $total_amount = array_sum($bill_amount);

        $id = ($mode===NULL)? $invoice->getPatient()->getId() : $invoice->getScheme()->getId();
        $debits = number_format(-$bills->_getPatientPaymentsTotals($id,$mode), 2,'.','');
        $credits = number_format($bills->_getPatientCreditTotals($id,$mode), 2,'.','');

        $outstanding_total = ($credits-$debits);
        $outstanding_total = ($outstanding_total < 0 )? 0 : $outstanding_total;

        $billInfo = "<h4 align='center'>".(htmlspecialchars(strtoupper(str_replace("&","and",$clinic->getName()))))."</h4><h5 align='center'>".$clinic->getAddress().",</h5><h5 align='center'>".$clinic->getLGA()->getName().", ".$clinic->getLGA()->getState()->getName()."</h5><h5>".$phone."</h5><h5 align='center'>Transaction Date: ".date("Y M, d", strtotime($invoice->getTime()))."</h5><h5 align=\"center\">Invoice Receipt</h5><br />";

        $billInfo .= "<h5>CLIENT: ".$patient->getFullname()."</h5><hr />";
        $billInfo .= "<h5>PHONE No: ".$patient->getPhoneNumber()."</h5><hr />";
        $billInfo .= "<h5>HMO: ".$patient->getInsurance()->getScheme()->getName()."</h5><hr />";
        $billInfo .= "<h5>REFERRER: ".($invoice->getLines()[0]->getBill()->getReferral()? $invoice->getLines()[0]->getBill()->getReferral()->getName(): 'N/A')."</h5><hr />";

        for($i=0; $i<count($bill_description); $i++) {
            $billInfo .= "<line line-ratio='0.7'><left>".$bill_description[$i]."</left><right>".$bill_amount[$i]."</right></line>";
        }
        $billInfo .= "<hr /><line size='double-height'><left>TOTAL</left><right>".$total_amount."</right></line><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>".number_format($outstanding_total, 2, '.','')."</value></h5><h5>Cashier: ".$invoice->getCashier()->getFullname()."</h5><br /><br /><barcode encoding='EAN13'>".$invoice->getId()."</barcode>".$msg;
        break;
    case 'ireceipt':
        $invoice = (new InvoiceDAO())->get($billID);
        $patient = (new PatientDemographDAO())->getPatient($invoice->getPatient()->getId(), TRUE);

        $received_from = ($invoice->getPatient() !== NULL) ? $invoice->getPatient()->getFullname().' ('.$invoice->getPatient()->getId().')' : $invoice->getScheme()->getName();
        $bill_description = $bill_amount = $bill_lines = array();
        foreach ($invoice->getLines() as $line) {
            $bill_description[] = $line->getBill()->getDescription();
            $bill_amount[] = $line->getBill()->getAmount();
            $bill_lines[] = $line->getBill()->getId();
        }
        $bill_lines = implode(",", $bill_lines);
        $total_amount = array_sum($bill_amount);

        $id = ($mode===NULL)? $invoice->getPatient()->getId() : $invoice->getScheme()->getId();
        $debits = number_format(-$bills->_getPatientPaymentsTotals($id,$mode), 2,'.','');
        $credits = number_format($bills->_getPatientCreditTotals($id,$mode), 2,'.','');

        $outstanding_total = ($credits-$debits);
        $outstanding_total = ($outstanding_total < 0 )? 0 : $outstanding_total;

        $billInfo = "<h4 align='center'>".(htmlspecialchars(strtoupper(str_replace("&","and",$clinic->getName()))))."</h4><h5 align='center'>".$clinic->getAddress().",</h5><h5 align='center'>".$clinic->getLGA()->getName().", ".$clinic->getLGA()->getState()->getName()."</h5><h5>".$phone."</h5><h5 align='center'>Transaction Date: ".date("Y M, d", strtotime($invoice->getTime()))."</h5><h5 align=\"center\">Invoice Receipt</h5><br />";

        $billInfo .= "<h5>CLIENT: ".$patient->getFullname()."</h5><hr />";
        $billInfo .= "<h5>PHONE No: ".$patient->getPhoneNumber()."</h5><hr />";
        $billInfo .= "<h5>HMO: ".$patient->getInsurance()->getScheme()->getName()."</h5><hr />";
        $billInfo .= "<h5>REFERRER: ".($invoice->getLines()[0]->getBill()->getReferral()? $invoice->getLines()[0]->getBill()->getReferral()->getName(): 'N/A')."</h5><hr />";

        for($i=0; $i<count($bill_description); $i++) {
            $billInfo .= "<line line-ratio='0.7'><left>".$bill_description[$i]."</left><right>".$bill_amount[$i]."</right></line>";
        }
        $billInfo .= "<hr /><line size='double-height'><left>TOTAL</left><right>".$total_amount."</right></line><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>".number_format($outstanding_total, 2, '.','')."</value></h5><h5>Cashier: ".$invoice->getCashier()->getFullname()."</h5><br /><br /><barcode encoding='EAN13'>".$invoice->getId()."</barcode>".$msg;
        break;
    default:
        $myBill = (new BillDAO())->getBill($billID, TRUE);
        $id = ($mode===NULL)? $myBill->getPatient()->getId() : $myBill->getBilledTo()->getId();
        $received_from = ($myBill->getPatient() !== NULL) ? $myBill->getPatient()->getFullname().' ('.$myBill->getPatient()->getId().')' : $myBill->getBilledTo()->getName();
        $debits = number_format(-$bills->_getPatientPaymentsTotals($id,$mode), 2,'.','');
        $credits = number_format($bills->_getPatientCreditTotals($id,$mode), 2,'.','');
        $outstanding_total = ($credits-$debits);
        $outstanding_total = ($outstanding_total < 0 )? 0 : $outstanding_total;
        $copy = "";
        if($reprint=='copy'){ $copy .= '<br /><h5 align="center">COPY</h5>'; }

        $payment_type = $myBill->getPaymentMethod()->getType();
        $ctype = $billInfo_voucher = "";
        $received = ($payment_type=='refund')? 'Refunded to' : 'Received from';
        switch($payment_type){
            case 'refund': $heading = '<h5 align="center">Refund Voucher</h5>'; break;
            case 'discount': $heading = '<h5 align="center">Discount Voucher</h5>'; break;
            case 'voucher': $heading = '<h5 align="center">Voucher</h5>'; break;
            default:
                $heading = '';
        }
        $amount = abs($myBill->getAmount());
        if($type=='voucher'){
            $ctype .= "<h5 align='center'>Customer Copy</h5>";
            $billInfo_voucher = "<h5 align='center'>Official Copy</h5><h4 align='center'>".(htmlspecialchars(strtoupper(str_replace("&","and",$clinic->getName()))))."</h4><h5 align='center'>".$clinic->getAddress().",</h5><h5 align='center'>".$clinic->getLGA()->getName().", ".$clinic->getLGA()->getState()->getName()."</h5><h5 align='center'>Transaction Date: ".date("Y M, d", strtotime($myBill->getTransactionDate()))."</h5>".$heading."<br /><p>".$received.": ".$received_from."</p><hr /><p>The sum of ".ucwords(convert_number_to_words($amount))." (<value value-symbol='NGN' value-symbol-position='after'>".number_format($amount, 2, '.','')."</value>)</p><p>".$myBill->getDescription()."</p><h5>Outstanding Balance:  <value value-symbol='NGN' value-symbol-position='after'>".number_format($outstanding_total, 2, '.','')."</value></h5> <h5>Cashier: ".$myBill->getReceiver()."</h5><br /><p>Name: ________________________</p><br /><p>Date: ________________________</p><br /><p>Sign: ________________________</p><br /><br /><barcode encoding='EAN13'>".$myBill->getId()."</barcode>";
        }

        $billInfo = $ctype."<h4 align='center'>".(htmlspecialchars(strtoupper(str_replace("&","and",$clinic->getName()))))."</h4><h5 align='center'>".$clinic->getAddress().",</h5><h5 align='center'>".$clinic->getLGA()->getName().", ".$clinic->getLGA()->getState()->getName()."</h5><h5>".$phone."</h5><h5 align='center'>Transaction Date: ".date("Y M, d", strtotime($myBill->getTransactionDate()))."</h5>".$heading."<br /><p>".$received.": ".$received_from."</p><hr /><p>The sum of ".ucwords(convert_number_to_words($amount))." (<value value-symbol='NGN' value-symbol-position='after'>".number_format($amount, 2, '.','')."</value>)</p><p>".$myBill->getDescription()."</p><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>".number_format($outstanding_total, 2, '.','')."</value></h5><h5>Cashier: ".$myBill->getReceiver()."</h5><br /><br /><barcode encoding='EAN13'>".$myBill->getId()."</barcode>".$copy.$msg;
}

$billInfo_ = array();
for($i=0; $i<$count; $i++){
    $billInfo_[] = $billInfo;
}
$printBill = implode("<partialcut />", $billInfo_);

//echo $billInfo;
if($type == "voucher"){ $printBill = $printBill."<partialcut />".$billInfo_voucher; }
$billInfo = "<receipt>".$printBill."<cashdraw /></receipt>";

$url = 'http://'.$printer.'/receipt/printnow/';

$fields = array('q'=> $billInfo);
$postData = '';
foreach($fields as $k => $v) {
    $postData .= $k . '='.$v.'&';
}
rtrim($postData, '&');
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch,CURLOPT_URL, $url);
curl_setopt($ch,CURLOPT_POST, count($fields));
curl_setopt($ch,CURLOPT_POSTFIELDS, $postData);
ob_end_clean();

//execute post
if(curl_exec($ch) === false) {
    error_log('Curl error: ' . curl_error($ch));
}

//close connection
curl_close($ch);