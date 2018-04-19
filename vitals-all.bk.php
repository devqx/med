<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVitalPreferenceDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) {
	$vitalOptions = getTypeOptions('type', 'vital_sign');
	$patientOptions = (new PAtientVitalPreferenceDAO())->forPatient($_GET['id']);
	$po = json_decode(json_encode($patientOptions));
	$o = array_col($po, 'type');
	$clinic = "General";
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.vitals.php';
	$vitalsRange = new VitalsConfig();
	
	//require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
	//
	//$pulse = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Pulse');
	//$bps = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Blood Pressure');
	//$temperatures = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Temperature');
	//$weights = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Weight');
	//$heights = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Height');
	//$BMIs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'BMI');
	//$BSAs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'BSA');
	//$RRs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Respiration');
	//$FundusHeights = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Fundus Height');
	//$Glucoses = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Glucose');
	//$Proteins = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Protein');
	//$MACs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Mid-Arm Circumference');
	//$HCs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Head Circumference');
	//$LAs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Length of Arm');
	//$PCVs = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'PCV');
	//$SpO2s = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'SpO2');
	//$Pains = (new VitalSignDAO())->getVitalSignsByType($_GET['id'], 'Pain Scale');
	?>
	<script type="text/javascript">
		var xx = <?= json_encode( $o, JSON_PARTIAL_OUTPUT_ON_ERROR )?>;
		Highcharts.createElement('link', {
			href: 'https://fonts.googleapis.com/css?family=Roboto:400,600',
			rel: 'stylesheet',
			type: 'text/css'
		}, null, document.getElementsByTagName('head')[0]);

		Highcharts.theme = {
			colors: ["#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
				"#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
			chart: {
				backgroundColor: null,
				style: {
					fontFamily: "Roboto, sans-serif"
				}
			},
			title: {
				style: {
					fontSize: '16px',
					fontWeight: 'bold',
					textTransform: 'uppercase'
				}
			},
			tooltip: {
				borderWidth: 0,
				backgroundColor: 'rgba(219,219,216,0.8)',
				shadow: false
			},
			legend: {
				itemStyle: {
					fontWeight: 'bold',
					fontSize: '13px'
				}
			},
			/*xAxis: {
			 gridLineWidth: 0,
			 labels: {
			 style: {
			 fontSize: '12px'
			 }
			 }
			 },*/
			/*yAxis: {
			 minorTickInterval: 'auto',
			 title: {
			 style: {
			 textTransform: 'uppercase'
			 }
			 },
			 labels: {
			 style: {
			 fontSize: '12px'
			 }
			 }
			 },*/
			plotOptions: {
				candlestick: {
					lineColor: '#404048'
				},
				area: {
					fillColor: {
						linearGradient: {
							x1: 0,
							y1: 0,
							x2: 0,
							y2: 1
						},
						stops: [
							[0, Highcharts.getOptions().colors[0]],
							[1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
						]
					},
					marker: {
						radius: 2
					},
					lineWidth: 1,
					states: {
						hover: {
							lineWidth: 1
						}
					},
					threshold: null
				},
				spline: {
					lineWidth: 4,
					states: {
						hover: {
							lineWidth: 5
						}
					}
				}
			},
			// General
			background2: '#F0F0EA',
			credits: {enabled: false}
		};
		// Apply the theme
		Highcharts.setOptions(Highcharts.theme);
		$(document).on('change', 'input:checkbox[name="options[]"]', function(e){
			if(!e.handled){
				var $this = $(e.target);
				if($this.is(':checked')){
					// fetch the javascript part and the html and append to the #page_ element
					$.post('/vital-single.php?id=<?=$_GET['id']?>', {type: $this.val()}, function(data){
						$('#page_').append(data);
					});
				} else {
					// remove that part
					$('[data-name="'+$this.val()+'"]').remove();
				}
				e.handled = true;
			}
		})
	</script>
	<div class="pull-left" style="margin-bottom: 10px;">
		<div class="dropdown">
			<button class="drop-btn large dropdown-toggle" data-toggle="dropdown">
				<i class="icon-cog"></i>
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu" role="menu" aria-labelledby="vLabel" style="max-height: 300px;overflow: scroll;">
				<?php foreach($vitalOptions as $option){?>
					<li><a href="javascript:" class="small" tabIndex="-1"><label><input type="checkbox" <?= in_array($option, $o)?' checked':''?> name="options[]" value="<?=$option ?>"/>&nbsp; <?=$option ?></label></a></li>
				<?php }?>
			</ul>
		</div>
	</div>

	<div id="page_">
		<?php
		foreach($o as $i) {
			$_REQUEST['type'] = $i;
			include('vital-single.php');
		}?>
	</div>
	
	<?php exit;?>
	<div id="page_">
		
		
		<div id="tempgraph" class="shadow"></div>
		<div id="tempLast" class="shadow absolutecenter">
			<div>Last Temperature Reading:<h3><?php
					$tempVal = end($temperatures) ? end($temperatures)->getValue() : null;
					if ($tempVal != '' || $tempVal) {
						$abnormal = (floatval($tempVal) < $vitalsRange::$maxNormalTemperature && floatval($tempVal) > $vitalsRange::$minNormalTemperature) ? '' : ' class="abnormal"';
						?><span<?= $abnormal ?>><?= $tempVal . '&deg;C' ?></span><?php } else {
						echo 'N/A';
					}
					?></h3>
				<span style="display: block;"><?= end($temperatures) ? date("\\o\\n Y/m/d g:ia", strtotime(end($temperatures)->getReadDate())) : '' ?></span>
				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=temp&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="bpgraph" class="shadow"></div>
		<div id="bpLast" class="shadow absolutecenter">
			<div>Last Blood Pressure Reading:
				<h3><?php
					$bpValue = end($bps) ? end($bps)->getValue() : null;
					if ($bpValue) {
						$bp = explode("/", $bpValue);
						$bp_min_range = explode("/", $vitalsRange::$minNormalBP);
						$bp_max_range = explode("/", $vitalsRange::$maxNormalBP);
						$abnormal_high = ($bp[0] >= $bp_max_range[0] || $bp[1] >= $bp_max_range[1]) ? ' class="abnormal"' : '';
						$abnormal_low = ($bp[0] <= $bp_min_range[0] || $bp[1] <= $bp_min_range[1]) ? ' class="abnormal"' : '';
						$abnormal = ($abnormal_high == '' && $abnormal_low == '') ? '' : ' class="abnormal"'; ?>
						<span<?= $abnormal ?>><table align="center" border="0"><tr><td align="center" style="padding-right:20px"><?= $bp[0] ?></td></tr><tr><td align="center" style="padding-left:20px"><?= $bp[1] ?></td></tr></table>
                        </span>
					<?php } else {
						echo 'N/A';
					}
					?></h3>
				<span style="display: block;"><?= end($bps) ? date("\\o\\n Y/m/d g:ia", strtotime(end($bps)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=bp&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="respgraph" class="shadow"></div>
		<div id="respLast" class="shadow absolutecenter">
			<div>Last Respiratory Rate Reading : <h3><?php
					$respValue = end($RRs) ? end($RRs)->getValue() : null;
					echo ($respValue) ? $respValue : 'N/A';
					?></h3>
				<span style="display: block;"><?= end($RRs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($RRs)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=rp&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="pulsegraph" class="shadow"></div>
		<div id="pulseLast" class="shadow absolutecenter">
			<div>Last Pulse Reading: <h3><?php
					$pulseValue = end($pulse) ? end($pulse)->getValue() : null;
					if ($pulseValue) {
						$abnormal = (intval($pulseValue) > $vitalsRange::$minPulse && intval($pulseValue) < $vitalsRange::$maxPulse) ? '' : ' class="abnormal"';
						?><span<?= $abnormal ?>><?= $pulseValue ?></span><?php } else {
						echo 'N/A';
					}
					
					?></h3>
				<span style="display: block;"><?= end($pulse) ? date("\\o\\n Y/m/d g:ia", strtotime(end($pulse)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=pulse&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="pain_scale" class="shadow"></div>
		<div id="pain_scaleLast" class="shadow absolutecenter">
			<div>Last Pain Scale Reading: <h3><?php
					$psValue = end($Pains) ? end($Pains)->getValue() : null;
					if ($psValue) {
						$abnormal = (intval($psValue) > $vitalsRange::$tolerablePain) ? ' class="abnormal"':'';
						?><span<?= $abnormal ?>><?= $psValue ?></span><?php } else {
						echo 'N/A';
					}
					
					?></h3>
				<span style="display: block;"><?= end($Pains) ? date("\\o\\n Y/m/d g:ia", strtotime(end($Pains)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=pain_scale&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="weightgraph" class="shadow"></div>
		<div id="weightLast" class="shadow absolutecenter">
			<div>Last Weight Reading:
				<h3><?php
					$weightValue = end($weights) ? end($weights)->getValue() : null;
					echo ($weightValue) ? $weightValue . 'Kg' : 'N/A';
					?></h3>
				<span style="display: block;"><?= end($weights) ? date("\\o\\n Y/m/d g:ia", strtotime(end($weights)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=weight&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a></div>
		</div>

		<div id="heightgraph" class="shadow"></div>
		<div id="heightLast" class="shadow absolutecenter">
			<div>Last Height Reading: <h3><?php
					$hValue = end($heights) ? end($heights)->getValue() : null;
					echo ($hValue) ? $hValue . 'm' : 'N/A';
					?></h3>
				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=height&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="bmigraph" class="shadow"></div>
		<div id="bmiLast" class="shadow absolutecenter">
			<div>Last BMI Reading: <h3><?php
					$bmiValue = end($BMIs) ? end($BMIs)->getValue() : null;
					echo ($bmiValue) ? $bmiValue : 'N/A';
					?></h3>
				<span style="display: block;"><?= end($BMIs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($BMIs)->getReadDate())) : '' ?></span>

				<a href="javascript:;" onclick="Boxy.load('/vitals-bmi-new.php?type=bmi&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="bsagraph" class="shadow"></div>
		<div id="bsaLast" class="shadow absolutecenter">
			<div>Last BSA Reading: <h3><?php
					$bsaValue = end($BSAs) ? end($BSAs)->getValue() : null;
					echo ($bsaValue) ? $bsaValue : 'N/A';
					?></h3>
				<span style="display: block;"><?= end($BSAs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($BSAs)->getReadDate())) : '' ?></span>

				<a href="javascript:;" onclick="Boxy.load('/vitals-bsa-new.php?type=bsa&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>

		<div id="spo2" class="shadow"></div>
		<div id="spo2Last" class="shadow absolutecenter">
			<div>SpO2 Reading: <h3><?php
					$spValue = end($SpO2s) ? end($SpO2s)->getValue() : null;
					if ($spValue) {
						?>
						<?= $spValue ?><?php } else {
						echo 'N/A';
					}
					?></h3>
				<span style="display: block;"><?= end($SpO2s) ? date("\\o\\n Y/m/d g:ia", strtotime(end($SpO2s)->getReadDate())) : '' ?></span>

				<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=spo2&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
					New Reading</a>
			</div>
		</div>
		
		<?php  if($clinic === "General") { ?>
			<div id="fundusheightgraph" class="shadow"></div>
			<div id="fundusHeightLast" class="shadow absolutecenter">
				<div>Last Fundus Height Reading: <h3><?php
						$fhValue = end($FundusHeights) ? end($FundusHeights)->getValue() : null;
						echo ($fhValue) ? $fhValue . 'cm' : 'N/A';
						?></h3>
					<span style="display: block;"><?= end($FundusHeights) ? date("\\o\\n Y/m/d g:ia", strtotime(end($FundusHeights)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=fundus_height&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="glucosegraph" class="shadow"></div>
			<div id="glucoseLast" class="shadow absolutecenter">
				<div>Last Glucose Reading: <h3><?php
						$glucoseValue = end($Glucoses) ? end($Glucoses)->getValue() : null;
						if ($glucoseValue) {
							$abnormal = (floatval($glucoseValue) >= $vitalsRange::$normalGlucose) ? ' class="abnormal"' : ''; ?>
							<span<?= $abnormal ?>><?= $glucoseValue ?></span><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($Glucoses) ? date("\\o\\n Y/m/d g:ia", strtotime(end($Glucoses)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=glucose&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="proteingraph" class="shadow"></div>
			<div id="proteinLast" class="shadow absolutecenter">
				<div>Last Protein Reading: <h3><?php
						$proteinVal = end($Proteins) ? end($Proteins)->getValue() : null;
						if ($proteinVal) {
							$abnormal = (floatval($proteinVal) > $vitalsRange::$normalProtein) ? ' class="abnormal"' : ''; ?>
							<span<?= $abnormal ?>><?= $proteinVal ?></span><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($Proteins) ? date("\\o\\n Y/m/d g:ia", strtotime(end($Proteins)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=protein&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="mid-arm-circum-graph" class="shadow"></div>
			<div id="midArmLast" class="shadow absolutecenter">
				<div>Last Mid-Arm Circumference Reading: <h3><?php
						$laValue = end($MACs) ? end($MACs)->getValue() : null;
						if ($laValue) {
							?>
							<?= $laValue ?><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($MACs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($MACs)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=mid-arm-circumference&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="head-circum-graph" class="shadow"></div>
			<div id="headCircLast" class="shadow absolutecenter">
				<div>Last Head Circumference Reading: <h3><?php
						$chValue = end($HCs) ? end($HCs)->getValue() : null;
						if ($chValue) {
							?>
							<?= $chValue ?><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($HCs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($HCs)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=head-circumference&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="arm-length-graph" class="shadow"></div>
			<div id="armLengthLast" class="shadow absolutecenter">
				<div>Last Length of Arm Reading: <h3><?php
						$laValue = end($LAs) ? end($LAs)->getValue() : null;
						if ($laValue) {
							?>
							<?= $laValue ?><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($LAs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($LAs)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=length-of-arm&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>

			<div id="pcv" class="shadow"></div>
			<div id="pcvLast" class="shadow absolutecenter">
				<div>PCV Reading: <h3><?php
						$pcvValue = end($PCVs) ? end($PCVs)->getValue() : null;
						if ($pcvValue) {
							?>
							<?= $pcvValue ?><?php } else {
							echo 'N/A';
						}
						?></h3>
					<span style="display: block;"><?= end($PCVs) ? date("\\o\\n Y/m/d g:ia", strtotime(end($PCVs)->getReadDate())) : '' ?></span>

					<a href="javascript:void(0)" onClick="Boxy.load('/vitals-all-new.php?type=pcv&id=<?= $_GET['id'] ?><?= isset($_GET['aid']) ? '&aid=' . $_GET['aid'] : '' ?>', {title: 'New Reading'})">Take
						New Reading</a>
				</div>
			</div>
		<?php } ?>

	</div>
	<script type="text/javascript">
		Highcharts.createElement('link', {
			href: 'https://fonts.googleapis.com/css?family=Roboto:400,600',
			rel: 'stylesheet',
			type: 'text/css'
		}, null, document.getElementsByTagName('head')[0]);

		Highcharts.theme = {
			colors: ["#7cb5ec", "#f7a35c", "#90ee7e", "#7798BF", "#aaeeee", "#ff0066", "#eeaaee",
				"#55BF3B", "#DF5353", "#7798BF", "#aaeeee"],
			chart: {
				backgroundColor: null,
				style: {
					fontFamily: "Roboto, sans-serif"
				}
			},
			title: {
				style: {
					fontSize: '16px',
					fontWeight: 'bold',
					textTransform: 'uppercase'
				}
			},
			tooltip: {
				borderWidth: 0,
				backgroundColor: 'rgba(219,219,216,0.8)',
				shadow: false
			},
			legend: {
				itemStyle: {
					fontWeight: 'bold',
					fontSize: '13px'
				}
			},
			/*xAxis: {
				gridLineWidth: 0,
				labels: {
					style: {
						fontSize: '12px'
					}
				}
			},*/
			/*yAxis: {
				minorTickInterval: 'auto',
				title: {
					style: {
						textTransform: 'uppercase'
					}
				},
				labels: {
					style: {
						fontSize: '12px'
					}
				}
			},*/
			plotOptions: {
				candlestick: {
					lineColor: '#404048'
				},
				area: {
					fillColor: {
						linearGradient: {
							x1: 0,
							y1: 0,
							x2: 0,
							y2: 1
						},
						stops: [
							[0, Highcharts.getOptions().colors[0]],
							[1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
						]
					},
					marker: {
						radius: 2
					},
					lineWidth: 1,
					states: {
						hover: {
							lineWidth: 1
						}
					},
					threshold: null
				},
				spline: {
					lineWidth: 4,
					states: {
						hover: {
							lineWidth: 5
						}
					}
				}
			},


			// General
			background2: '#F0F0EA'

		};
		// Apply the theme
		Highcharts.setOptions(Highcharts.theme);
		//pulse
		Highcharts.setOptions({credits: {enabled: false}});
		$(document).ready(function () {
			new Highcharts.Chart({
				chart: {
					type: 'area',
					renderTo: 'pulsegraph',
					width: 805,
					height: 195,
					zoomType: 'x'
				},
				title: {
					text: 'Patient\'s Pulse Rates'
				},
				subtitle: {
					text: document.ontouchstart === undefined ?
						'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
				},
				xAxis: {
					type: 'datetime',
					tickPixelInterval: 150
				},
				yAxis: {
					title: {
						text: 'Pulse Rate'
					}
				},
				//navigator: {enabled: false},
				//scrollbar: {enabled:false},
				series: [{
					name: 'Pulse Rate',
					data: [
						<?php foreach($pulse as $p){//$p=new VitalSign();?>
						[<?= $p->getUnixTime() ?>,<?= $p->getValue() ?>],
						<?php }?>
					]
				}]
			});

			new Highcharts.Chart({
				chart: {
					renderTo: 'bpgraph',
					type: 'spline',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195,
					zoomType: 'x'
				},
				title: {
					text: 'Patient\'s Blood Pressure'
				},subtitle: {
					text: document.ontouchstart === undefined ?
						'Click and drag in the plot area to zoom in' : 'Pinch the chart to zoom in'
				},
				xAxis: {
					type: 'datetime',
					tickPixelInterval: 100
				},
				yAxis: {
					title: {
						text: 'Blood Pressure'
					}
				},
				scrollbar: {enabled: false},
				series: [
					{
						name: 'Systolic',
						data: [
							<?php foreach ($bps as $bp) {
							//$bp=new VitalSign();
							$systolic = explode("/", $bp->getValue())[0];?>
							[<?= $bp->getUnixTime() ?>, <?= $systolic ?>],
							<?php }?>
						]
					}, {
						name: 'Diastolic',
						data: [
							<?php foreach ($bps as $bp) {
							//$bp=new VitalSign();
							$diastolic = (!is_null($bp->getValue())) ? explode("/", $bp->getValue())[1] : $bp->getValue();?>
							[<?= $bp->getUnixTime() ?>, <?= $diastolic ?>],
							<?php }?>
						]
					}]
			});
			//temperature

			new Highcharts.Chart({
				chart: {
					renderTo: 'tempgraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Body Temperature'
				},subtitle: {
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
						text: 'Temperature(°C)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Temperature',
					data: [<?php foreach ($temperatures as $temp) {//$temp=new VitalSign();?>
						[<?= $temp->getUnixTime() ?>,<?= $temp->getValue() ?>],
						<?php }?>
					]
				}]
			});
			
			$('#pain_scale').highcharts({
				chart: {
					zoomType: 'x',
					type: 'spline',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Pain Scale'
				},subtitle: {
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
						text: 'Pain Scale '
					},
					min: 0,
					max: 10
				},
				scrollbar: {enabled: false},
				legend: {enabled: false},
				series: [{
					name: 'Pain Scale',
					threshold : 3,
					color: 'orange',
					negativeColor: 'rgb(124, 181, 236)',
					data: [<?php foreach ($Pains as $p) {//$temp=new VitalSign();?>
						[<?= $p->getUnixTime() ?>,<?= $p->getValue() ?>],
						<?php }?>
					]
				},{
					name: 'Pain Scale',
					threshold : 5,
					color: 'red',
					negativeColor: 'transparent',
					data: [<?php foreach ($Pains as $p) {//$temp=new VitalSign();?>
						[<?= $p->getUnixTime() ?>,<?= $p->getValue() ?>],
						<?php }?>
					]
				}]
			});

			//weight
			window.chart4 = new Highcharts.Chart({
				chart: {
					renderTo: 'weightgraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Weight'
				},subtitle: {
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
						text: 'Weight(kg)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Weight',
					data: [<?php foreach($weights as $weight) {//$weight=new VitalSign(); ?>
						[<?= $weight->getUnixTime() ?>,<?= $weight->getValue() ?>],
						<?php }?>]
				}]
			});
			//height
			window.chart5 = new Highcharts.Chart({
				chart: {
					renderTo: 'heightgraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Height'
				},subtitle: {
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
						text: 'Height(m)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Height',
					data: [<?php foreach($heights as $height) {//$height=new VitalSign(); ?>
						[<?= $height->getUnixTime() ?>,<?= $height->getValue() ?>],
						<?php }?>]
				}]
			});
			//body mass index
			window.chart7 = new Highcharts.Chart({
				chart: {
					renderTo: 'bmigraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s BMI'
				},subtitle: {
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
						text: 'BMI'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'BMI',
					data: [<?php foreach($BMIs as $bmi) {//$bmi=new VitalSign() ?>
						[<?= $bmi->getUnixTime() ?>,<?= $bmi->getValue() ?>],
						<?php } ?>]
				}]
			});//body mass index
			window.chart7 = new Highcharts.Chart({
				chart: {
					renderTo: 'bmigraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s BMI'
				},subtitle: {
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
						text: 'BMI'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'BMI',
					data: [<?php foreach($BMIs as $bmi) {//$bmi=new VitalSign() ?>
						[<?= $bmi->getUnixTime() ?>,<?= $bmi->getValue() ?>],
						<?php } ?>]
				}]
			});
			//body surface area
			new Highcharts.Chart({
				chart: {
					renderTo: 'bsagraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s BSA'
				},subtitle: {
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
						text: 'BSA'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'BSA',
					data: [<?php foreach($BSAs as $bsa) {//$bmi=new VitalSign() ?>
						[<?= $bsa->getUnixTime() ?>,<?= $bsa->getValue() ?>],
						<?php } ?>]
				}]
			});
			//respiratory rate
			window.chart6 = new Highcharts.Chart({
				chart: {
					renderTo: 'respgraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Respiratory Rate'
				},subtitle: {
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
						text: 'Respiratory Rate'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'Respiratory Rate',
					data: [<?php foreach($RRs as $rr) { // $rr=new VitalSign(); ?>
						[<?=$rr->getUnixTime() ?>,<?= $rr->getValue()?>],
						<?php } ?>]
				}]
			});

			<?php if($clinic === "General"){ ?>
			//fundus height
			window.chart8 = new Highcharts.Chart({
				chart: {
					renderTo: 'fundusheightgraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Baby Fundus Height'
				},subtitle: {
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
						text: 'Fundus Height(cm)'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'Fundus Height',
					data: [<?php foreach($FundusHeights as $fh) { ?>[<?=$fh->getUnixTime() ?>,<?= $fh->getValue() ?>],
						<?php } ?>]
				}]
			});

			//glucose
			window.chart9 = new Highcharts.Chart({
				chart: {
					renderTo: 'glucosegraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Glucose'
				},subtitle: {
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
						text: 'Glucose'
					}
				},
				//navigator: {enabled: false},
				scrollbar: {enabled: false},
				series: [{
					name: 'Glucose',
					data: [<?php foreach($Glucoses as $gl) { ?>[<?=$gl->getUnixTime() ?>,<?= $gl->getValue() ?>],
						<?php } ?>]
				}]
			});

			//protein
			window.chart10 = new Highcharts.Chart({
				chart: {
					renderTo: 'proteingraph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Patient\'s Protein'
				},subtitle: {
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
						text: 'Protein'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Protein',
					data: [<?php foreach($Proteins as $gl) { ?>[<?=$gl->getUnixTime() ?>,<?= $gl->getValue() ?>],
						<?php } ?>]
				}]
			});
			//mid arm circumference
			new Highcharts.Chart({
				chart: {
					renderTo: 'mid-arm-circum-graph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Mid-arm Circumference'
				},subtitle: {
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
						text: 'Mid-arm Circumference (cm)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Mid-arm Circumference',
					data: [<?php foreach($MACs as $mac) {?>[<?= $mac->getUnixTime() ?>,<?= $mac->getValue() ?>],
						<?php } ?>]
				}]
			});

			new Highcharts.Chart({
				chart: {
					renderTo: 'head-circum-graph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Head Circumference'
				},subtitle: {
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
						text: 'Head Circumference (cm)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Head Circumference',
					data: [<?php foreach($HCs as $hc) { ?>[<?= $hc->getUnixTime() ?>,<?= $hc->getValue() ?>],
						<?php } ?>]
				}]
			});
			new Highcharts.Chart({
				chart: {
					renderTo: 'arm-length-graph',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'Length of Arm'
				},subtitle: {
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
						text: 'Length of Arm (cm)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'Length of Arm',
					data: [<?php foreach($LAs as $la) { ?>[<?=$la->getUnixTime() ?>,<?= $la->getValue() ?>],
						<?php } ?>]
				}]
			});

			new Highcharts.Chart({
				chart: {
					renderTo: 'pcv',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'PCV'
				},subtitle: {
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
						text: 'PCV (%)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'PCV',
					data: [<?php foreach($PCVs as $pcv) { ?>[<?=$pcv->getUnixTime() ?>,<?= $pcv->getValue() ?>],
						<?php } ?>]
				}]
			});
			<?php } ?>

			new Highcharts.Chart({
				chart: {
					renderTo: 'spo2',
					zoomType: 'x',
					type: 'area',
					ignoreHiddenSeries: true,
					width: 805,
					height: 195
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
					text: 'SpO2'
				},subtitle: {
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
						text: 'SpO2 (%)'
					}
				},
				scrollbar: {enabled: false},
				series: [{
					name: 'SpO2',
					data: [<?php foreach($SpO2s as $sp) { ?>[<?=$sp->getUnixTime() ?>,<?= $sp->getValue() ?>],
						<?php } ?>]
				}]
			});
		});
	</script>
	
<?php } else {
	echo $protect->ACCESS_DENIED;
}
