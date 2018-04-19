<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 10/16/15
 * Time: 3:09 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
?>
<div class="mini-tab">
    <a class="tab on certs" href="javascript:;" onclick="aTab(1)" data-href="?certs">Certificates</a>
    <a class="tab search" href="javascript:;" onclick="aTab(2)" data-href="?search">Search Certificates</a>
    <?php if ($this_user->hasRole($protect->doctor_role)) { ?><a class="tab addnew pull-right" href="javascript:;" onclick="aTab(3)" data-href="?addnew">New Certificate</a><?php } ?>
</div>
<div id="deathCert_container"></div>