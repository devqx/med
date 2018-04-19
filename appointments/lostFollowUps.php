<?php
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/AppointmentDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
$page = isset($_POST['page']) && !is_blank($_POST['page']) ? $_POST['page'] : 0;
$pageSize = 10;
$appoints = (new AppointmentDAO())->getMissedAppointments($page, $pageSize, TRUE);
$totalSearch = $appoints->total;
?>
<div id="lostFollowUpList" class="">
	<h5>Missed Appointments (<?= $totalSearch ?>)</h5>
	<table class="table-striped table-hover table">
		<thead>
		<tr>
			<th>S/No</th>
			<th>Patient Name</th>
			<th>Appointment Type</th>
			<th>Appointment Date</th>
			<th>Phone</th>
			<th>Email</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($appoints->data as $key => $appoint) {
			if ($appoint->getGroup()->getPatient()) { ?>
				<tr>
					<td><?php echo $key + 1 ?>.</td>
					<td>
						<a href="/patient_profile.php?id=<?= $appoint->getGroup()->getPatient()->getId() ?>"><?= $appoint->getGroup()->getPatient()->getFullname() ?></a>
					</td>
					<td><?= $appoint->getGroup()->getType() ?></td>
					<td><?= date(((@explode(" ", $appoint->getStartTime())[1] === null) ? 'M jS, Y' : 'M jS, Y H:i'), strtotime($appoint->getStartTime())) ?></td>
					<td><?= $appoint->getGroup()->getPatient()->getPhoneNumber() ?></td>
					<td><?= $appoint->getGroup()->getPatient()->getEmail() ?></td>
				</tr>
			<?php }
		} ?>
		</tbody>
	</table>
	<div class="resultsPager dataTables_wrapper dataTables_paginate no-footer">
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status"
		     aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?>
			of <?= ceil($totalSearch / $pageSize) ?>)
		</div>

		<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
			<a id="DataTables_Table_1_first" data-page="0"
			   class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>"
			   class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
				records</a>
			<a id="DataTables_Table_1_last"
			   class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			   data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
			<a id="DataTables_Table_1_next"
			   class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>"
			   data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
		</div>
	</div>
</div>
<script type="text/javascript">
	// $('table.table').dataTable();
	$(document).on('click', '.resultsPager.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			$.post('<?= $_SERVER['REQUEST_URI'] ?>', {page: page}, function (data) {
				$('#lostFollowUpList').html( $(data).filter('#lostFollowUpList').html() );
			});
			e.handled = true;
		}
	});
</script>