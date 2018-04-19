<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVitalPreferenceDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if ($this_user->hasRole($protect->doctor_role) || $this_user->hasRole($protect->nurse)) {
	$vitalTypes = json_decode(json_encode((new VitalDAO())->all(), JSON_PARTIAL_OUTPUT_ON_ERROR));
	$vitalOptions = array_col($vitalTypes, 'name');
	$patientOptions = (new PatientVitalPreferenceDAO())->forPatient($_GET['id']);
	$po = json_decode(json_encode($patientOptions));
	$o = array_col($po, 'type');
	$clinic = "General";
	
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/functions/func.php';
	?>
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
					$.post('/api/vital_preference.php', {action:'delete', type:$this.val(), pid:'<?=$_GET['id']?>'})
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
<?php } else {
	echo $protect->ACCESS_DENIED;
}
