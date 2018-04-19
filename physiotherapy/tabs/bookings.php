<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/15/16
 * Time: 2:56 PM
 */
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PhysioBookingDAO.php';
$temp = (new PhysioBookingDAO())->active($page, $pageSize);
$bookings = $temp->data;
$totalSearch = $temp->total;
?>
<div class="dataTables_wrapper" id="contentliquid">
    <?php include "template.php";?>
    <div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page+1?> of <?= ceil($totalSearch / $pageSize)?>)</div>
    <div class="resultsPagerBookings no-footer dataTables_paginate">
        <div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
            <a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page +1 ) == 1)? "disabled":""?>">First <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page+1) <= 1)? "disabled":""?>">Previous <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_last" class="paginate_button next <?=(($page +1 ) == ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ceil($totalSearch / $pageSize) -1 ?>">Last <?= $pageSize ?> records</a>
            <a id="DataTables_Table_1_next" class="paginate_button next <?=(($page +1) >= ceil($totalSearch / $pageSize))?"disabled":""?>" data-page="<?= ($page) +1 ?>">Next <?= $pageSize ?> records</a>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click', '.resultsPagerBookings.dataTables_paginate a.paginate_button', function(e){
        var page = $(this).data("page");
        if(!$(this).hasClass("disabled") && !e.handled){
            var url = "/physiotherapy/tabs/bookings.php?page="+page;
            $('#contentliquid').load(url, function (responseText, textStatus, req) {});
            e.handled = true;
        }
    });
    $(document).ready(function(){
        $('a.cancelSessions').live('click', function(e){
            var href = $(this).data("href");
            if(e.handled != true){
                Boxy.ask("Are you sure you want to cancel this booking?", ["Yes", "No"], function(choice){
                    if(choice == "Yes"){
                        $.post(href, {action:"cancel"}, function(s){
                            if(s){
                                $('a[data-url="tabs/bookings.php"]').click();
                                Boxy.info("Booking Cancelled");
                            } else {
                                Boxy.alert("An error occurred");
                            }
                        }, 'json');
                    }
                });
                e.handled=true;
            }
        });
    });
</script>

