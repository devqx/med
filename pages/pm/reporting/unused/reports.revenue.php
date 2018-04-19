<?php include("/protect.php");?>
<?php $start='1971-01-01';//set to start of time
$end=date("Y-m-d",time());
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$revreport = new Reports();
if($_POST){
$start=$_POST['startdate'];
$end=$_POST['enddate'];
//echo $revreport->getDateDangeIncome('eleme',$start,$end,'paid');
?>
<?php //include '../reportcharts/billsourcechart.php';?>
<table width="100%"><tr><td valign="top" width="50%"><?php if($_POST){ $income= $revreport->getDateDangeIncome('eleme',$start,$end,'paid');echo $income;} ?></td><td align="right">
<?php 
if($income != "No Income within date"){
include "/report/reportcharts/paidchart.php";}?>
</td></tr>

</td></tr>
</table>
<?php exit; }?>

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
    <div class="reportTitle"><h2>REVENUE REPORT </h2><h3>(paid and unpaid, by date)</h3></div>
<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit">Submit</button><hr></form><?php }?>
<div id="rev">
</div>
</div>
