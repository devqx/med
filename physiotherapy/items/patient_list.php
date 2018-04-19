<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 9:05 AM
 */

$_REQUEST['status'] = NULL;
$_REQUEST['patient_id'] = $_REQUEST['id'];
?>
<div class="menu-head"><a href="javascript:" onclick="showTabs(17)">Physiotherapy Services</a> | <span id="newLink" class="bold"><a href="javascript:void(0)" onClick="Boxy.load('/physiotherapy/items/new_request.php?patient_id=<?= $_GET['id'] ?>',{title: 'New Physiotherapy Item Request', afterHide: function() {showTabs(17, 2); }})">New Physiotherapy Item Request</a></span></div>

<?php
include_once 'requests_list.php';
?>
<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
<div class="resultsPagerReceived_ no-footer dataTables_paginate">
    <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
        <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
        <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click', '.resultsPagerReceived_.dataTables_paginate a.paginate_button', function(e){
        var page = $(this).data("page");
        if(!$(this).hasClass("disabled") && !e.handled){
            var url = "physiotherapy/items/patient_list.php?id=<?=$_REQUEST['patient_id'] ?>&pane=items&page="+page;
            $('#contentPane').load(url, function (responseText, textStatus, req) {});
            e.handled = true;
        }
    });

    $('a.receiveItem, a.deliverItem').live('click', function (e) {
        if(!e.handled){
            if($(this).hasClass('receiveItem')){
                Boxy.load("physiotherapy/items/receive.php?req-id="+$(this).data("id"));
            } else if($(this).hasClass('deliverItem')){
                Boxy.load("physiotherapy/items/deliver.php?req-id="+$(this).data("id"));
            }
            e.handled = true;
        }
    })
</script>
</div>
