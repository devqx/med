<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/get_staffs.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PaymentMethodDAO.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
$protect = new Protect();
$paymentTypes = getTypeOptions('type', 'payment_methods');
//todo make this page really printable
?>
<style type="text/css">
	h5.paymenttype {
		margin-top: 30px;
	}

	h5.signature {
		margin-top: 20px;
	}

	@media print {
		body {
			background-color: #fff;
		}

		body * {
			visibility: hidden;
			border: none;
			color: #000
		}

		div.printable, div.printable * {
			visibility: visible;
		}

		div.printable {
			position: absolute;
			left: 0;
			top: 0;
		}

		.display thead tr th {
			background-color: #808080 !important;
			color: #FFF !important;
		}

		.display td, .display td {
			font-size: 11px !important;
		}
	}
</style>
<div class="no-print"><a href='/pm/reporting/index.php' class='btn'><< Back</a></div><br>
<h6>Cashier End of Day Transactions</h6>
<div class="document no-print">
	<div class="row-fluid">
		<label class="span4">
			<select name="staff" id="staff" class="wide">
				<option value="">--- select staff ---</option>
				<?php foreach ($staff as $is) {
					if (!empty($_REQUEST['staff']) && $is->getId() === $_REQUEST['staff']) {
						$staffName = $is->getFullName();
						$staffId = $is->getId();
					}
					if ($is->hasRole($protect->cashier)) {
						echo '<option value="' . $is->getId() . '" ' . (!empty($_REQUEST['staff']) && $is->getId() === $_REQUEST['staff'] ? ' selected' : '') . '>' . $is->getFullName() . '</option>';
					}
				} ?>
			</select>
		</label>
		<div class="span3 input-prepend">
			<span class="add-on">From</span>
			<input class="span10" type="text" placeholder="Start Date" name="from" value="<?= (isset($_GET['from']) ? $_GET['from'] : date('Y-m-d')) ?>" id="from">
		</div>
		<div class="span3 input-prepend">
			<span class="add-on">To</span>
			<input class="span10" type="text" placeholder="End Date" name="to" value="<?= (isset($_GET['to']) ? $_GET['to'] : date('Y-m-d')) ?>" id="to" disabled="disabled">
		</div>

		<a class="btn span1" id="pdfIT" type="button" href="/pdf.php?page=<?= urlencode($_SERVER['REQUEST_URI']) ?>&title=CashierEndOfDayRpt" target="_blank"><i class="icon-pdf"></i>PDF</a>
		<button class="btn span1" id="printIT" type="button"><i class="icon-print"></i>Print</button>

	</div>
</div>
<?php if (isset($staffName)) { ?>
	<div class="printable document">
		<h3 style="text-align: center">Cashier End of Day Report</h3>
		<div>
			<h5>Name: <?php echo $staffName; ?></h5>
			<h5>Date: <?php if ($_REQUEST['from'] == $_REQUEST['to']) {
					echo date("Y M, d", strtotime($_REQUEST['from']));
				} else {
					echo "PERIOD [" . date("Y M, d", strtotime($_REQUEST['from'])) . " - " . date("Y M, d", strtotime($_REQUEST['to'])) . "]";
				} ?></h5>
		</div>
		<?php
		$grandTotal = 0;
		foreach ($paymentTypes as $payment) {
			$bills = (new BillDAO())->getBillsByCustomIds($payment, $staffId, $_REQUEST['from'], $_REQUEST['to'], TRUE);
			if (count($bills) > 0) {
				?>
				<h5 class="paymenttype"><?= ucwords($payment); ?></h5>
				<div class="document">
					<table class="display table">
						<thead>
						<tr>
							<th>Patient</th>
							<th class="amount">Amount</th>
						</tr>
						</thead>
						<tbody>
						<?php
						$total = 0;
						foreach ($bills as $key => $bill) {
							$myBill = ($bill->Amount * -1);
							echo '<tr>' . '<td>' . (($bill->PatientId != null) ? '<a href="/patient_profile.php?id=' . $bill->PatientId . '" title="View patient details">' . $bill->PatientName . '</a>' : '') . '</td>' . '<td style="text-align:right">' . $currency->getSymbolLeft() . abs($myBill). $currency->getSymbolRight() . '</td>' . '</tr>';
							$total += $myBill;
							$grandTotal = ($bill->Type == "discount") ? $grandTotal - 0 : $grandTotal - $myBill;
							//zero because the `cash` equivalent of the discount wasn't `given out` to the patient
						}
						require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
						echo '<tr><td colspan="2" style="text-align: right"><h6>GRAND TOTAL: '. $currency->getSymbolLeft() . number_format(abs($total), 2) . $currency->getSymbolRight() . '</h6></td></tr>';
						echo '<tr><td colspan="2" style="text-align: right"><h6>' . strtoupper((new toWords(abs($total)))->words) . '</h6></td></tr>';
						?>
						</tbody>
					</table>

				</div>
			<?php }

		} ?>
		<h5>Total Amount: <?= $currency->getSymbolLeft() ?><?= number_format(abs($grandTotal), 2); ?><?= $currency->getSymbolRight()?></h5>
		<h5><?= strtoupper((new toWords(abs($grandTotal)))->words) ?></h5>

		<h5>Signature</h5>
		<h5 class="signature">__________________________</h5>
		<h6>Manager</h6>
	</div>
<?php } else {?>

	<!-- ######### When querying using dates only ####-->

<div class="printable document">
	<h3 style="text-align: center">Cashier End of Day Report</h3>
	<div>
		<h5>Date: <?php if (@$_REQUEST['from'] == @$_REQUEST['to']) {
				echo date("Y M, d", strtotime(@$_REQUEST['from']));
			} else {
				echo "PERIOD [" . date("Y M, d", strtotime(@$_REQUEST['from'])) . " - " . date("Y M, d", strtotime(@$_REQUEST['to'])) . "]";
			} ?></h5>
	</div>

	<?php

	$grandTotal = 0;
	foreach ($paymentTypes as $payment) {
		$bills = (new BillDAO())->getBillsByCustom($payment, @$_REQUEST['from'], @$_REQUEST['to'], TRUE);
		if (count($bills) > 0) {
			?>
			<h5 class="paymenttype"><?= ucwords($payment); ?></h5>
			<div class="document">
				<table class="display table">
					<thead>
					<tr>

						<th>Staff</th>
						<th class="amount">Amount</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$total = 0;
					foreach ($bills as $bill) {
						$myBill = ($bill->Amount * -1);
						$grandTotal = ($bill->Type == "discount") ? $grandTotal - 0 : $grandTotal - $myBill;

					if ($bill->Receiver->hasRole($protect->cashier)) {
						echo '<tr>' . '<td>' . $bill->Receiver . '</td>' . '<td style="text-align:right">' .$currency->getSymbolLeft() . $myBill. $currency->getSymbolRight(). '</td>' . '</tr>';
					}
						$total += $myBill;

					}
					require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
					echo '<tr ><td colspan="2" style="text-align: right; margin-right: 5px;"><h6>GRAND TOTAL: ' . $currency->getSymbolLeft() . number_format(abs($total), 2). $currency->getSymbolRight() . '</h6></td></tr>';
					echo '<tr><td colspan="2" style="text-align: right;"><h6>' . strtoupper((new toWords(abs($total)))->words) . '</h6></td></tr>';
					?>
					</tbody>
				</table>

			</div>
		<?php }

	} ?>
	<h5>Total Amount: <?= $currency->getSymbolLeft() ?><?= number_format(abs($grandTotal), 2); ?><?=$currency->getSymbolRight()?></h5>
	<h5><?= strtoupper((new toWords(abs($grandTotal)))->words) ?></h5>

	<h5>Signature</h5>
	<h5 class="signature">__________________________</h5>
	<h6>Manager</h6>
</div>

<?php }?>


<script type="text/javascript">
	$(document).ready(function () {
		$("#staff").select2().change(function(e){
			if(!e.handled){
				go();
				e.handled = true;
			}
		});

		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() != "") {
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
				if (/*$("#staff").val() !== "0" && */$("#from").val().trim().length > 6 && $("#to").val().trim().length > 6) {
					go();
				}
			}
		});

		if ($("#from").val().trim() != "") {
			$("#to").removeAttr('disabled');
		}
		$('#printIT').on('click', function () {
			window.print();
		});
	});

	function go(){
		location.href = '/pm/reporting/transactions.php?staff=' + $('#staff').val() + "&from=" + $("#from").val() + "&to=" + $("#to").val();
	}
</script>