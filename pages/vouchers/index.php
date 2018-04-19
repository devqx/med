<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/26/15
 * Time: 2:47 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
@session_start();
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if(!$this_user->hasRole($protect->voucher)){
    exit($protect->ACCESS_DENIED);
}?>
<div class="mini-tab">
    <a class="tab on batches" href="javascript:;" onclick="aTab(1)" data-href="?batches">Voucher Batches</a>
    <a class="tab search" href="javascript:;" onclick="aTab(2)" data-href="?search">Search Used Vouchers</a>
    <?php if($this_user->hasRole($protect->voucher)){?><a class="tab new pull-right" href="javascript:;" data-href="#">Generate Voucher</a><?php }?>
</div>
<div id="voucher_container"></div>