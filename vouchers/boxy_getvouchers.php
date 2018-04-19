<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/27/15
 * Time: 4:20 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VoucherDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$batch_id = (isset($_GET['batchid'])) ? $_GET['batchid'] : '';
$vouchers = (new VoucherDAO())->getByBatch($batch_id);
?>
<div style="width: 660px;">
	<table class="voucher table table-hover table-striped">
		<thead>
		<tr>
			<th>Code</th>
			<th>Value (<?= $currency ?>)</th>
			<th>Type</th>
			<th>Status</th>
			<th>&nbsp;</th>
		</tr>
		</thead>
		<tbody>
		<?php if (isset($vouchers) && count($vouchers) > 0) {
			foreach ($vouchers as $key => $voucher) { /*$voucher=new VoucherBatch();*/ ?>
				<tr>
					<td><?= $voucher->getCode() ?></td>
					<td><?= $voucher->getBatch()->getAmount() ?></td>
					<td><?= ucwords($voucher->getBatch()->getType()) ?></td>
					<td><?= ($voucher->getUsedDate() === null) ? 'VALID' : 'USED' ?></td>
					<td><a href="javascript:;" class="printvoucher" data-id="<?= $voucher->getId() ?>"><i class="icon-print"></i>
							print</a></td>
				</tr>
				<?php
			}
		} else {
			echo '<tr><td colspan="5">No Vouchers Available</td></tr>';
		} ?>
		</tbody>
	</table>
</div>
<script>
	$(document).ready(function () {
		$("table.voucher").DataTable();
		$(document).on('click', '.printvoucher', function (e) {
			if (!e.handled) {
				var vid = $(this).data('id');
				Boxy.load('/vouchers/boxy.select_printer.php?vid=' + vid, {title: 'Select Printer'});
				e.handled = true;
				e.preventDefault();
			}
		});
	});
</script>