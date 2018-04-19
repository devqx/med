<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/5/15
 * Time: 4:19 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$lab_centers = (new ServiceCenterDAO())->all('Dentistry');
?>
<h5>Available Dentistry Centres <a href="javascript:" class="pull-right" onclick="Boxy.load('/pages/pm/dent-center-new.php', {title:'New Dentistry Centre'})">New Dentistry Centre</a></h5>

<table class="table table-striped">
    <thead><tr><th>Name/Code</th><th>Department</th><th>Cost Centre</th></tr></thead>
    <?php foreach ($lab_centers as $lab) {//$lab=new ServiceCenter();?>
        <tr><td><?=$lab->getName()?></td><td><?=$lab->getDepartment()->getName()?></td><td><?= $lab->getCostCentre()->getName()?></td></tr>
    <?php }?>
</table>