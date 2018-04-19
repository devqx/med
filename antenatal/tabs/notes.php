<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/26/15
 * Time: 2:05 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
$instance = (new AntenatalEnrollmentDAO())->get($_GET['instance'], true);
?>
<?php if ($instance->getActive()) { ?>
	<div class="menu-head">
		<div style="width:50%;float:left">
			<a href="javascript:void(0)" onClick="Boxy.load('/antenatal/tabs/boxy.add-note.php?instance=<?= $_GET['instance'] ?>',{title:'New Note'})">New Note</a>
		</div>
	</div>
<?php } ?>
	<p></p>

<?php if (count($instance->getNotes()) == 0) { ?>
	<div class="notify-bar">No notes available</div>
<?php } else { ?>

	<table class="table table-striped">
		<thead>
		<tr>
			<th width="20%">Date</th>
			<th>Note</th>
			<th width="15%">By</th>
		</tr>
		</thead>
		<?php foreach ($instance->getNotes() as $note) {//$note=new AntenatalNote();?>
			<tr>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($note->getEnteredOn())) ?></td>
				<td><?= $note->getNote() ?></td>
				<td><?= $note->getEnteredBy() ? $note->getEnteredBy()->getFullname() : '- -' ?></td>
			</tr>
		<?php } ?>
	</table>
<?php } ?>