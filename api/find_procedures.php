<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/6/16
 * Time: 1:47 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientProcedureDAO.php';

$date=((isset($_REQUEST['from']) && $_REQUEST['from']!='' && isset($_REQUEST['to']) && $_REQUEST['to']!='')? TRUE:FALSE);
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 15;
$totalSearch = 0;
$procedureReport = array();
if($date ===TRUE){
    $data = (new PatientProcedureDAO())->getProceduresReport($_REQUEST['from'], $_REQUEST['to'], $_REQUEST['category_id'], $page, $pageSize);
    $totalSearch = $data->total;
    $procedureReport = $data->data;
}
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Procedure Requests</div>
<table class="table table-striped table-hover no-footer">
    <thead>
    <tr>
        <th>Date</th>
        <th>Name</th>
        <th>Age</th>
        <th>Procedure</th>
        <th>Diagnosis</th>
        <th>Participants</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($procedureReport as $k => $report) { ?>
	    <tr>
		    <td nowrap><?= date('jS M, Y', strtotime($report->getRequestDate())) ?></td>
		    <td nowrap><span class="profile" data-pid="<?= $report->getPatient()->getId() ?>"><?= ($report->getPatient() == null) ? '' : $report->getPatient()->getFullname() ?></span></td>
		    <td nowrap><?= ($report->getPatient() == null) ? '' : $report->getPatient()->getAge() ?></td>
		    <td><?= $report->getProcedure()->getName() ?></td>
		    <td><?php $conditions = [];
			    foreach ($report->getConditions() as $condition) {
				    $conditions[] = $condition->getName();
			    }
			    echo implode(', ', $conditions); ?></td>
		    <td><?php $participants = [];
			    foreach ($report->getResources() as $resource) {
				    $participants[] = $resource->getResource()->getShortname();
			    }
			    echo implode(', ', $participants); ?></td>
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
