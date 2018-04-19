<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/30/16
 * Time: 4:43 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PAuthCodeDAO.php';
$data = (new PAuthCodeDAO())->get($_GET['id']);
?>
<section style="width: 650px;">
	<table class="table table-striped">
		<tr>
			<th>Request Date</th>
			<td><?= date(MainConfig::$dateTimeFormat, strtotime($data->getCreateDate()))?></td>
		</tr>
		<tr>
			<th>Patient</th>
			<td><?= $data->getPatient()->getFullname()?></td>
		</tr>
		<tr>
			<th>Request Channel</th>
			<td><?= $data->getChannel()->getDescription()?> (<?= $data->getChannel()->getName()?>)</td>
		</tr>
		<tr>
			<th>Code</th>
			<td><?= $data->getCode() ? $data->getCode() : '- -' ?></td>
		</tr>
		<tr>
			<td colspan="2">Notes | <a class="pull-right" href="javascript:" id="addPa_Note" data-id="<?= $data->getId()?>">Add Note</a></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="no-border table table-striped">
					<tr>
						<th nowrap>Date</th>
						<th>Note</th>
						<th nowrap>By</th>
					</tr>
					<?php foreach ($data->getNotes() as $note){//$note=new PAuthCodeNote();?>
					<tr>
						<td nowrap><?= date(MainConfig::$dateTimeFormat, strtotime($note->getTime()))?></td>
						<td><?= $note->getNote()?></td>
						<td nowrap><?= $note->getUser()->getUsername()?></td>
					</tr>
					<?php }?>
				</table>
			</td>
		</tr>
	</table>
</section>
<script type="text/javascript">
	$(document).on('click', '#addPa_Note', function (e) {
		var id = $(this).data('id');
		if(!e.handled){
			Boxy.load('/billing/pa_code_note.new.php?id='+id);
			e.handled = true;
		}
	})
</script>
