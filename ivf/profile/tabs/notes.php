<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/20/16
 * Time: 12:23 PM
 */


require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/ivf/classes/DAOs/IVFNoteDAO.php';

$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;

$data = (new IVFNoteDAO())->forInstance($_GET['aid'], $page, $pageSize);
$totalSearch = $data->total;
?>
<div class="menu-head">
	<span id="newLink"><a id="newTreatmentLnk" href="javascript:;" data-href="/ivf/profile/tabs/note.new.php?id=<?= $_GET['aid'] ?>">New Record</a></span>
</div>
<div id="treatments_" class="dataTables_wrapper">
	<table class="table table-striped">
		<thead>
		<tr>
			<th>Date</th>
			<th width="70%">Note</th>
			<th class="fadedText">By</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($data->data as $item) { //$item = new IVFNote();?>
			<tr>
				<td><?= date(MainConfig::$dateTimeFormat, strtotime($item->getDate())) ?></td>
				<td><?= $item->getNote() ?></td>
				<td class="fadedText"><?= $item->getUser()->getUsername() ?></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
	<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results
		found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)
	</div>
	<div class="resultsPagerNote no-footer dataTables_paginate">
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
<script type="text/javascript">
	$('#newTreatmentLnk').on('click', function (e) {
		if (!e.handled) {
			Boxy.load($(e.target).data('href'), {
				title: $(e.target).data('title'), afterHide: function () {
					$('#tabbedPane').find('li.active a').click();
				}
			});
		}
	});

	$(document).on('click', '.resultsPagerNote.dataTables_paginate a.paginate_button', function (e) {
		var page = $(this).data("page");
		if (!$(this).hasClass("disabled") && !e.handled) {
			var url = '<?= $_SERVER['REQUEST_URI'] ?>';
			$.post(url, {page: page}, function(response){
				$('#treatments_').html($(response).filter('#treatments_').html());
			});
			e.handled = true;
		}
	});
</script>

