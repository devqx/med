<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/19/16
 * Time: 4:57 PM
 */
$fluidTimeFormat1 = 'g:00A';
$fluidTimeFormat2 = 'H:00';//is this one better??
$fluidTimeFormat3 = 'H:i';//is this one better??

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InPatientDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/FluidChartDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
$instanceStatus = (new InPatientDAO())->getInPatient($_GET['aid'], FALSE)->getStatus();

if (isset($_GET['date']) && !is_blank($_GET['date'])){
	$date = explode(",", $_GET['date']);
	$start = $date[0];
	$stop  = $date[1];
}else {
	$start = null;
	$stop  = null;
}
$chartData = (new FluidChartDAO())->forInstance($_GET['aid'], $start, $stop);
if($instanceStatus == "Active"){ ?>
	<div class="menu-head"><a class="newFluidEntry" href="javascript:">New Entry</a></div>
<?php }?>
<div class="row-fluid ui-bar-c">
	<div class="span6">
		Filter by date:
		<div class="input-prepend">
			<span class="add-on">From</span>
			<input class="span6" type="text" name="date_start" value="<?=isset($start) ? $start: ''?>"  placeholder="Start Date">
			<span class="add-on">To</span>
			<input class="span6" type="text" name="date_stop" value="<?=isset($stop) ? $stop: ''?>" placeholder="Stop Date">
			<button class="btn" type="button" id="date_filter">Apply</button>
		</div>
	</div>
</div>
<p class="clear"></p>
<?php if (count($chartData) == 0) { ?>
	<div class="notify-bar">No entries to show at this time</div>
<?php } else { ?>


	<table class="table table-striped table-bordered">
		<thead>
		<tr>
			<th nowrap="nowrap" width="10%">Date</th>
			<th nowrap="nowrap" width="10%">Time</th>
			<th>Input</th>
			<th>Output</th>
			<th class="amount">Volume (ml)</th>
			<th width="20%">Entry by</th>
		</tr>
		</thead>
		<?php foreach ($chartData as $data) {//$data = new FluidChart();?>
			<tr>
				<td><?= date(MainConfig::$dateFormat, strtotime($data->getTimeEntered())) ?></td>
				<td><?= date($fluidTimeFormat3, strtotime($data->getTimeEntered())) ?></td>
				<td><?= ($data->getRoute()->getType()=="input") ? $data->getRoute()->getName() : '- -'?></td>
				<td><?= ($data->getRoute()->getType()=="output") ? $data->getRoute()->getName() : '- -'?></td>
				<td class="amount"><?= $data->getVolume()?></td>
				<td><?= $data->getUser()->getUsername() ?></td>
			</tr>
		<?php } ?>
	</table>
	<table class="table">
		<tr>
			<td><span class="">Total Inputs: <span id="inputFluid" class="big bold"></span> ml</span></td>
			<td><span class="">Total Outputs: <span id="outputFluid" class="big bold"></span> ml</span></td>
			<td><span class="">Balance: <span id="balFluid" class="big bold"></span> ml</span></td>
		</tr>
	</table>
	<div class="row-fluid">
	</div>
<?php } ?>

<script type="text/javascript">
	$(document).on('click', '.newFluidEntry', function (e) {
		if(!e.handled){
			Boxy.load("/admissions/patientTabs/fluid_chart_new.php?aid=<?= $_GET['aid']?>", {title: "New Fluid Chart Entry"});
			e.handled = true;
		}
	}).ready(function () {
		$('input[name="date_start"]').datetimepicker({format:'Y-m-d H:i' /*, timepicker: false*/});
		$('input[name="date_stop"]').datetimepicker({format:'Y-m-d H:i' /*, timepicker: false*/});
		$entries = <?= json_encode($chartData, JSON_PARTIAL_OUTPUT_ON_ERROR)?>;

		$tI = _.sum(_.map(_.filter($entries, ['type','input']), 'volume'));
		$tO = _.sum(_.map(_.filter($entries, ['type','output']), 'volume'));

		$('#inputFluid').html($tI);
		$('#outputFluid').html($tO);
		$('#balFluid').html($tI-$tO);

	}).on('click', '#date_filter', function (e) {
		if(!e.handled){
			var url = "/admissions/patientTabs/fluid_chart.php?aid=<?= $_GET['aid']?>&date="+encodeURIComponent($('input[name="date_start"]').val())+","+encodeURIComponent($('input[name="date_stop"]').val());
			$('#contentPane').load(url, function (responseText, textStatus, req) { });
			e.handled = true;
		}
	})
</script>
