<?php
$yrInstalled = 2010;
//TODO: find a way to keep something on disk or db 
//when this hospital app was installed, 
//then start tracking from there
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$revreport = new Reports();
if($_POST){
	$yr=$_POST['year'];
	$start=$yr."-01-01";
	$end=$yr."-12-31";
$hospital='Government';
//$nextday=$revreport->addOneMonth($start);
//$dif = $revreport->calculatedatedifference($start,$end);
//die('Next day: '.$nextday);

//if($dif < 365 || $dif > 366){
//die('<strong>This date range is less than or greater than one year:'.$dif.' days.</strong>');
//}

 //include '../reportcharts/billsourcechart.php';
?>
<div>

<?php 
if($_POST){ $yrincome= $revreport->incomeByRevenueSource($hospital,$start,$end);
if($yrincome != 'No Revenue'){
	include 'reportcharts/endofyrbarchart.php';
}
}?>

<br/>
<table width="100%"><tr><td><?php if($_POST){echo $yrincome;} ?></td><td align="right">
<?php if($_POST){ 
if($yrincome != 'No Revenue'){
	include 'reportcharts/billsourcechart.php';
}
}?>
</td></tr>
<tr><td><?php if($_POST){ echo $revreport->getDateDangeIncome('eleme',$start,$end,'paid');} ?></td><td align="right"><?php if($_POST){
if($yrincome != 'No Revenue'){	
	include 'reportcharts/paidchart.php';
}
}?>
</td></tr>
</table>

</div>
<?php exit;}?>


<script src="/js/webtoolkit.aim.js" type="text/javascript"></script>
<link href="/style/def.css" rel="stylesheet" type="text/css" media="screen" />
<link href="/style/pm.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript">
function start(){
	$('#rev').html("<em>loading ...</em>");}
function finished(s) {
	$('#rev').html(s);
}
</script>

<div id="content" class="login">
<br/>
<div class="reportTitle"><h2>REVENUE REPORT</h2><h3>(annual summary)</h3></div>
<br/>
    
<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
<strong> Annual Report Summary for the year:</strong><hr/>
<!-- Starting:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
Ending:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/> -->
 <select name="year"> <?php for ($i=date("Y",time());$i>=$yrInstalled;$i--){?><option value="<?php echo $i ?>"><?php echo $i ?></option><?php }?>
 </select>
<button type="submit" name="submit">View Report</button><hr>
			</form><?php }?>
<div id="rev" align="center">
</div>

</div>