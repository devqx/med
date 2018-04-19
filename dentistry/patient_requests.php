<div class="menu-head"><span id="newLink"><a href="javascript:void(0)" onclick="Boxy.load('/dentistry/boxy.new_request.php?pid=<?= $_GET['id'] ?>', {title:'New Request'})">New Request</a></span></div>
<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/14/14
 * Time: 5:17 PM
 */

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDentistryDAO.php';
if (isset($_GET['pid'])) {
	$temp = (new PatientDentistryDAO())->getPatientRequests($_GET['pid'], $page, $pageSize);
} else {
	$temp = (new PatientDentistryDAO())->getServices($page, $pageSize);
}

$Requests = $temp->data;
$totalSearch = $temp->total;
include_once "template.php"; ?>
<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
<div class="resultsPager no-footer dataTables_paginate">
	<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
		<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>

		<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
	</div>
</div>
<!-- yes i know, the start tag is in template.php -->
</div>
<script>
	$(document).on('click', '.resultsPager.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		var date_start = $('div[class="ui-bar-c"] > .input-prepend > input[name="date_start"]').val();
		var date_stop = $('div[class="ui-bar-c"] > .input-prepend > input[name="date_stop"]').val();
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "/dentistry/patient_requests.php?pid=<?=$_GET['pid']?>&page=" + page;
			$('#contentPane').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	});
</script>
<?php exit;