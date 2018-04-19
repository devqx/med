<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/27/17
 * Time: 12:10 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.vitals.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientVitalPreference.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';

$vitalObj = (new VitalDAO())->getByName($_REQUEST['type']);
$data = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], $vitalObj->getId());
//save this preference for this patient
(new PatientVitalPreference())->setPatient(new PatientDemograph($_GET['id']))->setType($_REQUEST['type'])->add();

?>
<div data-name="<?= $_REQUEST['type'] ?>">
	<div class="vital_chart shadow" id="chart<?= $_REQUEST['type'] ?>"></div>
	<div class="last_reading shadow absolutecenter">
		<div>Last <?= $_REQUEST['type'] ?> Reading:<h3><?php
				$dataVal = end($data) ? end($data)->getValue() : null;
				$abnormal = '';
				if ($dataVal != '' || $dataVal) {
					$values = array_filter(explode('/', $dataVal));
					if ($vitalObj->getMaximum() && $vitalObj->getMinimum()) {
						if (count($values) == 1) {
							$val = $values[0];
							if (floatval($val) > floatval($vitalObj->getMaximum()) || floatval($val) < floatval($vitalObj->getMinimum())) {
								//abnormal: add an alert
								$abnormal = ' class="abnormal" data-val="'.$val.'" data-min="'.$vitalObj->getMinimum().'" data-max="'.$vitalObj->getMaximum().'"';
							}
						} else if (count($values) == 2) {
							$maxs = array_filter(explode("/", $vitalObj->getMaximum()));
							$mins = array_filter(explode("/", $vitalObj->getMinimum()));
							if ((floatval($values[0]) > floatval($maxs[0]) || floatval($values[0]) < floatval($mins[0])) || (floatval($values[1]) > floatval($maxs[1]) || floatval($values[1]) < floatval($mins[1]))) {
								$abnormal = ' class="abnormal"';
							}
						}
						
					}
					?><span<?= $abnormal ?>><?= $dataVal . utf8_encode($vitalObj->getUnit()) ?></span><?php } else {
					echo 'N/A';
				}
				?></h3>
			<span style="display: block;"><?= end($data) ? date("\\o\\n Y/m/d g:ia", strtotime(end($data)->getReadDate())) : '' ?></span>
			<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type_id=<?= $vitalObj->getId() ?>&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
				New Reading</a>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function () {
		new Highcharts.Chart({
			chart: {
				renderTo: 'chart<?=$_REQUEST['type']?>',
				zoomType: 'x',
				<?php if(in_array($_REQUEST['type'], ['Blood Pressure','Pain Scale'])){ ?>type: 'spline' ,
				<?php } else { ?>type: 'area' ,<?php } ?>
				ignoreHiddenSeries: true,
				width: 805,
				height: 255
			},
			rangeSelector: {
				selected: 4,
				inputEnabled: false,
				buttons: [{
					type: 'minute',
					count: 60,
					text: 'hr'
				}, {
					type: 'day',
					count: 1,
					text: 'Tod'
				}, {
					type: 'day',
					count: 7,
					text: 'wk'
				}, {
					type: 'month',
					count: 1,
					text: '1m'
				}, {
					type: 'all',
					text: 'All'
				}]
			},
			title: {
				text: '<?=$_REQUEST['type']?>'
			}, subtitle: {
				text: document.ontouchstart === undefined ?
					'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
			},
			xAxis: {
				//maxZoom: 3600000, // 1 hour in detailed zoom
				type: 'datetime',
				tickPixelInterval: 100
			},
			yAxis: {
				title: {
					text: '<?=$_REQUEST['type']?> (<?= $vitalObj->getUnit() ?>)'
				}<?php if(in_array($_REQUEST['type'], ['Pain Scale'])){?>,
				min: 0,
				max: 10
				<?php }?>
			},
			scrollbar: {enabled: false},
			<?php if(!in_array($_REQUEST['type'], ['Blood Pressure', 'Pain Scale'])){?>
			series: [{
				name: '<?=$_REQUEST['type']?>',
				data: [<?php foreach ($data as $temp) { ?>
					
					[<?= $temp->getUnixTime()  ?>,<?= $temp->getValue() ?>],
					<?php }?>
				]
			}]
			<?php } else if(in_array($_REQUEST['type'], ['Blood Pressure'])){?>
			series: [
				{
					name: 'Systolic',
					data: [
						<?php foreach ($data as $bp) {//$bp=new VitalSign();
						 error_log("Timee:".json_encode($bp->getUnixTime()));
						$systolic = explode("/", $bp->getValue())[0];?>
						[<?= $bp->getUnixTime()  ?>, <?= $systolic ?>],
						<?php }?>
					]
				}, {
					name: 'Diastolic',
					data: [
						<?php foreach ($data as $bp) {//$bp=new VitalSign();
						$diastolic = (!is_null($bp->getValue())) ? @explode("/", $bp->getValue())[1] : $bp->getValue();?>
						[<?= $bp->getUnixTime()  ?>, <?= $diastolic ?>],
						<?php }?>
					]
				}]
			<?php } else if(in_array($_REQUEST['type'], ['Pain Scale'])){?>
			series: [{
				name: 'Pain Scale',
				threshold : 3,
				color: 'orange',
				negativeColor: 'rgb(124, 181, 236)',
				data: [<?php foreach ($data as $p) {//$temp=new VitalSign();?>
					[<?= $p->getUnixTime()  ?>,<?= $p->getValue() ?>],
					<?php }?>
				]
			},{
				name: 'Pain Scale',
				threshold : 5,
				color: 'red',
				negativeColor: 'transparent',
				data: [<?php foreach ($data as $p) {//$temp=new VitalSign();?>
					[<?= $p->getUnixTime()  ?>,<?= $p->getValue() ?>],
					<?php }?>
				]
			}]
			<?php }?>
		});

	});
</script>
