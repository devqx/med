<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/10/15
 * Time: 10:41 AM
 */

//$grouped = true;

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InvoiceDAO.php';
if (isset($_REQUEST['pid'])) {
	$invoices = (new InvoiceDAO())->getForPatient($_REQUEST['pid']);
} else if (isset($_REQUEST['sid'])) {
	$invoices = (new InvoiceDAO())->getForScheme($_REQUEST['sid']);
} else {
	$invoices = [];
} ?>
<table class="table table-striped">
	<thead>
	<tr>
		<th>Invoice #</th>
		<th>Date</th>
		<th>*</th>
	</tr>
	</thead>
	<?php foreach ($invoices as $invoice) { ?>
		<tr>
			<td><?= $invoice->getId() ?></td>
			<td><?= date("Y M, d", strtotime($invoice->getTime())) ?></td>
			<td>
				<a target="_blank" href="/billing/invoice.php?id=<?= $invoice->getId() ?><?= (isset($_REQUEST['mode']) && $_REQUEST['mode'] == "insurance") ? "&mode=insurance" : "" ?>">Open (Ungrouped)</a>
				|
				<a target="_blank" href="/billing/invoice.php?id=<?= $invoice->getId() ?>&grouped<?= (isset($_REQUEST['mode']) && $_REQUEST['mode'] == "insurance") ? "&mode=insurance" : "" ?>">Open (Grouped)</a> |
				<a href="javascript:" onclick="Print('ireceipt', '<?= $invoice->getId() ?>')"><i class="icon-print"></i> Receipt</a> |
				<a href="javascript:" onclick="Print('ireceipt2', '<?= $invoice->getId() ?>')"><i class="icon-print"></i> Receipt (Grouped Items)</a>
			</td>
		</tr>
	<?php } ?>
</table>