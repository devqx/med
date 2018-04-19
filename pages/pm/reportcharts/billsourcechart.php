
		<script type="text/javascript" src="../../scripts/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="../../scripts/highcharts-android.js"></script>
<script type="text/javascript"> 		
	var chart1;
		$(document).ready(function() {
			window.chart1 = new Highcharts.Chart({
			    chart: {
			        renderTo: 'sourcegraph',
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
			text: 'Revenue Sources'
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
					xAxis:{
					
					},
			    series: [{
			        name: 'Category',			        
					data: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql="SELECT bill_source,sum(amount) as dsum FROM bills WHERE DATE(transaction_date) BETWEEN '".$start."' AND '".$end."' GROUP BY bill_source";
$chk=mysql_query($sql,$dbconnection);$row = mysql_fetch_assoc($chk); do { echo "['".substr($row["bill_source"],0,4)."',". $row["dsum"]."]" ?>,<?php }while($row = mysql_fetch_assoc($chk));?>]
			    }]
			});
		});
</script>
<div class="<?php $start ?>"id="sourcegraph" style="width:300px;height:300px"></div>
		