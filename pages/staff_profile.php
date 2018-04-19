<?php
if(!isset($_SESSION)){session_start();}
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.sip.php';
$sip=new SipConfig();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
function invert($status){
    return ($status=='active') ? 'disable':'activate';
}
require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$id = escape($_GET['id']);

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
$staffObj = (new StaffDirectoryDAO())->getStaff($id, TRUE);
?>
<h3 class="pull-left">User Profile</h3>
<?php
echo '<div class="action_buttons">';
if($this_user->hasRole($protect->user_management)){
    echo '<a class="action_ pull-right" id="reset_password" data-id="'.$id.'" style="margin-right: 5px;" href="javascript:void(0)"><i class="_icon_-random_"></i> Reset Password</a>';
    echo '<a class="action_ pull-right" id="edit_role" data-id="'.$id.'" style="margin-right: 5px;" href="javascript:void(0)"><i class="_icon_-lock_"></i> Edit Roles</a>';
    echo '<a class="action_ pull-right" id="enable_disable" data-next-status="'.invert($staffObj->getStatus()).'" data-current-status="'.$staffObj->getStatus().'" data-id="'.$id.'" style="margin-right: 5px;" href="javascript:void(0)"><i class="_icon_-lock_"></i> '. ( $staffObj->getStatus() ==='active' ? 'Disable': ($staffObj->getStatus() ==='disabled' ? 'Enable' : '')).'</a>';
}

if($id == $_SESSION['staffID']){
    echo '<a class="action_ pull-right" data-id="'.$id.'" id="change_password" style="margin-right: 5px;" href="javascript:void(0)"><i class="_icon_-edit_"></i>Change Password</a>';
}
if($id == $_SESSION['staffID'] || $this_user->hasRole($protect->user_management)){
    echo '<a class="action_ pull-right" style="margin-right: 5px;" href="javascript:void(0)" onclick="Boxy.load(\'boxy.staff.edit.profile.php?id='.$id.'\',{title:\'Edit Profile\'})"><i class="_icon_-picture"></i> Edit '.($id == $_SESSION['staffID']?'my':'').' Profile</a>';
}else {
    echo '<a></a>';//just to maintain the block
}

echo '</div>';
?>
<div class="clearfix"></div>
<div class="document">
    <table class="table table-striped">
      <tr class="fancy"><td>Username</td>  <td><abbr><?=$staffObj->getUsername()?></abbr></td>  </tr>
      <tr class="fancy"><td>First Name</td>   <td><?= $staffObj->getFirstName() ?></td> </tr>
      <tr class="fancy"><td>Last Name</td>  <td><?= $staffObj->getLastName() ?></td> </tr>
      <tr class="fancy"><td>Phone number</td>  <td><a href="tel:<?= $staffObj->getPhone()?>"><?= $staffObj->getPhone() ?></a></td> </tr>
      <tr class="fancy"><td>Clinic/Hospital</td>  <td><?= ($staffObj->getClinic()!=NULL)?$staffObj->getClinic()->getName():'N/A' ?></td> </tr>
      <tr class="fancy"><td>Department</td>  <td><?= (($staffObj->getDepartment()!=NULL)?$staffObj->getDepartment()->getName():'N/A') ?></td> </tr>
      <tr class="fancy"><td>Profession</td> <td><?= $staffObj->getProfession() ?></td> </tr>
      <tr class="fancy"><td>Specialization</td><td><?= ($staffObj->getSpecialization()!=NULL)? $staffObj->getSpecialization()->getName():'N/A' ?></td></tr>
        <tr class="fancy"><td>Is Consultant </td><td><?= ($staffObj->getisConsultant() == 1)? "YES" : "NO"?></td></tr>

      <tr class="fancy"><td>Email address</td> <td><?= $staffObj->getEmail() ?></td></tr>
      <tr class="fancy"><td>Folio Number</td> <td><?= !is_blank($staffObj->getFolioNumber()) ? $staffObj->getFolioNumber() :'---' ?></td></tr>
      <?php if($sip::$enabled){?><tr class="fancy"><td>Sip Username</td> <td><?= !is_blank($staffObj->getSipUserName()) ? $staffObj->getSipUserName() : '---' ?></td></tr>
      <tr class="fancy"><td>Sip Extension</td> <td><?= !is_blank($staffObj->getSipExtension()) ? $staffObj->getSipExtension() : '---' ?></td></tr>
      <tr class="fancy"><td>Sip Password</td> <td><?= !is_blank($staffObj->getSipPassword()) ? $staffObj->getSipPassword() : '---' ?></td></tr>
	    <?php }?>
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#change_password').click(function(){
            Boxy.load('boxy.staff.change.password.php?id='+$(this).data('id'),{title:'Change Password'})
        });
        $('#reset_password').click(function(){
            Boxy.load('boxy.staff.reset.password.php?id='+$(this).data('id'),{title:'Reset Password'})
        });
        $('#edit_role').click(function(){
            Boxy.load('/pages/pm/editRole.php?id='+$(this).data('id'),{title:'Edit Roles/Access Rights'})
        });
        $('#enable_disable').click(function(e){
            if($(e.target).data().nextStatus === 'activate'){
                doEnable($(e.target).data().id);
            } else if($(e.target).data().nextStatus === 'disable'){
                doDisable($(e.target).data().id);
            }
        });
    });

    function doEnable(userid) {
        <?php if($this_user->hasRole($protect->user_management)){?>
        $.ajax({
            url: "/pm/index.php?action=enable&user=" + userid,
            success: function(s) {
                var ret = s.split(":");
                if (ret[0]==="success") {
                    $('#existingUsers').load("/pages/pm/getUserList.php");
                } else {
                    Boxy.alert(ret[1]);
                }
            }
        });
        <?php } else {?>
        Boxy.warn("Sorry, you do not have access to this function");
        <?php }?>
    }
    function doDisable(userid) {
        <?php if($this_user->hasRole($protect->user_management)){?>
        $.ajax({
            url: "/pm/index.php?action=disable&user=" + userid,
            success: function(s) {
                var ret = s.split(":");
                if (ret[0]==="success") {
                    $('#existingUsers').load("/pages/pm/getUserList.php");
                } else {
                    Boxy.alert(ret[1]);
                }
            }
        });<?php } else {?>
        Boxy.warn("Sorry, you do not have access to this function");
        <?php }?>
    }
</script>
