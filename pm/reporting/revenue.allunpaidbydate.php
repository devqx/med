<?php if(!isset($_SESSION)){session_start();}?>
<?php include("../../protect.php");?>
<?php $start='2011-01-01';
$end='2012-08-02';
require '../../class.reports.php';
$revreport = new Reports();
if($_POST){
$start=$_POST['startdate'];
$end=$_POST['enddate'];
echo $revreport->getDateDangeIncome('eleme',$start,$end,'unpaid');
?>
<img align="center" id="paidgraph" src="../reportcharts/paidchart.php?startdate=<?php echo $start;?>&enddate=<?php echo $end;?>" width="320" border="1"/>
<img align="center" id="billsourcegraph" src="../reportcharts/billsourcechart.php?startdate=<?php echo $start;?>&enddate=<?php echo $end;?>" width="360" border="1"/>
</div>
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
	$('#rev').html("<em>loading ...</em>");}
function finished(s) {
	$('#rev').html(s);
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
<strong> ALL UNPAID INCOME BETWEEN:</strong><hr/>

<?php if(!$_POST){?>
<form action="<? echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit"></button><hr></form><?php }?>
<div id="rev">
</div>

</div>
<?php include 'footer.php';?>
</body>
</html>	