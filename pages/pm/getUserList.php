<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
$data = (new StaffDirectoryDAO())->getAllStaffs();?>
<h6>Existing Users:</h6>
<table id="usersList" class="table table-hover table-striped">
    <?php foreach ($data as $staff) {?>
        <tr><td><i class="icon-chevron-right"></i><span style=""><a href="/staff_profile.php?id=<?=$staff->getId()?>"><?=$staff->getFullname()?></a></span></td>
        <td><i class="icon-check"></i><a href="javascript:void(0);" onClick="loadEditUser('<?=$staff->getId()?>')">Edit Roles</a></td>
        <?php if($staff->getStatus()=="active"){?>
            <td><i class="icon-lock"></i><a href="javascript:void(0);" onClick="doDisable('<?=$staff->getId()?>')">Disable</a></td>
        <?php } else if($staff->getStatus() == "disabled") {?>
        <td><i class="icon-ok-circle"></i><a href="javascript:void(0);" onClick="doEnable('<?=$staff->getId()?>')">Enable</a></td>
        <?php }?>
    </tr>
    <?php }?>

</table>
<script type="text/javascript">
    $("#usersList").tableScroll({height:700});
</script>

