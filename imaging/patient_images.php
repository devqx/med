<?php require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/InPatientDAO.php';
$ip = (new InPatientDAO())->getInPatient(@$_GET['aid']);
if( /*($ip != null && $ip->getStatus()=='Active') &&  isset($_GET['aid']) && */true){?><div class="menu-head">
	<span id="newLink"><a href="javascript:void(0)" onclick="Boxy.load('/imaging/boxy.new_scan.php?pid=<?= $_GET['id'] ?>', {title:'New Imaging Request'})">New Imaging Request</a></span>
</div><?php }
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/14
 * Time: 5:17 PM
 */

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientScanDAO.php';
if (isset($_GET['pid'])) {
	$temp = (new PatientScanDAO())->getPatientScans($_GET['pid'], $page, $pageSize);
} else {
	$temp = (new PatientScanDAO())->getScans($page, $pageSize);
}

$pScans = $temp->data;
$totalSearch = $temp->total;
?>
<div class="dataTables_wrapper">
	<?php
include_once "template.php"; ?>
<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results
	found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
</div>
<div class="resultsPagerImaging no-footer dataTables_paginate">
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
	$(document).on('click', '.resultsPagerImaging.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		var date_start = $('div[class="ui-bar-c"] > .input-prepend > input[name="date_start"]').val();
		var date_stop = $('div[class="ui-bar-c"] > .input-prepend > input[name="date_stop"]').val();
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "/imaging/patient_images.php?pid=<?=$_GET['pid']?>&page=" + page;
			$('#contentPane').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	});
</script>

<?php exit;