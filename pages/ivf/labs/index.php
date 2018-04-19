<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/11/16
 * Time: 1:53 PM
 */
if(!isset($_SESSION))@session_start();
require_once $_SERVER['DOCUMENT_ROOT'] .'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
?>
<div id="" class="mini-tab">
    <a class="tab" href="javascript:;" data-href="labs_open.php">Open Requests</a>
    <a class="tab approve" href="javascript:;" data-href="labs_to_approve.php">Awaiting Review</a>
    <a class="tab on" href="javascript:;" data-href="labs_search.php">Search</a>
    <a class="tab pull-right" href="javascript:;" data-title="New Scan Request" data-href="lab_new.php">New Request</a>
</div>
<div class="document">
    <div class="ball"></div>
</div>
<script type="text/javascript">
    $(document).on('click','.mini-tab > .tab', function(e){
        if(!e.handled){
            $('.mini-tab > .tab').removeClass('on');
            $(e.target).addClass('on');
            $('.mini-tab + .document').load('/pages/ivf/labs/'+$(e.target).data('href'), function(response, status){
                if(status=="error"){ // already throws ajax error
                }
            });
            e.handled = true;
        }
    }).ready(function () {
        $('.mini-tab > .tab:first').click();
    });
</script>