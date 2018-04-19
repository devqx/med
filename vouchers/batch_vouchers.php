<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/27/15
 * Time: 2:43 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherBatchDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : 0;
$pageSize = 10;
$data = (new VoucherBatchDAO())->all($page, $pageSize);
$batches = $data->data;
$totalSearch = $data->total;
?>
	<style type="text/css">
		.voucher_details {
			cursor: pointer;
			color: #1570A6
		}

		.voucher_details:hover {
			text-decoration: underline;
		}
	</style>
	<div><a href="javascript:;" onclick="aTab(1)" data-href="?batches">Refresh List</a></div>
<?= ((sizeof($batches) > 0) ? '' : '<div class="notify-bar">There are no voucher batches available yet</div>') ?>
<?php if (sizeof($batches) > 0) { ?>
	<div class="notify-bar"><i class="icon-info-sign"></i> <?= $totalSearch ?> Vouchers</div>
	<table class="table table-striped table-hover no-footer">
		<thead>
		<tr>
			<th>ID</th>
			<th>Batch Type</th>
			<th>Quantity</th>
			<th>Used</th>
			<th>Value (<?= $currency ?>)</th>
			<th>Created By</th>
			<th>Description</th>
			<th>Date Generated</th>
			<th>Expiration Date</th>
			<th>Voucher Centre</th>
			<th>*</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($batches as $v) { ?>
			<tr data-href="/vouchers/boxy_getvouchers.php?batchid=<?= $v->getId() ?>">
				<td class="voucher_details"><?= $v->getId() ?></td>
				<td><?= ucwords($v->getType()) ?></td>
				<td><?= $v->getQuantity() ?></td>
				<td><?= $v->getUsed() ?></td>
				<td><?= $v->getAmount() ?></td>
				<td><?= $v->getGenerator()->getshortname() ?></td>
				<td><?= ucfirst($v->getDescription()) ?></td>
				<td><?= date("Y M, d", strtotime($v->getDateGenerated())) ?></td>
				<td><?= date("Y M, d", strtotime($v->getExpirationDate())) ?></td>
				<td><?= ($v->getServiceCentre() == null) ? '' : $v->getServiceCentre()->getName() ?></td>
				<td><a href="javascript:;" data-id="<?= $v->getId() ?>" class="span1 exportIT"><i class=""></i> Export</a></td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
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
	<script>
		$(document).ready(function () {
			$('.exportIT').on('click', function (e) {
				if (!e.handled) {
					window.open('/vouchers/export_batch.php?ex_=csv&batch_id=' + $(this).data('id'), '_blank');
					e.handled = true;
					e.preventDefault();
				}
			});
			$(".voucher_details").on('click', function (e) {
				if (!e.handled) {
					Boxy.load($(this).parent().data('href'), {
						title: 'Vouchers', afterHide: function () {
							aTab(1);
						}
					});
					e.handled = true;
					e.preventDefault();
				}
			});
		});
		$(document).on('click', '.list1.dataTables_wrapper a.paginate_button', function (e) {
			if (!e.clicked) {
				var page = $(this).data("page");
				if (!$(this).hasClass("disabled")) {
					$('#voucher_container').load('batch_vouchers.php?page=' + page);
				}
				e.clicked = true;
			}
		});
	</script>
<?php } ?>