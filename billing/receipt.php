<!DOCTYPE html>
<html moznomarginboxes mozdisallowselectionprint>
<head>
	<style>
		@import url(../style/bootstrap.css);
		@import url(../style/def.css);

		.transform {
			-webkit-transform: rotate(-90deg); /* Chrome, Safari, Opera */
			transform: rotate(-90deg);
			/*width: 100%;*/
			margin-left: -30px;
			margin-top: 110%;
			font-size: 200%;
		}

		.table td {
			padding: 20px;
		}

		code {
			padding: 5px;
			line-height: 30px;
			color: darkgrey;
			background-color: transparent;
			border: none;
		}

		.no-print {
			margin-top: 10px;
		}
	</style>
	<script src="/js/jquery.js"></script>
</head>
<body>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.bills.php';
$bills = new Bills();
$bill = (new BillDAO())->getBill($_GET['id'], true);

$debits = number_format(-$bills->_getPatientPaymentsTotals($bill->getPatient()->getId()), 2, '.', '');
$credits = number_format($bills->_getPatientCreditTotals($bill->getPatient()->getId()), 2, '.', '');
$outstanding_total = ($credits - $debits);
?>

<div style="width:550px;margin: 10px auto; border: 2px solid #ccc;">
	<table class="table table-borderless">
		<tr>
			<td rowspan="2" style="border-right: 1px solid #ccc !important;">
				<div class="transform">RECEIPT</div>
			</td>
			<td></td>
			<td>
				<div class="pull-right">
					Receipt #: <code><?= $bill->getId() ?></code><br>
					Date: <code><?= date("Y M, d", strtotime($bill->getTransactionDate())) ?></code>
				</div>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				Received from: <code><?= ($bill->getPatient() !== null) ? $bill->getPatient()->getFullname() : $bill->getBilledTo()->getName() ?></code><br>
				The sum of <code><?= ucwords(convert_number_to_words($bill->getAmount() * -1)) ?> (<?= number_format($bill->getAmount() * -1, 2, '.', '') ?>)</code><br>
				<?= $bill->getDescription() ?>

			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
			<td class="pull-right">Received By: <code><?= $bill->getReceiver()->getFullname() ?></code></td>
		</tr>
	</table>
	<div class="no-print pull-right">
		<a class="action" href="javascript:window.print()"><i class="icon-print"></i> Printer</a>
		<a class="action" data-type="thermal" href="javascript:;"><i class="icon-print"></i> Receipt Printer</a>
		<a href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=Receipt<?= (int)$bill->getId() ?>" class="action"><i class="icon-book"></i> PDF</a>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('a[data-type="thermal"]').live('click', function (e) {
			if (e.handled !== true) {
				var xmld = "<receipt><h4 align='center' bold='on'><?= strtoupper($bill->getClinic()->getName()) ?></h4><h5 align='center'><?= $bill->getClinic()->getAddress() ?>,</h5><h5 align='center'><?= $bill->getClinic()->getLGA()->getName() ?>, <?= $bill->getClinic()->getLGA()->getState()->getName() ?></h5><h5 align='center'>Date: <?= date("Y M, d", strtotime($bill->getTransactionDate())) ?></h5><br /><h5>Received from: <?= ($bill->getPatient() !== null) ? $bill->getPatient()->getFullname() : $bill->getBilledTo()->getName()?></h5><hr /><h5>The sum of <?= ucwords(convert_number_to_words($bill->getAmount() * -1))?> (<value value-symbol='NGN' value-symbol-position='after'><?= number_format($bill->getAmount() * -1, 2, '.', '')?></value>)</h5><h5><?= $bill->getDescription()    ?></h5><h5>Balance: <value value-symbol='NGN' value-symbol-position='after'><?= number_format($outstanding_total, 2, '.', '')?></value></h5><br /><br /><barcode encoding='ean13'><?= $bill->getId() ?></barcode><cashdraw /></receipt>";
				$.ajax({
					url: "http://127.0.0.1:8000/receipt/printnow/",
					data: {q: xmld},
					type: 'post',
					success: function (s) {
						console.log(s);
					},
					error: function (t) {
						console.log(t)
					}
				});
				e.preventDefault();
				e.handled = true;
			}
		});
	});
</script>
</body>
</html>
