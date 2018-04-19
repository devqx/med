<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/11/14
 * Time: 12:43 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
$p = (new PatientProcedureDAO())->get($_GET['id']);

?>
<?php if (in_array($p->getStatus(), ["open", "started", "scheduled"]) && (@$_GET['add'] != "false")) { ?>
	<div class="menu-head">
		<a href="javascript:;" onclick="addNote(<?= $p->getId() ?>)">Add a Note</a>
	</div>
<?php } ?>

<p></p>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Date</th>
		<th>Type</th>
		<th>Specialty</th>
		<th>Note</th>
		<th>By</th>
	</tr>
	</thead>
	<?php foreach ($p->getNotes() as $note) {//$note=new PatientProcedureNote();?>
		<tr>
			<td title="<?= date(MainConfig::$dateTimeFormat, strtotime($note->getNoteTime())) ?>"><?= date("Y.m.d H:i A", strtotime($note->getNoteTime())) ?></td>
			<td><strong><?= $note->getType() ?></strong></td>
			<td><?= $note->getSpecialty() ? $note->getSpecialty()->getName() : '- -' ?></td>
			<td>
				<span class="badge badge-success"><?= $note->getType() ?></span> <?= $note->getNote() ?>
			</td>
			<td><em><?= $note->getStaff()->getFullname() ?></td>
		</tr>
	<?php } ?>
</table>


<script type="text/javascript">
	function addNote(key) {
		Boxy.load('/procedures/dialogs/new-note.php?id=' + key, {
			afterHide: function () {
				// refresh this tab?
				$("#tab-container").easytabs('select', '#notes');
			}
		});
	}
</script>