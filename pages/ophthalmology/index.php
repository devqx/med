<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>
<div class="mini-tab">
    <a class="tab on incomplete" href="javascript:;" onclick="aTab(1)" data-href="?incomplete">Request Queue</a>
    <a class="tab search" href="javascript:;"  onclick="aTab(2)" data-href="?search">Search Requests</a>
    <?php if($this_user->hasRole($protect->lab_super)){?><a class="tab approve" href="javascript:;"  onclick="aTab(4)" data-href="?approve">Requests to approve</a><?php }?>
    <a class="tab addnew pull-right" href="javascript:;"  onclick="aTab(3)" data-href="?addnew">New Request</a>
    <a class="tab datesearch" href="javascript:;"  onclick="aTab(5)" data-href="?datesearch">Requests by Date</a>
    <a class="tab itemsrequests" href="javascript:;"  onclick="aTab(6)" data-href="?itemsrequests">Items Requests</a>
</div>
<div id="opth_container"></div>

<script type="text/javascript">
    $(function(){
        $('.head-link').live('click', function(){
            $('.'+$(this).attr('id')).fadeToggle();
        });
    });

</script>