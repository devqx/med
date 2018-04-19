<div><a href='/pm/reporting/index.php'><input type='button' class='btn' value='<< Back'></a></div>
<h4>Drug Prescriptions</h4>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PrescriptionDAO.php";
$datefrom = isset($_GET['from']) ? $_GET['from'] : null;
$dateto = isset($_GET['to']) ? $_GET['to'] : null;
$pres = (new PrescriptionDAO())->getOpenPrescriptionsByDateRange($datefrom, $dateto, false, null);
$drugs_prescribed = [];

foreach ($pres as $d) {
	foreach ($d->getData() as $dr) {
		$drugs_prescribed[] = $dr;
	}
}

function compare_sort($a, $b)
{
	return strcmp($a->getGeneric()->getId(), $b->getGeneric()->getId());
}

usort($drugs_prescribed, 'compare_sort');
$drugslist = [];
foreach ($drugs_prescribed as $drugspresc) {
	$brand = $drugspresc->getDrug() != null ? $drugspresc->getDrug()->getName() : '- -';
	$generic = $drugspresc->getGeneric()->getName();
	$dispensed = $drugspresc->getFrequency() . '*' . $drugspresc->getDuration();
	$qtydispensed = (int)$drugspresc->getFrequency() * $drugspresc->getDuration();
	$stockqty = $drugspresc->getDrug() != null ? $drugspresc->getDrug()->getStockQuantity() : '0';
	
	$drugslist[$drugspresc->getGeneric()->getId()][] = array('Id' => $drugspresc->getGeneric()->getId(), 'DrugName' => $brand, 'DrugGeneric' => $generic, 'QtyPrescribed' => $qtydispensed, 'QtyDispensed' => $dispensed, 'StockQuantity' => $stockqty);
}
?>
<div style="" class="no-print drug-prescriptions">
	<div class="row-fluid">
		<label class="span6">
			From
			<input type="text" name="from" value="<?= (isset($_GET['from']) ? $_GET['from'] : '') ?>" id="from" placeholder="Click/touch to select a Start date"/>
		</label>
		<label class="span6">
			To
			<input type="text" name="to" value="<?= (isset($_GET['to']) ? $_GET['to'] : '') ?>" id="to" placeholder="Click/touch to select a End date" disabled="disabled"/>
		</label>
	</div>
</div>
<div class="document">
	<div style="text-align: center; font-size: 28px; /*margin-top: 230px*/">
		<?php if (!isset($_GET['from']) && !isset($_GET['to'])) {
			echo "<br>Showing all drug prescriptions";
		} elseif (isset($_GET['from']) && !isset($_GET['to'])) {
			echo "<br>Showing prescriptions for " . date("d M, Y", strtotime($_GET['from']));
		} else {
			echo "<br>Showing prescriptions from <em>" . date("d M, Y", strtotime($_GET['from'])) . "</em> - <em>" . date("d M, Y", strtotime($_GET['to'])) . "</em>";
		} ?>
	</div>
	<table class="table table-bordered table-striped">
		<thead>
		<tr>
			<th>Drug</th>
			<th>Generic</th>
			<th>Quantity Prescribed</th>
			<th>Quantity Available</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($drugslist as $presc) { ?>
			<tr>
				<td><?= $presc[0]['DrugName'] ?></td>
				<td><?= $presc[0]['DrugGeneric'] ?></td>
				<td><?php echo array_sum(array_col($presc, 'QtyPrescribed')); ?></td>
				<td><?= $presc[0]['StockQuantity'] ?></td>
			</tr>
		<?php } ?>


		</tbody>
	</table>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('table').dataTable();
		$("#from").datetimepicker({
			format: 'Y-m-d',
			formatDate: 'Y-m-d',
			timepicker: false,
			onChangeDateTime: function (dp, $input) {
				if ($input.val().trim() !== "") {
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
				if ($("#from").val() && $("#to").val()) {
					location.href = '/pm/reporting/drugprescriptions.php?from=' + $("#from").val() + '&to=' + $("#to").val();
				}
			}
		});

		if ($("#from").val().trim() !== "") {
			$("#to").removeAttr('disabled');
		}
	});
</script>