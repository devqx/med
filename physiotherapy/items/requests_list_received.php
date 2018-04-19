<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/13/16
 * Time: 3:26 PM
 */

$_REQUEST['status'] = ['Received'];
include_once 'requests_list.php'; ?>
<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
<div class="resultsPagerReceived no-footer dataTables_paginate">
	<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
		<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
		<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.resultsPagerReceived.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = "items/requests_list_received.php?page=" + page + "&physiotherapy_centre_id=" + $('select[name="physiotherapy_centre_id"]').val();
			$('#requests_container').load(url, function (responseText, textStatus, req) {
			});
			e.handled = true;
		}
	});
</script>
</div>
