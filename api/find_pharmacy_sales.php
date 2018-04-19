<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 11/17/15
 * Time: 12:56 PM
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDataDAO.php');

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 100;
$totalSearch = 0;
$psReport = [];
if($date ===TRUE){
    $data = (new PrescriptionDataDAO())->getCompletedPrescriptionsByDateRange($page, $pageSize, $_REQUEST['pharmacy_id'], TRUE, $_REQUEST['from'], $_REQUEST['to']);
    $totalSearch = $data->total;
    $psReport = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Pharmacy Sales Requests</div>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>Prescription Date</th>
        <th>Filled Date</th>
        <th>Patient</th>
        <th>Drug</th>
        <th>Quantity</th>
        <th>Scheme</th>
        <th class="amount">Amount</th>
    </tr>
    </thead>
    <?php if(isset($psReport)){
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
        $iItem = new InsuranceItemsCostDAO();
        foreach ($psReport as $k=>$report) { ?>
            <tr>
                <td nowrap><?= date(MainConfig::$dateFormat, strtotime($report->when)) ?></td>
                <td><?= $report->patientName ?></td>
                <td><?= (is_null($report->drug_id))? $report->generic_name : $report->drug_name ?></td>
                <td><?= $report->quantity ?></td>
                <td><?= $report->scheme_name ?></td>
                <td class="amount"><?= !is_null($report->drug_id) ? $report->quantity * $iItem->getItemPriceByCode($report->drug_code, $report->patient_id, TRUE) : 'N/A' ?></td>
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
