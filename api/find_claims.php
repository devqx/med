<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 10/3/17
 * Time: 4:43 PM
 */
exit("errorrrr");
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClaimDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsurerDAO.php';
$currency = (new CurrencyDAO())->getDefault();

$insurance_id = (new InsuranceSchemeDAO())->getInsuranceSchemes();
$provider = (new InsurerDAO())->getInsurers(FALSE);

$date = ((isset($_REQUEST['from']) && $_REQUEST['from'] != '' && isset($_REQUEST['to']) && $_REQUEST['to'] != '') ? TRUE : FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 100;
$totalSearch = 0;

$bill_lines = array();

$claimsReport = array();
$bill_source = null;
//$line_ids = array();


if ($date === TRUE) {
    $data = (new ClaimDAO())->getClaimsReport($_REQUEST['from'], $_REQUEST['to'], @$_REQUEST['insurance_scheme_id'], @$_REQUEST['provider'], $page, $pageSize);
    $totalSearch = $data->total;
    $claimsReport = $data->data;
}

$return = [];

foreach ($claimsReport as $lines) {
    $line_ids = array_filter(explode(',', $lines->line_ids));
    foreach ($line_ids as $id) {
        $line = (new BillDAO())->getBill($id, true);
        $re = new stdClass();
        $re->claimId = $lines->id;
        $re->claimDate = $lines->create_date;
        $re->Diagnosis = $lines->diagnoses;
        if($line){
            $re->transaction_date = $line->getTransactionDate();
            $re->Description = $line->getDescription();
            $re->BillSource = $line->getSource()->getName();
            $re->Amount = $line->getAmount();
            $re->Code = $line->getAuthCode();
            $re->quantity = $line->getQuantity();
            $re->insurance = $line->getBilledTo()->getName();
            $re->Patient = $line->getPatient()->getFullName();
            $re->Phone = $line->getPatient()->getPhoneNumber();
            $re->cliniId = $line->getPatient()->getId();
        }
        $return[] = $re;
    }
} ?>
<table class="table table-striped table-hover no-footer">
    <thead>
    <tr>
        <th>Claim ID</th>
        <th>Claim Date</th>
        <th>Hospital ID</th>
        <th>Patient</th>
        <th>Phone Number</th>
        <th>Scheme Name</th>
        <th>Enrolle ID</th>
        <th>Transaction Date</th>
        <th>Service</th>
        <th>Description</th>
        <th>Diagnosis</th>
        <th>PA Code</th>
        <th>Quantity</th>
        <th>Amount (<?= $currency ?>)</th>
    </tr>
    </thead>
    <?php if (isset($return) && sizeof($return) > 0) {
        foreach ($return as $report) {?>
            <tr>
                <td><?= $report->claimId ?></td>
                <td nowrap><?= date('M jS, Y', strtotime($report->claimDate)) ?></td>
                <td><?= $report->cliniId ?></td>
                <td><?= $report->Patient ?></td>
                <td><?= $report->Phone ?></td>
                <td><?= $report->insurance ?></td>
                <td><?= $report->EnroleeNumber ?></td>
                <td nowrap><?= date('M jS, Y', strtotime($report->transaction_date)) ?></td>
                <td><?= $report->BillSource ?></td>
                <td><?= ucwords($report->Description) ?></td>
                <td><?= $report->Diagnosis ?></td>
                <td><?= $report->Code ?></td>
                <td><?= $report->quantity ?></td>
                <td class="amount"><?= number_format(abs($report->Amount), 2) ?></td>
            </tr>
        <?php }
    } ?>
</table>
<div class="list1 dataTables_wrapper no-footer">
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
        <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
    </div>
</div>