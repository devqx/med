<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 11/23/15
 * Time: 1:53 PM
 */
@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Invoice.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InvoiceLine.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bill.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';

if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'invpay') {
	$bill_lines = $_REQUEST['bills'];
	//create the invoice
	$invoice_ = new Invoice();

	$lines = array();
	foreach (explode(",", $_REQUEST['bills']) as $bill) {
		$line = new InvoiceLine();
		$line->setBill(new Bill($bill));

		$lines[] = $line;
	}
	$invoice_->setLines($lines);
	if (isset($_GET['mode']) && $_GET['mode'] == "patient") {
		$invoice_->setPatient(new PatientDemograph($_GET['pid']));
	} else {
		$invoice_->setPatient(null);
	}

	if (isset($_GET['mode']) && $_GET['mode'] == "insurance") {
		$invoice_->setScheme((new InsuranceScheme($_GET['sid'])));
	} else {
		$invoice_->setScheme(null);
	}

	$invoice_->setCashier(new StaffDirectory($_SESSION['staffID']));
	$invoice = (new InvoiceDAO())->create($invoice_);

	ob_end_clean();
	if ($invoice === null) {
		exit("err:Error creating Invoice");
	}
	exit('ok:' . $invoice->getId());
}