<?php if($_POST){
//$start='2011-01-01';
//$end='2012-08-02';
require '../../class.reports.php';
$invreport = new Reports();
$hospital="Government";
$start=$_POST['startdate'];
$end=$_POST['enddate'];


?>
<br/>
<table width="100%"><tr><td><?php echo $invreport->drugexpiry($hospital,$start,$end);?></td><td align="right"><?php include '../reportcharts/admissionvisitplot.php';?>
</td></tr>
</table>
<?php exit;}?>
<?php if(!isset($_SESSION)){session_start();}?>
<?php include("../../protect.php");?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
<title>Check Drug's Expiry Date</title>
<?php include("datecalendar.php");?>
<script type="text/javascript">
function start(){
		$('#inv').html("<em>loading ...</em>");}
function finished(s) {
		$('#inv').html(s);
	}
</script>
</head>
<body>
<!-- Begin Header -->
<div id="header"> <a href="javascript:void(0)" id="logo"> </a> </div> 
<!-- End Header --> 
<!-- Begin Content -->
<div id="content" class="login">
<br/>
<?php if(!$_POST){?>
<form action="<? echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit"></button><hr></form><?php }?>
<div id="inv" align="center">
<?php 
?>
</div>
</div>
<?php include 'footer.php';?>
</body>
</html>