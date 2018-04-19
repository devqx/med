<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/16/15
 * Time: 10:57 AM
 */

include_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$labReport=[];
if($date ===TRUE){
    $data = (new PatientLabDAO())->findLabRequestsByDateCategory($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page, $pageSize, TRUE);
    $totalSearch = $data->total;
    $labReport = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Lab Requests</div>
<table class="table table-striped table-hover">
    <thead>
    <tr>
        <th>Date</th>
        <th>Lab</th>
        <th>Staff</th>
        <th>Patient</th>
        <th>Scheme</th>
        <th>Amount</th>
    </tr>
    </thead>
    <?php if(isset($labReport)){
        foreach ($labReport as $k=>$report) { ?>
            <tr>
                <td><?= date('jS M, Y', strtotime($report->getLabGroup()->getRequestTime())) ?></td>
                <td><?= $report->getTest()->getName() ?></td>
                <td><?= $report->getLabGroup()->getRequestedBy()->getFullname() ?></td>
                <td><?= $report->getPatient()->getFullname() ?></td>
                <td><?= $report->getPatient()->getScheme()->getName() ?></td>
                <td><?= $report->getTest()->getBasePrice() ?></td>
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