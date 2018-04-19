<script type="text/javascript" src="../../scripts/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../scripts/highcharts-android.js"></script>
<script type="text/javascript"> 		
	var chart1;
		$(document).ready(function() {
			window.chart1 = new Highcharts.Chart({
			    chart: {
			        renderTo: 'annualgraph',
					marginLeft:80,
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
			text: 'Annual Revenue analysis'
				},
			    yAxis: {
			        title: {
			            text: 'Amount'
			        }
			    },
				xAxis: {
			categories: [<?php $start1=$start;
			$i=0;
			//
			do { $end1=$revreport->getLastOfMonth($start1); echo "'".$revreport->getCurrentMonth($start1)."'";
				if($end1 > $end) $end1=$end; $start1= $revreport->addOneDay($end1); $i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>]
		},
			    series: [{
			        name: 'Month',			        
					data: [<?php $start1=$start;$i=0;
 do { $end1=$revreport->getLastOfMonth($start1);echo $revreport->monthlyrevenue($hospital,$start1, $end1);
 if($end1 > $end)$end1=$end;
 $start1= $revreport->addOneDay($end1);$i = $i + 1;?>,<?php }while($i<=12 && $end1 < $end);?>]
			    }]
			});
		});
</script>
<div id="annualgraph" style="width:700px;height:300px"></div>