<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/26/15
 * Time: 11:44 AM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.printer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';

function barcodeImg($text)
{
	//$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . "/barcode.php?text=$text";
	//$imdata = base64_encode(file_get_contents($url));
	return '<barcode encoding="CODE39">' . $text . '</barcode>';
	//return '<img src="data:image/png;base64,' . escape($imdata) . '" />';
}

$bills = new Bills();
$billID = (isset($_GET['billId'])) ? $_GET['billId'] : '';
$printer = (isset($_GET['printer'])) ? $_GET['printer'] : '';
$type = (isset($_GET['type'])) ? $_GET['type'] : '';
$mode = (isset($_GET['mode']) && $_GET['mode'] != '') ? $_GET['mode'] : null;
$reprint = (isset($_GET['reprint'])) ? $_GET['reprint'] : '';
$count = (isset($_REQUEST['count'])) ? $_REQUEST['count'] : '1';
$clinic = (new ClinicDAO())->getClinic(1, true);
$grouped = isset($_REQUEST['grouped']);
$msg = "";
if (date('m') == '12' && date('j') >= '25') {
	$msg .= '<br /><h4 align="center">Merry Christmas</h4>';
}
if (date('n-j') == '1-1') {
	$msg .= '<br /><h4 align="center">Happy New Year</h4>';
}

switch ($type) {
	case 'invpay':
		$ids = explode(',', $billID);
		$invoice_id = $ids[1];
		$invoice = (new InvoiceDAO())->get($invoice_id);
		$received_from = ($invoice->getPatient() !== null) ? $invoice->getPatient()->getFullname() . ' (' . $invoice->getPatient()->getId() . ')' : $invoice->getScheme()->getName();
		$bill_description = $bill_amount = $bill_lines = array();
		foreach ($invoice->getLines() as $line) {
			$bill_description[] = $line->getBill()->getDescription();
			$bill_amount[] = $line->getBill()->getAmount();
			$bill_lines[] = $line->getBill()->getId();
		}
		$bill_lines = implode(",", $bill_lines);
		$total_amount = array_sum($bill_amount);
		
		$id = ($mode === null) ? $invoice->getPatient()->getId() : $invoice->getScheme()->getId();
		$debits = number_format(-$bills->_getPatientPaymentsTotals($id, $mode), 2, '.', '');
		$credits = number_format($bills->_getPatientCreditTotals($id, $mode), 2, '.', '');
		
		$outstanding_total = ($credits - $debits);
		$outstanding_total = ($outstanding_total < 0) ? 0 : $outstanding_total;
		
		$billInfo = "<h4 align='center'>" . (htmlspecialchars(strtoupper(str_replace("&", "and", $clinic->getName())))) . "</h4><h5 align='center'>" . str_replace("&", "and", $clinic->getAddress()) . ",</h5><h5 align='center'>" . $clinic->getLGA()->getName() . ", " . $clinic->getLGA()->getState()->getName() . "</h5><h5 align='center'>Transaction Date: " . date("Y M, d", strtotime($invoice->getTime())) . "</h5><h5 align=\"center\">Invoice Receipt</h5><br />";
		$billInfo .= "<hr width='40' />PATIENT: " . $received_from . "<hr width='40' />";
		for ($i = 0; $i < count($bill_description); $i++) {
			$billInfo .= "<line width='40' line-ratio='0.7'><left>" . str_replace("&", "$", $bill_description[$i]) . "</left><right>" . number_format($bill_amount[$i], 2) . "</right></line>";
		}
		$billInfo .= "<hr width='40' /><line width='40' size='double-height'><left>TOTAL</left><right>" . number_format($total_amount, 2) . "</right></line><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>" . number_format($outstanding_total, 2, '.', '') . "</value></h5><h5>Cashier: " . $invoice->getCashier()->getFullname() . "</h5><br /><br />" . barcodeImg($invoice->getId()) . $msg;
		break;
	case 'ireceipt':
	case 'ireceipt2':
		$invoice = (new InvoiceDAO())->get($billID);
		$received_from = ($invoice->getPatient() !== null) ? $invoice->getPatient()->getFullname() . ' (' . $invoice->getPatient()->getId() . ')' : $invoice->getScheme()->getName();
		$bill_description = $bill_amount = $bill_lines = array();
		foreach ($invoice->getLines() as $line) {
			$bill_description[] = $line->getBill()->getDescription();
			$bill_amount[] = $line->getBill()->getAmount();
			$bill_lines[] = $line->getBill()->getId();
		}
		$bill_lines = implode(",", $bill_lines);
		$total_amount = array_sum($bill_amount);
		
		$id = ($mode === null) ? $invoice->getPatient()->getId() : $invoice->getScheme()->getId();
		$debits = number_format(-$bills->_getPatientPaymentsTotals($id, $mode), 2, '.', '');
		$credits = number_format($bills->_getPatientCreditTotals($id, $mode), 2, '.', '');
		
		$outstanding_total = ($credits - $debits);
		$outstanding_total = ($outstanding_total < 0) ? 0 : $outstanding_total;
		
		$billInfo = "<h4 align='center'>" . (htmlspecialchars(strtoupper(str_replace("&", "and", $clinic->getName())))) . "</h4><h5 align='center'>" . str_replace("&", "and", $clinic->getAddress()) . "</h5><h5 align='center'>" . $clinic->getLGA()->getName() . ", " . $clinic->getLGA()->getState()->getName() . "</h5><h5 align='center'>Transaction Date: " . date("Y M, d", strtotime($invoice->getTime())) . "</h5><h5 align=\"center\">Invoice Receipt</h5><br />";
		$billInfo .= '<hr width=\'40\' />PATIENT: ' . $received_from . '<hr width=\'40\' />';
		
		if (!$grouped) {
			for ($i = 0; $i < count($bill_description); $i++) {
				$billInfo .= "<line width='40' line-ratio='0.7'><left>" . str_replace("&", "$", $bill_description[$i]) . "</left><right>" . number_format($bill_amount[$i], 2) . "</right></line>";
			}
		} else {
			$pdo = (new MyDBConnector())->getPDO();
			$sql = "SELECT bs.name AS bill_source, SUM(b.amount) AS amount FROM bills b LEFT JOIN bills_source bs ON b.bill_source_id=bs.id LEFT JOIN insurance_schemes s ON s.id=b.billed_to WHERE b.bill_id IN (" . $bill_lines . ") AND b.transaction_type='credit' AND s.pay_type = 'self' AND b.cancelled_on IS NULL GROUP BY bs.name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$billInfo .= "<line width='40' line-ratio='0.7'><left>" . ucwords(str_replace('_', ' ', $row['bill_source'])) . "</left><right>" . number_format($row['amount'], 2) . "</right></line>";
			}
		}
		$billInfo .= "<hr width='40' /><line width='40' size='double-height'><left>TOTAL</left><right>" . number_format($total_amount, 2) . "</right></line><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>" . number_format($outstanding_total, 2, '.', '') . "</value></h5><h5>Cashier: " . $invoice->getCashier()->getFullname() . "</h5><br /><br /> " . barcodeImg($invoice->getId()) . $msg;
		
		break;
	default:
		$myBill = (new BillDAO())->getBill($billID, true);
		$id = ($mode === null) ? $myBill->getPatient()->getId() : $myBill->getBilledTo()->getId();
		$received_from = ($myBill->getPatient() !== null) ? $myBill->getPatient()->getFullname() . ' (' . $myBill->getPatient()->getId() . ')' : $myBill->getBilledTo()->getName();
		$debits = number_format(-$bills->_getPatientPaymentsTotals($id, $mode), 2, '.', '');
		$credits = number_format($bills->_getPatientCreditTotals($id, $mode), 2, '.', '');
		$outstanding_total = ($credits - $debits);
		$outstanding_total = ($outstanding_total < 0) ? 0 : $outstanding_total;
		$copy = "";
		if ($reprint == 'copy') {
			$copy .= '<br /><h5 align="center">COPY</h5>';
		}
		
		$payment_type = $myBill->getPaymentMethod()->getType();
		$ctype = $billInfo_voucher = "";
		$received = ($payment_type == 'refund') ? 'Refunded to' : ($payment_type!='discount'?'Received from':'For');
		switch ($payment_type) {
			case 'refund':
				$heading = '<h5 align="center">Refund Voucher</h5>';
				break;
			case 'discount':
				$heading = '<h5 align="center">Discount Voucher</h5>';
				break;
			case 'voucher':
				$heading = '<h5 align="center">Voucher</h5>';
				break;
			default:
				$heading = '';
		}
		$amount = abs($myBill->getAmount());
		if ($type == 'voucher') {
			$ctype .= "<h5 align='center'>Customer Copy</h5>";
			$billInfo_voucher = "<h5 align='center'>Official Copy</h5><h4 align='center'>" . (htmlspecialchars(strtoupper(str_replace("&", "and", $clinic->getName())))) . "</h4><h5 align='center'>" . str_replace("&", "and", $clinic->getAddress()) . ",</h5><h5 align='center'>" . $clinic->getLGA()->getName() . ", " . $clinic->getLGA()->getState()->getName() . "</h5><h5 align='center'>Transaction Date: " . date("Y M, d", strtotime($myBill->getTransactionDate())) . "</h5>" . $heading . "<br /><p>" . $received . ": " . $received_from . "</p><hr /><p>The sum of " . ucwords(convert_number_to_words($amount)) . " (<value value-symbol='NGN' value-symbol-position='after'>" . number_format($amount, 2, '.', '') . "</value>)</p><p>" . $myBill->getDescription() . "</p><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>" . number_format($outstanding_total, 2, '.', '') . "</value></h5><h5>Cashier: " . $myBill->getReceiver() . "</h5><br /><p>Name: ________________________</p><br /><p>Date: ________________________</p><br /><p>Sign: ________________________</p><br /><br /> " . barcodeImg($myBill->getId());
		}
		
		$billInfo = $ctype . "<h4 align='center'>" . (htmlspecialchars(strtoupper(str_replace("&", "and", $clinic->getName())))) . "</h4><h5 align='center'>" . str_replace("&", "and", $clinic->getAddress()) . ",</h5><h5 align='center'>" . $clinic->getLGA()->getName() . ", " . $clinic->getLGA()->getState()->getName() . "</h5><h5 align='center'>Transaction Date: " . date("Y M, d", strtotime($myBill->getTransactionDate())) . "</h5>" . $heading . "<br /><p>" . $received . ": " . $received_from . "</p><hr /><p>The sum of " . ucwords(convert_number_to_words($amount)) . " (<value value-symbol='NGN' value-symbol-position='after'>" . number_format($amount, 2, '.', '') . "</value>)</p><p>" . $myBill->getDescription() . "</p><h5>Outstanding Balance: <value value-symbol='NGN' value-symbol-position='after'>" . number_format($outstanding_total, 2, '.', '') . "</value></h5><h5>Cashier: " . $myBill->getReceiver() . "</h5><br /><br />" . barcodeImg($myBill->getId()) . $copy . $msg;
}


$billInfo_ = array();
for ($i = 0; $i < $count; $i++) {
	$billInfo_[] = $billInfo;
}
$printBill = implode("<partialcut />", $billInfo_);


if ($type == "voucher") {
	$printBill = $printBill . "<partialcut />" . $billInfo_voucher;
}
$billInfo = "<receipt width='47'>" . $printBill . "<cashdraw /></receipt>";
//error_log("-----------------$billInfo-----------------");
$url = 'http://' . $printer . '/receipt/printnow/';
//error_log($url);
$fields = array('q' => $billInfo);
$postData = '';
foreach ($fields as $k => $v) {
	$postData .= $k . '=' . $v . '&';
}
rtrim($postData, '&');
$ch = curl_init();

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
ob_end_clean();

//execute post
if (curl_exec($ch) === false) {
	error_log('Curl error: ' . curl_error($ch));
}
//close connection
curl_close($ch);