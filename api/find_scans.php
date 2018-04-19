<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/20/15
 * Time: 2:28 PM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$imagingReport = [];
if($date ===TRUE){
    $data = (new PatientScanDAO())->findScansByDateCategory($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page, $pageSize);
    $totalSearch = $data->total;
    $imagingReport = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Requests</div>
<table class="table table-striped table-hover no-footer">
    <thead>
    <tr>
        <th>Date</th>
        <th>Scan</th>
        <th>Staff</th>
        <th>Patient</th>
        <th>Scheme</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
    $iItem = new InsuranceItemsCostDAO();
    foreach ($imagingReport as $k=>$report) { ?>
        <tr>
            <td><?= date('jS M, Y', strtotime($report->request_date)) ?></td>
            <td><?= $report->scanName ?></td>
            <td><?= $report->staffFullName ?></td>
            <td><?= $report->patientFullName ?></td>
            <td><?= $report->scheme_name ?></td>
            <td><?= $iItem->getItemPriceByCode($report->billing_code, $report->patient_id, TRUE)  ?></td>
        </tr>
    <?php } ?>
    </tbody>
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