
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
			text: 'Drug\'s Expiry'
				},
			    yAxis: {
			        title: {
			            text: 'Quantity'
			        }
			    },
				xAxis: {
			categories: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql="SELECT   *  FROM drugs WHERE expiry_date BETWEEN '".$start."' AND '".$end."'"; 
$chk=mysql_query($sql,$dbconnection);$row = mysql_fetch_assoc($chk); do { echo "'".$row["drug_name"]."'" ?>,<?php }while($row = mysql_fetch_assoc($chk));?>]
		},
			    series: [{
			        name: 'Quantity',			        
					data: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql="SELECT   *  FROM drugs WHERE expiry_date BETWEEN '".$start."' AND '".$end."'"; 
$chk=mysql_query($sql,$dbconnection);$row = mysql_fetch_assoc($chk); do { echo $row["quantity"] ?>,<?php }while($row = mysql_fetch_assoc($chk));?>]
			    }]
			});
		});
</script>
<div id="pulsegraph" style="width:300px;height:300px"></div>
		