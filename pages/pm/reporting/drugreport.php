<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 3/10/15
 * Time: 12:10 PM
 */
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';

$page = (isset($_POST['page'])) ? $_POST['page'] : 0;
$pageSize = 20;
$totalSearch = 0;
$drugs = array();
$data = (new DrugBatchDAO())->getBatches($_REQUEST['from'], $_REQUEST['to'], $page, $pageSize);
$totalSearch = $data->total;
$drugs = $data->data;
?>
<style type="text/css">
	.filter .btn {
		float: right;
		margin-top: 24px;
		white-space: nowrap;
	}

	.filter .span1 {
		margin-left: 0;
	}

	#exportIT {
		margin-left: 1%;
		width: 8%;
	}
</style>
<div><a class="btn-link" href="/pm/reporting/index.php">&laquo; Back</a></div>
<form id="filterForm" class="document" method="post" action="/pm/reporting/report.consultant.php">
	<h4>Drug Expiration Report</h4>
</form>
<div class="document">
	<div id="drug_report_container">
		<?php if ($totalSearch < 1) {
			echo '<div class="notify-bar">There are no drug reports</div>';
		} else { ?>
			<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Drugs</div>
			<table class="table table-striped table-hover no-footer">
				<thead>
				<tr>
					<th>Drug Name</th>
					<th>Drug Quantity</th>
					<th>Batch</th>
					<th>Expiration Date</th>
				</tr>
				</thead>
				<?php
				if (isset($drugs) && sizeof($drugs) > 0) {
					foreach ($drugs as $key => $drug) { ?>
						<tr>
							<td><?= $drug->getDrug()->getName() ?></td>
							<td class="amount <?php if ($drug->getQuantity() <= 0) { ?>required<?php } ?>">
								<?= $drug->getQuantity() ?>
							</td>
							<td><?= $drug->getName() ?></td>
							<td class="<?php if (strtotime($drug->getExpirationDate()) <= time()) { ?>required<?php } ?>">
								<?= date('jS F Y', strtotime($drug->getExpirationDate())) ?>
							</td>
						</tr>
					<?php }
				} ?>
			</table>
			<div class="list1 dataTables_wrapper no-footer">
				<div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite"> <?= $totalSearch ?> results found (Page <?= $page + 1 ?> of <?= ceil($totalSearch / $pageSize) ?>)</div>
				<div id="DataTables_Table_1_paginate" class="dataTables_paginate paging_simple_numbers">
					<a id="DataTables_Table_1_first" data-page="0" class="paginate_button previous <?= (($page + 1) == 1) ? "disabled" : "" ?>">First <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_previous" data-page="<?= ($page) - 1 ?>" class="paginate_button previous <?= (($page + 1) <= 1) ? "disabled" : "" ?>">Previous <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_last" class="paginate_button next <?= (($page + 1) == ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ceil($totalSearch / $pageSize) - 1 ?>">Last <?= $pageSize ?> records</a>
					<a id="DataTables_Table_1_next" class="paginate_button next <?= (($page + 1) >= ceil($totalSearch / $pageSize)) ? "disabled" : "" ?>" data-page="<?= ($page) + 1 ?>">Next <?= $pageSize ?> records</a>
				</div>
			</div>
		<?php } ?>
	</div>
</div>
<script type="text/javascript">
	$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
		if (!e.clicked) {
			var page = $(this).data("page");
			if (!$(this).hasClass("disabled")) {
				$.post('/api/find_drug_batches.php', {'page': page}, function (s) {
					$('#drug_report_container').html(s);
				});
			}
			e.clicked = true;
		}
	});
	$(document).ready(function () {
		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val()/*.trim()*/ !== "") {
					$("#to").val('').removeAttr('disabled');
				}
				else {
					$("#to").val('').attr({'disabled': 'disabled'});
				}

			}
		});
		$("#to").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({minDate: $("#from").val() ? $("#from").val() : false});
			},
			onSelectDate: function (ct, $i) {

			}
		});

		if ($("#from").val()/*.trim()*/ !== "") {
			$("#to").removeAttr('disabled');
		}

		$('#exportIT').on('click', function (e) {
			if (!e.handled) {
				window.open('/excel.php?dataSource=drug&filename=Drug_Batch_Report', '_blank');
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>