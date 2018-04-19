<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/6/16
 * Time: 6:07 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$consultants = array();
if($date ===TRUE){
    $data = (new StaffManager())->getDoctorWhoSawWho($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['specialty_id'], $_REQUEST['staff_id'], $page, $pageSize);
    $totalSearch = $data->total;
    $consultants = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Consultant Requests</div>
<table class="table table-striped table-hover no-footer">
    <thead>
    <tr>
        <th>Date</th>
        <th>Doctor</th>
        <th>Specialization</th>
        <th>Department</th>
        <th>Patient</th>
        <th>Scheme</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
    <?php if(isset($consultants) && sizeof($consultants)>0){
        for($i=0; $i<count($consultants); $i++) { ?>
        <tr>
            <td nowrap><?= date('jS M, Y h:iA', strtotime($consultants[$i]->Date)) ?></td>
            <td><?= $consultants[$i]->Doctor ?></td>
            <td><?= $consultants[$i]->Specialization ?></td>
            <td><?= $consultants[$i]->Department ?></td>
            <td><a href="/patient_profile.php?id=<?=$consultants[$i]->PatientID?>" target="_blank"><?= $consultants[$i]->Patient ?> (<?= strtoupper($consultants[$i]->Sex{0}) ?>)</a></td>
            <td><?= $consultants[$i]->Scheme ?></td>
            <td><?= $consultants[$i]->Amount ?></td>
        </tr>
        <?php }
    } ?>
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

