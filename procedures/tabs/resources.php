<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);
?>

<div class="menu-head"><?php if (in_array($p->getStatus(), ["started", "scheduled"]) && (@$_GET['add'] != "false")) { ?>
	<a href="javascript:;" onclick="addResource(<?= $_GET['id'] ?>)">Add a
			Resource</a><?php } ?></div>

<p></p>
<?php if (count($p->getResources()) == 0 && $p->getAnesthesiologist() == null && $p->getSurgeon() == null) { ?>
	<div class="notify-bar">No resources</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Date Added</th>
			<th>Name</th>
			<th>Specialty</th>
		</tr>
		</thead>
		<?php if ($p->getAnesthesiologist() != null) { ?>
			<tr>
				<td> -- </td>
				<td><?= $p->getAnesthesiologist()->getFullname(); ?></td>
				<td><span class="badge badge-info">Anesthesiologist</span></td>
			</tr>
		<?php } ?>
		<?php if ($p->getSurgeon() != null) { ?>
			<tr>
				<td> -- </td>
				<td><?= $p->getSurgeon()->getFullname(); ?></td>
				<td><span class="badge badge-info">Surgeon</span></td>
			</tr>
		<?php } ?>
		<?php foreach ($p->getResources() as $res) {//$res=new PatientProcedureResource();?>
			<tr>
				<td><?= date("Y.m.d H:iA", strtotime($res->getCreateTime())) ?></td>
				<td><?= $res->getResource()->getFullname() ?></td>
				<td><span class="badge badge-info"><?= $res->getResourceType() ? $res->getResourceType()->getName() : '- -'?></span></td>
				<td><?= ($res->getResource()->getSpecialization() != null) ? $res->getResource()->getSpecialization()->getName() : "- -" ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<script type="text/javascript">
	function addResource(key) {
		Boxy.load('/procedures/dialogs/new-resource.php?id=' + key, {
			afterHide: function () {
				// refresh this tab?
				$("#tab-container").easytabs('select', '#resources');
			}
		});
	}
</script>