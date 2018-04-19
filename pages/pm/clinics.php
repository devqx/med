<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/19/17
 * Time: 9:49 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AptClinicDAO.php';

$data = (new AptClinicDAO())->all();
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Name</th>
		<th class="amount">Daily Appointment Limit</th>
		<th>Queue Type</th>
		<th>*</th>
	</tr>
	</thead>
	<?php foreach ($data as $clinic){//$clinic=new AptClinic();?>
		<tr>
			<td><?= $clinic->getName()?></td>
			<td class="amount" data-decimals="0"><?= $clinic->getALimit()?></td>
			<td><?= $clinic->getQueueType() ? $clinic->getQueueType() : '- -'?></td>
			<td><a class="edit-clinic" href="javascript:" data-id="<?= $clinic->getId()?>">Edit</a></td>
		</tr>
<?php }?>
</table>
<script type="text/javascript">
	$(document).ready(function () {
		$('table.table.table-striped').dataTable();
	}).on('click', 'a[href="javascript:"][data-id].edit-clinic', function (e) {
		if(!e.handled) {
			var id = $(this).data('id');
			Boxy.load('/pages/pm/clinic_edit.php?id=' + id, {
				afterHide: function(){
					loadClinics();
				}
			});
			e.handled=true;
		}
	})
</script>
