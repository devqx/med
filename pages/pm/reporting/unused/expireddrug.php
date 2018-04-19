<?php include("datecalendar.php");?>



<div id="content" class="login">
<script type="text/javascript">
	function start(){
		$('#inv').html("<em>loading ...</em>");}
	function finished(s) {
		$('#inv').html(s);
	}
</script><br/>
<div class="reportTitle"><h2>DRUG INVENTORY REPORT </h2><h3>(expired drugs)</h3></div>
<div id="inv"><?php $start='2011-01-01';
//$end='2012-08-02';
$end=date("Y-m-d",time());//'2012-08-02';
require '../../class.reports.php';
$stockcontrol = new Reports();
$hospital="Government";
//sleep(2);
echo $stockcontrol->expiredDrugs($hospital,date("Y-m-d",time()));
echo '<hr/>';
//$criticalquantity=5;
//echo $stockcontrol->lowquantitystock($hospital,$criticalquantity);
?>
<center>

</div>
<?php ?>
</div>
