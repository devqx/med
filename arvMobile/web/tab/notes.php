<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/24/16
 * Time: 3:38 PM
 */





//todo this page will get data from the ArvConsulting object which has its ArvConsultingData objects/children
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
$page= isset($_REQUEST['page']) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$notes = (new VisitNotesDAO())->getPatientNotes($_GET['pid'], $page, $pageSize, FALSE, 'h');
$row_data_array = $notes->data;
$totalSearch = $notes->total;
?>

<div class="menu-head"><a href="javascript:" id="newArtNote">New Record</a></div>
<!--<div class="" style="border-bottom:1px solid #dfdfdf"></div>-->
<div id="arvNotes">
    <table class="table">
        <thead>
        <tr>
            <th width="20%">Date</th>
            <th>Note</th>
            <th width="10%">By</th>
        </tr>
        </thead>
        <?php foreach($row_data_array as $row_data){?><tr>
            <td class="nowrap"><?=date("<\\s\\m\\a\\l\\l>jS M, Y h:ia</\\s\\m\\a\\l\\l>", strtotime($row_data->date_of_entry))?></td>
            <td><?= $row_data->description?></td>
            <td valign="top"><?= $row_data->noted_by ? $row_data->username :'N/A' ?></td>
            </tr><?php }?>
        <?php if($notes->total ==0){?>
            <tr><td colspan="3"><div class="notify-bar">No available notes to display</div> </td></tr>
        <?php }?>
    </table>
    <div class="list_arv dataTables_wrapper no-footer">
        <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>

        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '#newArtNote', function (e) {
        if (!e.handled){
            Boxy.load("/arvMobile/web/tab/dialogs/wizard.php?pid=<?= $_GET['pid']?>", {afterHide: function () {
                tab(1, new Event('click'));
            }});
            e.handled=true;
        }
    }).on('click', '.list_arv.dataTables_wrapper a.paginate_button', function(e){
        if (!e.clicked) {
            var page = $(this).data("page");
            if (!$(this).hasClass("disabled")) {
                $.get('/arvMobile/web/tab/notes.php', {'page': page, 'pid':<?= $_REQUEST['pid']?>}, function (s) {
                    $('#arvNotes').html($(s).filter('#arvNotes').html());
                });
            }
            e.clicked = true;
        }
    });
</script>
