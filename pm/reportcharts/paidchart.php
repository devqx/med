
		
		
		<script type="text/javascript" src="../../scripts/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="../../scripts/highcharts-android.js"></script>
<script type="text/javascript"> 		
	var chart1;
		$(document).ready(function() {
			window.chart1 = new Highcharts.Chart({
			    chart: {
			        renderTo: 'paidgraph',
					marginLeft:50,
					marginBottom:20,
					marginRight:10,
					spacingLeft:1,
					spacingRight:2,
					type:'pie',
					borderRadius:2,
					borderWidth:1,
					ignoreHiddenSeries:true
			    },
			    title: {
			text: 'Paid & Unpaid Income'
				},
				tooltip: {
			formatter: function() {
				return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage*100)/100 +' %';
			}
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				dataLabels: {
					enabled: false
				},
				showInLegend: true
			}
		},
			    yAxis: {
			        title: {
			            text: 'Quantity'
			        }
			    },
				
			    series: [{
			        name: 'Category',			        
					data: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql1="SELECT  sum( x.`amount` ) AS totalpaid FROM `bills` x 
		WHERE x.paid ='t'  AND x.transaction_date BETWEEN '".$start."' AND '". $end."'";
		$sql2="SELECT  sum( x.`amount` ) AS totalunpaid FROM `bills` x 
		WHERE x.paid !='t'  AND x.transaction_date BETWEEN '".$start."' AND '". $end."'";
$rst = mysql_query($sql1,$dbconnection);
		
		$row = mysql_fetch_assoc($rst);
		$numrows=intval($row['totalpaid'],10);
		
		$rst2 = mysql_query($sql2,$dbconnection);
		$row2 = mysql_fetch_assoc($rst2);
		$numrows2=intval($row2['totalunpaid'],10);
 echo "['Paid',".$numrows."],['Unpaid',".$numrows2."]]"; ?>
			    }]
			});
		});
</script>
<div class="<?= $start ?>" id="paidgraph" style="width:300px;height:300px"></div>
		
		