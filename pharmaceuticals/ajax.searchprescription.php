<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';


if (isset($_POST['date_start'], $_POST['date_stop'])){
	/*$date = explode(",", $_POST['date']);
	$start = $date[0];
	$stop  = $date[1];*/
	$start = $_POST['date_start'];
	$stop = $_POST['date_stop'];
}else {
	$start = null;
	$stop  = null;
}


$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 10;
$patientId = isset($_POST['patient_id']) ? $_POST['patient_id'] : null;
$data = (new PrescriptionDAO())->findPrescriptions($_POST['q'], $start, $stop, $page, $pageSize, TRUE, $patientId);
$totalSearch = $data->total;
?>
<?= (($totalSearch > 0) ? '' : '<div class="notify-bar">No prescriptions matched the filter</div>') ?>
<?php if ($totalSearch > 0) { ?>
	<div id="searchResults">
	<div class="dataTables_wrapper">
		<table class="table outer table-hover table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>ID</th>
				<th>By</th>
				<th>Patient</th>
				<th>Coverage</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($data->data as $p) { ?>
				<tr class="pres_details" data-href="/pharmaceuticals/boxy_fillBatch.php?pCode=<?= $p->getCode() ?>&mode=pharmacy&pid=<?= $p->getPatient()->getId() ?>">
					<td><?= date("d M, Y", strtotime($p->getWhen())) ?></td>
					<td class="color"><?= $p->getCode() ?></td>
					<td><?= $p->getRequestedBy()->getFullname() ?></td>
					<td><?= $p->getPatient()->isAdmitted() ? '<i title="Patient is currently admitted" class="required fa fa-hospital-o"></i>':'' ?> <span class="profile" data-pid="<?=$p->getPatient()->getId()?>"><?= $p->getPatient()->getFullName() ?></span></td>
					<td><?= $p->getPatient()->getScheme()->getName()?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="list2 dataTables_wrapper no-footer">
			<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
				results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
			</div>

			<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
				<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?>
					records</a>
				<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?>
					records</a>
			</div>
		</div>
		</div>

	</div>
<?php } ?>
