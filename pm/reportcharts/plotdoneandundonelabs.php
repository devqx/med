<script type="text/javascript" src="../../scripts/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../scripts/highcharts-android.js"></script>
<script type="text/javascript"> 		
	var chart1;
		$(document).ready(function() {
			window.chart1 = new Highcharts.Chart({
			    chart: {
			        renderTo: 'pulsegraph',
					marginLeft:50,
					marginBottom:20,
					marginRight:10,
					spacingLeft:1,
					spacingRight:2,
					type:'column',
					borderRadius:2,
					borderWidth:1,
					ignoreHiddenSeries:true
			    },
			    title: {
			text: 'Complete and Incomplete Labs'
				},
			    yAxis: {
			        title: {
			            text: 'Number '
			        }
			    },
					xAxis: {
			categories: ['Incomplete','Complete']
		},
			    series: [{
			        name: 'Frequency',			        
					data: [<?php require_once '../../Connections/dbconnection.php';

								//require_once '../../class.reports.php';
								$revreport = new Reports();
								//$hospital='eleme';
								//$start='2011-01-01';
								//$end = '2012-03-09';
					echo $revreport->getNoOfUndoneLabs($hospital,$start, $end);?>,
	<?php	echo $revreport->getNoOfDoneLabs($hospital,$start, $end);?>]
			    }]
			});
		});
</script>
<div id="pulsegraph" style="width:300px;height:300px"></div>