<?php
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/insuranceSchemes.php';
$_GET['full'] = true;
include_once $_SERVER ['DOCUMENT_ROOT'] . '/api/search_bills.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CurrencyDAO.php';
$currency = (new CurrencyDAO())->getDefault();
?>
<style type="text/css">
	div.growlUI {
		background: url(/img/check48.png) no-repeat 10px 10px
	}

	div.growlUI h1, div.growlUI h2 {
		color: white;
		padding: 5px 5px 5px 75px;
		text-align: left;
		font-size: 14px;
	}

	@media print {
		body {
			background-color: #fff;
		}

		#scrollIt, div.tablescroll.table {
			display: none;
		}

		#scrollIt2 {
			display: block !important;
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

		/*h3 {font-size: 100%}*/
	}
</style>
<script src="/assets/blockUI/jquery.blockUI.js"></script>
<div class="document">
	<div><a href='/pm/reporting/index.php' class='btn'><< Back</a></div>
	<br>
	<div class="row-fluid">
		<div class="span2">Insurance Scheme</div>
		<div class="span4">
			<label>
				<select name="scheme" id="scheme" style="width: 100%">
					<option value="0">--- Select Scheme ---</option>
					<?php foreach ($insSchemes as $is) {
						if (!empty($_REQUEST['scheme']) && $is->getId() === $_REQUEST['scheme']) {
							$schemeName = $is->getName();
						}
						echo '<option value="' . $is->getId() . '" ' . (!empty($_REQUEST['scheme']) && $is->getId() === $_REQUEST['scheme'] ? ' selected' : '') . '>' . $is->getName() . '</option>';
					} ?>
				</select>
			</label>
		</div>
		<div class="span2"><input type="text" class="wide" value="<?= (isset($_REQUEST['from']) ? $_REQUEST['from'] : '') ?>" name="from" id="from" readonly="readonly" placeholder="From:"/></div>
		<div class="span2"><input type="text" class="wide" value="<?= (isset($_REQUEST['to']) ? $_REQUEST['to'] : '') ?>" name="to" id="to" readonly="readonly" placeholder="To:"/></div>
		<div class="span2">
			<button class="btn" id="printIT" type="button"><i class="icon-print"></i>Print</button>
		</div>
	</div>

	<hr>
	<?php if (isset($schemeName)) { ?>
		<div class="printable">
			<h3 style="text-align: center">Medical Bill Report for <?= $schemeName ?> Program<br>PERIOD:
				<span> [<?= $_REQUEST['from'] . " - " . $_REQUEST['to'] ?>]</span></h3>
			<table id="scrollIt" class="table table-bordered table-hover">
				<thead>
				<tr>
					<!--                <th>S/N</th>-->
					<th>Date</th>
					<th>Bill No</th>
					<th>Patient</th>
					<th>Details/Description</th>
					<th>Amount <?= $currency ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				$pats = [];
				$isNewPat = true;
				$currentDate = (sizeof($bills) > 0 ? date('M jS, Y', strtotime($bills[0]->getTransactionDate())) : null);
				
				$grandTotal = 0;
				foreach ($bills as $key => $bill) {
					if ($bill->getPatient() != null && !in_array($bill->getPatient()->getId(), $pats)) {
						$pats[] = $bill->getPatient()->getId();
						$currentDate = null;
						$isNewPat = true;
					} else {
						if ($currentDate !== date('M jS, Y', strtotime($bill->getTransactionDate()))) {
							$currentDate = date('M jS, Y', strtotime($bill->getTransactionDate()));
						} else {
							$currentDate = null;
						}
						$isNewPat = false;
					}
					echo '<tr>' //                    . '<td>' . ($key + 1) . '</td>'
						. '<td style="white-space:nowrap; font-size:smaller">' . (date('M jS, Y', strtotime($bill->getTransactionDate()))) . '</td>' . '<td>' . $bill->getId() . '</td>' . '<td>' . ($isNewPat ? '<a href="/patient_profile.php?id=' . $bill->getPatient()->getId() . '" title="View patient details">' . $bill->getPatient()->getFullname() . '</a>' : ' <span style="display: block; font-size: 150%">&rdquo;</span> ') . '</td>' . '<td>' . $bill->getDescription() . '</td>' . '<td style="text-align:right">' . $bill->getAmount() . '</td>' . '</tr>';
					$grandTotal += $bill->getAmount();
				}
				require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
				echo '<tr><td colspan="6" style="text-align: right"><h6>GRAND TOTAL: '. $currency->getSymbolLeft() . number_format($grandTotal, 2) . $currency->getSymbolRight(). '</h6></td></tr>';
				echo '<tr><td colspan="6" style="text-align: right"><h6>' . strtoupper((new toWords($grandTotal))->words) . ' </h6></td></tr>';
				?>
				</tbody>
			</table>
			<table id="scrollIt2" style="display: none;" class="table">
				<thead>
				<tr>
					<!--                <th>S/N</th>-->
					<th>Date</th>
					<th>Bill No</th>
					<th>Patient</th>
					<th>Details/Description</th>
					<th>Amount</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$pats = [];
				$isNewPat = true;
				$currentDate = (sizeof($bills) > 0 ? date('M jS, Y', strtotime($bills[0]->getTransactionDate())) : null);
				
				$grandTotal = 0;
				foreach ($bills as $key => $bill) {
					if ($bill->getPatient() != null && !in_array($bill->getPatient()->getId(), $pats)) {
						$pats[] = $bill->getPatient()->getId();
						$currentDate = null;
						$isNewPat = true;
					} else {
						if ($currentDate !== date('M jS, Y', strtotime($bill->getTransactionDate()))) {
							$currentDate = date('M jS, Y', strtotime($bill->getTransactionDate()));
						} else {
							$currentDate = null;
						}
						$isNewPat = false;
					}
					echo '<tr>' //                    . '<td>' . ($key + 1) . '</td>'
						. '<td style="white-space:nowrap; font-size:smaller">' . (date('M jS, Y', strtotime($bill->getTransactionDate()))) . '</td>' . '<td>' . $bill->getId() . '</td>' . '<td>' . ($isNewPat ? '<a href="/patient_profile.php?id=' . $bill->getPatient()->getId() . '" title="View patient details">' . $bill->getPatient()->getFullname() . '</a>' : ' <span style="display: block;; font-size: 150%">&rdquo;</span> ') . '</td>' . '<td>' . $bill->getDescription() . '</td>' . '<td style="text-align:right">' .$currency->getSymbolLeft() . $bill->getAmount() . $currency->getSymbolRight(). '</td>' . '</tr>';
					$grandTotal += $bill->getAmount();
				}
				require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/func.php';
				echo '<tr><td colspan="6" style="text-align: right"><h6>GRAND TOTAL: '. $currency->getSymbolLeft() . number_format($grandTotal, 2, '.', ','). $currency->getSymbolRight() . '</h6></td></tr>';
				echo '<tr><td colspan="6" style="text-align: right"><h6>' . strtoupper(convert_number_to_words($grandTotal)) . ' NAIRA</h6></td></tr>';
				?>
				</tbody>
			</table>
		</div>
		<div id="searchBox">
		</div>
	<?php } ?>
</div>


<script type="text/javascript">
	$(document).ready(function () {
		$("#scrollIt").dataTable({aaSorting: []});
		$("#scheme").select2();
		var now = new Date().toISOString().split('T')[0];
		$('#from').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now
				});
			},
			onChangeDateTime: function (e) {
				if ($("#scheme").val() === "0") {
					$.growlUI("Please select Insurance Scheme first");
					$("#from").val("");
				}
				$("#to").val("");
			}
		});
		$('#to').datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onShow: function (ct) {
				this.setOptions({
					maxDate: now,
					minDate: $("#from").val() ? $("#from").val() : false
				});
			},
			onChangeDateTime: function () {
				if ($("#scheme").val() === "0") {
					$.growlUI('Sorry you need to select Insurance Scheme first');
					$("#from").val("");
					$("#to").val("");
				}
				if ($("#scheme").val() !== "0" && $("#from").val().trim().length > 6 && $("#to").val().trim().length > 6) {
					window.location.href = "/pm/reporting/insuranceScheme.php?scheme=" + $('#scheme').val() + "&from=" + $("#from").val() + "&to=" + $("#to").val();
				}
			}
		});
		$('#printIT').on('click', function () {
			//$("div.tablescroll").remove();
			//$('#scrollIt2').show();
			window.print();
		});
	});
</script>
