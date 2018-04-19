<?php if(!isset($_SESSION)){session_start();}?>
<?php include("../../protect.php");?>
<?php 

require '../../class.reports.php';
$visitreport = new Reports();
$hospital='Government';
if($_POST){
$start=$_POST['startdate'];
$end=$_POST['enddate'];
echo $visitreport->daterangevisits($hospital,$start,$end);
exit; }
?>

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
}
</script>
</head>
<body>
<!-- Begin Header -->
<div id="header"> <a href="javascript:void(0)" id="logo"> </a> </div> 
<!-- End Header --> 
<!-- Begin Content -->
<div id="content" class="login">
<script type="text/javascript">

</script><br/>
    <div class="reportTitle"><h2>VISITS</h2><h3>(by date)</h3></div>
<?php if(!$_POST){ echo  '<strong>Visitors Between:</strong><hr>' ?>
<form action="<? echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit"></button><hr>
			</form><?php }?>
<div id="ttty" align="center">
</div>

</div>
<?php include 'footer.php';?>
</body>
</html>		