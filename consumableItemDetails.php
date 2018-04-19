<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/13/17
 * Time: 2:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$protect = new Protect();

$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
//if ($this_user->hasRole($protect->consumables)) { ?>
	<div class="menu-head"><span id="newLink">
<a href="javascript:void(0)" onClick="Boxy.load('/boxy.newItemRequest.php?id=<?=$_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>',{title: 'New Item'})">New Item Request</a></span>
	</div>
<?php //}
//if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy))
//	exit ($protect->ACCESS_DENIED);
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientItemRequestDAO.php';
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$patient = isset($_GET['pid'])?$_GET['pid']:(isset($_GET['id'])? $_GET['id']:null);
$inpatient = isset($_GET['aid'])?$_GET['aid']:null;
$center = null;
$data = (new PatientItemRequestDAO())->getAllItems($page, $pageSize, $center, true, $patient, $inpatient, null);
$totalSearch = $data->total;

?>

<?= (($totalSearch > 0) ? '' : '<div class="notify-bar">No Item request found</div>') ?>

<div class="dataTables_wrapper" id="resultPage">
	<div id="open_request"><a href="javascript:;" onclick="showTabs(20)" data-href="?open">Refresh List</a></div>
	<?php if ($totalSearch > 0) { ?>
		<table class="table outer table-hover table-striped">
			<thead>
			<tr>
				<th>Date</th>
				<th>ID</th>
				<th>By</th>
				<th>Item</th>
				<th>Generic</th>
			</tr>
			</thead>
			<tbody>
			<?php
			foreach ($data->data as $p) { ?>
				<tr>
					<td nowrap class="pres_details"><?= date(MainConfig::$dateTimeFormat, strtotime($p->getRequestDate())) ?></td>
					<td class="pres_details">
						<a href="javascript:;" class="code" data-id="<?= $p->getCode() ?>" data-note="<?= urlencode($p->getRequestNote()) ?>">
							<?= $p->getCode() ?></a></td>
					<td class="pres_details"><?= $p->getRequestedBy()->getFullname() ?></td>
					<td class="pres_details">
                        <?php foreach ($p->getData() as $item)  { ?>
                            <?= $item->getItem() ? '<span class="tag">'. $item->getItem()->getName().'</span>' : '--' ?>
                        <?php } ?>
					</td>
					<td class="pres_details">
						<?= $p->getData()[0]->getGeneric() ? $p->getData()[0]->getGeneric()->getName() : '---' ?>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	<div class="list1 dataTables_wrapper no-footer">
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

<script>
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				goto(page);
			}
			e.clicked = true;
		}
	});
	function goto(page) {
		$('#contentPane').load('consumableItemDetails.php?page=' + page + '&service_center_id=' + $('select[name="service_center_id"]').val() + '&patient_id=' + $('[name="patient_id"]').val());
	}

	$(document).ready(function () {
		$('.code').live('click', function (e) {
			if (!e.handled) {
				Boxy.load('/boxy_ItemRequestDetails.php?rCode=' + $(this).data('id') + '&note=' + $(this).data("note"), {title: 'Item Request Details'});
				e.handled = true;
				e.preventDefault();
			}
		});

	});


</script>

