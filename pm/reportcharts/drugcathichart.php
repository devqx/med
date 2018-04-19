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
			text: 'Drug Categories in Stock'
				},
			    yAxis: {
			        title: {
			            text: 'Quantity'
			        }
			    },
				xAxis: {
			categories: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql="SELECT drugs.*,drug_category.name as dname,sum(drugs.quantity) as dsum FROM drugs,drug_category WHERE drugs.cater_ID=drug_category.id GROUP BY drugs.cater_ID ORDER BY cater_ID";
$chk=mysql_query($sql,$dbconnection);$row = mysql_fetch_assoc($chk); do { echo "'".$row["dname"]."'" ?>,<?php }while($row = mysql_fetch_assoc($chk));?>]
		},
			    series: [{
			        name: 'Category',			        
					data: [<?php require "../../Connections/dbconnection.php";mysql_select_db($database_dbconnection, $dbconnection);
$sql="SELECT drugs.*,drug_category.name,sum(drugs.quantity) as dsum FROM drugs,drug_category WHERE drugs.cater_ID=drug_category.id GROUP BY drugs.cater_ID ORDER BY cater_ID";
$chk=mysql_query($sql,$dbconnection);$row = mysql_fetch_assoc($chk); do { echo $row["dsum"] ?>,<?php }while($row = mysql_fetch_assoc($chk));?>]
			    }]
			});
		});
</script>
<div id="pulsegraph" style="width:300px;height:300px"></div>