<?php include("datecalendar.php");?>


<div id="content" class="login">
<script type="text/javascript">
	function start(){
		$('#inv').html("<em>loading ...</em>");}
	function finished(s) {
		$('#inv').html(s);
	}
</script><br/>
    <div class="reportTitle"><h2>DRUG INVENTORY REPORT </h2><h3>(by category)</h3></div>
<p align="center">
<div id="inv" align="center"><?php $start='2011-01-01';
$end=date("Y-m-d",time());//'2012-08-02';
require '../../class.reports.php';
$invreport = new Reports();
$hospital="Government";
//sleep(2);
echo '<table><tr><td valign="top">';
$catrep= $invreport->inventoryreportbycategory($hospital);echo $catrep.'</td><td>';
if($catrep !='There are no Drugs in the inventory'){
	include '/pages/pm/reporting/reportcharts/drugcathichart.php';
 }
 echo '</td></tr></table>'?>
</div>


</div>
	