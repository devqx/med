<script type="text/javascript" src="../../scripts/jquery-1.7.2.min.js"></script>
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
		text: 'Admissions and Visits'
			},
		    yAxis: {
		        title: {
		            text: 'Quantity'
		        }
		    },
<?php require_once '../../class.reports.php';
	$admvisitreport = new Reports();
 //$dat= $admvisitreport->alladmissionswithindate($hospital,$start,$end).",". $admvisitreport->dayrangeadmissions('eleme',$start,$end,'discharged').","
 //.$admvisitreport->daterangevisits($hospital,$start,$end);?>
			xAxis: {
				categories: ['Admitted','Discharged','Visited']
			},
			series: [{
			    name: 'Persons',			        
				data: [<?php echo $admvisitreport->alladmissionswithindate($hospital,$start,$end); ?>,<?php echo $admvisitreport->dayrangeadmissions('eleme',$start,$end,'discharged')?>,<?php echo $admvisitreport->daterangevisits($hospital,$start,$end);?>]
			}]
		});
	});
</script>
<div id="pulsegraph" style="width:250px;height:250px"></div>
		