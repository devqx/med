<?php $start='2011-01-01';
$end='2012-08-02';
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$admissionreport = new Reports();

if($_POST){

//echo $scheme;
 ?>
<table width="100%"><tr><td><?php ;?></td><td align="right">
</td></tr>
</table>
<?php
//echo $admissionreport->getInsuranceList($scheme);
echo $admissionreport->getInsuranceSchemeRevenue();
?>
<div align="center">

<?php exit;}?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
<title>Insurance Lists</title>
<?php include("datecalendar.php");?>
<script type="text/javascript">
jQuery(function() {
      jQuery("#startdate,#enddate").datepicker({ dateFormat: 'yy-mm-dd' });
});
function start(){
	$('#ttty').html("<em>loading ...</em>");}
function finished(s) {
	$('#ttty').html(s);
	$("#admgraph").attr("src","../reportcharts/admissionplot.php?startdate="+$("#startdate").val()+"&enddate="+$("#enddate").val());
}</script>


<!-- Begin Content -->
<div id="content" class="login">
<?php if(!$_POST){ echo  '<strong></strong><hr>' ?>
<form id="insure" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">

<button type="submit" name="submit">View Account Summary for Insurance Schemes</button>
<button type="button" onclick="location.href='/pm/reporting/'">Reports Home</button>
<hr>
			</form><?php }?>
<div id="ttty">
</div>

</div>