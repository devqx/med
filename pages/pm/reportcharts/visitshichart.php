<script type="text/javascript" src="../../scripts/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../scripts/highcharts-android.js"></script>
<script type="text/javascript"> 
var chart;
$(document).ready(function() {
	window.chart = new Highcharts.Chart({
		chart: {
			renderTo: 'container',
			type: 'column'
		},
		title: {
			text: 'Monthly Visit,Admission and Discharge'
		},
		subtitle: {
			text: <?php echo "'For year: <strong>".$yr."</strong>'"; ?>
		},
		xAxis: {
			categories: [
				<?php $start1=$start;
			$i=0;
			//
			do { $end1=$visitreport->getLastOfMonth($start1); echo "'".$visitreport->getCurrentMonth($start1)."'";
				if($end1 > $end) $end1=$end; $start1= $visitreport->addOneDay($end1); $i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>
			]
		},
		yAxis: {
			min: 0,
			title: {
				text: 'No of Persons'
			}
		},
		legend: {
			layout: 'vertical',
			backgroundColor: '#FFFFFF',
			align: 'left',
			verticalAlign: 'top',
			x: 100,
			y: 100,
			floating: true,
			shadow: true
		},
		tooltip: {
			formatter: function() {
				return ''+
					this.x +': '+ this.y +' persons';
			}
		},
		plotOptions: {
			column: {
				pointPadding: 0.2,
				borderWidth: 0
			}
		},
			series: [{
			name: 'Visits',
			data: [<?php $start1=$start;$i=0;
 do { $end1=$visitreport->getLastOfMonth($start1);echo $visitreport->daterangevisits($hospital,$start1, $end1);
 if($end1 > $end)$end1=$end;
 $start1= $visitreport->addOneDay($end1);$i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>]

		}, {
			name: 'Admissions',
			data: [<?php $start1=$start;$i=0;
 do { $end1=$visitreport->getLastOfMonth($start1);echo $visitreport->alladmissionswithindate($hospital,$start1,$end1);
 if($end1 > $end)$end1=$end;
 $start1= $visitreport->addOneDay($end1);$i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>]

		}, {
			name: 'Discharges',
			data: [<?php $start1=$start;$i=0;
 do { $end1=$visitreport->getLastOfMonth($start1);echo $visitreport->dayrangeadmissions($hospital,$start1,$end1,'discharged');
 if($end1 > $end)$end1=$end;
 $start1= $visitreport->addOneDay($end1);$i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>]

		}]
	});
});
</script>
<div id="container" style="width:700px;height:300px"></div>