<?php if(!isset($_SESSION)){session_start();}?>
<?php include("../../protect.php");?>
<?php $start='2011-01-01';
$end='2012-08-02';
require '../../class.reports.php';
$admissionreport = new Reports();
if($_POST){
$start=$_POST['startdate'];
$end=$_POST['enddate']; ?>
<table width="100%"><tr><td><?php ;?></td><td align="right"><?php include '../reportcharts/admissionvisitplot.php';?>
</td></tr>
</table>
<?php
//echo $admissionreport->dayrangeadmissions('eleme',$start,$end,'undischarged');
echo $admissionreport->dayrangeadmissions('eleme',$start,$end,'discharged');

?>
<div align="center">

<?php exit;}?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
<title>Practice Management</title>
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
</head>
<body>
<!-- Begin Header -->
<div id="header"> <a href="javascript:void(0)" id="logo"> </a> </div> 
<!-- End Header --> 
<!-- Begin Content -->
<div id="content" class="login">
<?php if(!$_POST){ echo  '<strong>Admitted Between:</strong><hr>' ?>
<form action="<? echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit"></button><hr>
			</form><?php }?>
<div id="ttty">
</div>

</div>
<?php include 'footer.php';?>
</body>
</html>		