<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/16
 * Time: 1:13 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InpatientObservationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$instanceStatus = (new InPatientDAO())->getInPatient($_GET['aid'], FALSE)->getStatus();
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$notes = (new InpatientObservationDAO())->forIpInstance($_GET['aid'], $page, $pageSize);
$totalSearch = $notes->total;
?>
<?php if ($instanceStatus == "Active") { ?>

	<div class="menu-head">
		<a href="javascript:void(0)"
		   onClick="Boxy.load('/admissions/patientTabs/observations.new.php?aid=<?= $_GET['aid'] ?>', {title: 'New Observation', afterHide: function() {showTabs(14); }})">
			New Observation Note</a>
	</div>
<?php } ?>
<p class="clear"></p>
<?php if (count($totalSearch) == 0) { ?>
	<div class="notify-bar">No observations have been made</div>
<?php } else { ?>
	<div id="observations" class="dataTables_wrapper">
		<table class="table table-striped">
			<thead class="">
			<tr>
				<th nowrap="nowrap" width="15%">Date</th>
				<th>Note</th>
				<th width="20%">Noted By</th>
			</tr>
			</thead>
			<?php foreach ($notes->data as $note) {//$note = new InpatientObservation();?>
				<tr>
					<td><?= date(MainConfig::$dateTimeFormat, strtotime($note->getDateEntered())) ?></td>
					<td><?= $note->getNote() ?></td>
					<td><?= $note->getUser()->getFullname() ?></td>
				</tr>
			<?php } ?>
		</table>
		<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?>
			results
			found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
		</div>
		<div class="resultsPagerObs no-footer dataTables_paginate">
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
<?php } ?>

<script type="text/javascript">
	$(document).on('click', '.resultsPagerObs.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = '<?= $_SERVER['REQUEST_URI'] ?>';
			$.post(url, {page: page}, function (response) {
				$('#observations').html($(response).filter('#observations').html());
			});
			e.handled = true;
		}
	});
</script>
