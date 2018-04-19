<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/27/15
 * Time: 8:02 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';

$batch_id = (isset($_GET['batch_id']))? $_GET['batch_id']:'';
$vouchers = (new VoucherDAO())->getByBatch($batch_id);
//echo json_encode($vouchers);
$batch_vouchers=array();
if(sizeof($vouchers)>0) {
    foreach($vouchers as $key => $voucher_) {
        $voucher = array();
        $voucher['Code'] = $voucher_->getCode();
        $voucher['Amount'] = $voucher_->getBatch()->getAmount();
        $voucher['Type'] = ucwords($voucher_->getBatch()->getType());
        $voucher['Status'] = (($voucher_->getUsedDate() === NULL) ? 'VALID' : 'USED');
        $batch_vouchers[] = $voucher;
    }
}

if(isset($_REQUEST['ex_'])){
    require_once $_SERVER['DOCUMENT_ROOT']. '/classes/json2csv.class.php';
    $JSON2CSV = new JSON2CSVutil;
    $JSON2CSV->readJSON(json_encode($batch_vouchers));
    $JSON2CSV->flattenDL("BatchVouchers.csv");
    exit;
}