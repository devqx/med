<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 10/16/15
 * Time: 3:48 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DeathDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if(is_blank($_POST['q'])){
    exit('<div class="warning-bar">Empty search parameter</div>');
}
$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 1;
$data = (new DeathDAO())->findPatient($_POST['q'], $page, $pageSize);
$totalSearch = $data->total;

if ($totalSearch < 1) {
    ?><div class="notify-bar">No search results for <?=$_POST['q']?></div>
    <?php
} else {
    ?>
    <div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Results found</div>
    <table class="table table-striped table-hover no-footer">
        <thead>
        <tr>
            <th>Date</th>
            <th>Certificate #</th>
            <th>Patient</th>
            <th>Time of Death</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data->data as $c) { ?>
            <tr><td><?= date("d M, Y h:iA", strtotime($c->getCreateDate()))?></td><td><?= $c->getCertNumber() ?></td><td><?= $c->getPatient()->getFullname() ?></td><td><?= date("d M, Y h:iA", strtotime($c->getTimeOfDeath()))?></td></tr>
        <?php }?>
        </tbody>

    </table>
    <div class="resultsPager dataTables_wrapper no-footer">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>

        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
<?php } ?>