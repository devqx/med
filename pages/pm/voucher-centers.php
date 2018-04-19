<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 1/7/16
 * Time: 3:25 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$lab_centers = (new ServiceCenterDAO())->all('Voucher');
?>
<h5>Available Voucher Centres <a href="javascript:" class="pull-right" onclick="Boxy.load('/pages/pm/voucher-center-new.php', {title:'New Voucher Centre'})">New Voucher Centre</a></h5>

<table class="table table-striped">
    <thead><tr><th>Name/Code</th><th>Department</th><th>Cost Centre</th></tr></thead>
    <?php foreach ($lab_centers as $lab) {//$lab=new ServiceCenter();?>
        <tr><td><?=$lab->getName()?></td><td><?=$lab->getDepartment()->getName()?></td><td><?= $lab->getCostCentre()->getName()?></td></tr>
    <?php }?>
</table>