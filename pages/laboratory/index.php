<?php
include_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/class.labs.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>
<div class="mini-tab">
    <a class="tab on incomplete" href="javascript:;" onclick="aTab(1)" data-href="?incomplete<?= (isset($_SESSION['pid'])?'&pid='.$_SESSION['pid']:'')?>">Lab Request Queue</a>
    <a class="tab search" href="javascript:;"  onclick="aTab(2)" data-href="?search">Search Lab Requests</a>
    <?php if($this_user->hasRole($protect->lab_super)){?><a class="tab approve" href="javascript:;"  onclick="aTab(4)" data-href="?approve">Lab Requests to approve</a><?php }?>
    <?php if(Labs::$allowLabOrderLinkInModule){?><a class="tab addnew pull-right" href="javascript:;"  onclick="aTab(3)" data-href="?addnew">New Lab Request</a> <?php }?>
    <a class="tab datesearch" href="javascript:;"  onclick="aTab(5)" data-href="?datesearch">Search Lab Requests by Date</a>
</div>
<div id="labTest_container" style="/*margin-top: 25px;*/"></div>

<script type="text/javascript">
    $(function(){
        $('.head-link').live('click', function(){
            $('.'+$(this).attr('id')).fadeToggle();
        });
    });

</script>