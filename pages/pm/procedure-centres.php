<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/18/16
 * Time: 5:25 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$centers = (new ServiceCenterDAO())->all('Procedure');
?>
<h5 class="pull-left">Available Business Unit/Service Centres </h5>
<a href="javascript:" class="pull-right" onclick="Boxy.load('/pages/pm/procedure-center-new.php', {title:'New Business Unit/Service Centre'})">New Business Unit/Service Centre</a>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Name/Code</th>
		<th>Department</th>
		<th>Cost Centre</th>
	</tr>
	</thead>
	<?php foreach ($centers as $pc) {//$lab=new ServiceCenter();?>
		<tr>
			<td><?= $pc->getName() ?></td>
			<td><?= $pc->getDepartment()->getName() ?></td>
			<td><?= $pc->getCostCentre()->getName() ?></td>
		</tr>
	<?php } ?>
</table>