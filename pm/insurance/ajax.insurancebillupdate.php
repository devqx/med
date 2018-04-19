<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/class.bills.php';
$bills=new Bills;
$sid=$_POST['sid'];
$bills->updateBillForScheme($sid);

//$invoiceStr = $bills->doInvoice($str,$pid);

echo "success"; ?>