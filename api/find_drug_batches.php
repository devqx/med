<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/9/16
 * Time: 11:36 AM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] .'/classes/DAOs/DrugBatchDAO.php';

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 20;
$totalSearch = 0;
$drugs = array();
$data = (new DrugBatchDAO())->getBatches($_REQUEST['from'], $_REQUEST['to'], $page, $pageSize);
$totalSearch = $data->total;
$drugs = $data->data;
?>
<div class="notify-bar"><i class="icon-info-sign"></i> <?=$totalSearch ?> Drugs</div>
<table class="table table-striped table-hover no-footer">
    <thead>
    <tr>
        <th>Drug Name</th>
        <th>Drug Quantity</th>
        <th>Batch</th>
        <th>Expiration Date</th>
    </tr>
    </thead>
    <?php
    if(isset($drugs) && sizeof($drugs)>0){
        foreach($drugs as $key=>$drug){ ?>
            <tr>
                <td><?= $drug->getDrug()->getName() ?></td>
                <td class="amount"><?php if($drug->getQuantity() <= 0){
                        echo '<span class="warning-bar no-padding">'.$drug->getQuantity().'</span>';
                    } else {
                        echo $drug->getQuantity();
                    } ?></td>
                <td><?= $drug->getName() ?></td>
                <td><?php if(strtotime($drug->getExpirationDate()) <= time()){
                        echo '<span class="warning-bar no-padding">'.date('jS F Y', strtotime($drug->getExpirationDate())).'</span>';
                    } else {
                        echo date('jS F Y', strtotime($drug->getExpirationDate()));
                    } ?></td>
            </tr>
        <?php }
    }?>
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