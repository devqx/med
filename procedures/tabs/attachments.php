<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/26/16
 * Time: 6:53 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);
?>
<div class="menu-head"><?php if (in_array($p->getStatus(), ["open","started"]) && (@$_GET['add'] != "false")) { ?>
<a href="javascript:;" onclick="addAttachment(<?= $_GET['id'] ?>)">Upload attachment</a><?php } ?></div>

<p></p>
<?php if (count($p->getAttachments()) == 0) { ?>
	<div class="notify-bar">No attachments</div>
<?php } else { ?>
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Date Added</th>
			<th>Uploaded By</th>
			<th>Description</th>
			<th>*</th>
		</tr>
		</thead>
		<?php foreach ($p->getAttachments() as $res) {//$res=new ProcedureAttachment();?>
			<tr>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($res->getUploadDate())) ?></td>
				<td><?= $res->getUploadBy()->getFullname() ?></td>
				<td><?= $res->getDescription() ?></td>
				<td><?= ($res->getUrl() != null) ? '<a href="'.$res->getUrl().'" target="_blank">View</a>' : "N/A" ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>
<script type="text/javascript">
	function addAttachment(key) {
		Boxy.load('/procedures/dialogs/new-attachment.php?id=' + key, {
			afterHide: function () {
				// refresh this tab?
				$("#tab-container").easytabs('select', '#attachments');
			}
		});
	}
</script>