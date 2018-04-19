<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$revreport = new Reports();
if($_POST){
	
	$start=$_POST['startdate'];
	$end=$_POST['enddate'];

$hospital='Government';

?>

<table width="100%"><tr><td valign="top" width="50%"><?php if($_POST){ $bsource= $revreport->incomeByRevenueSource($hospital,$start,$end); if($bsource != 'No Revenue'){ echo $bsource;}} ?></td><td align="right">
<?php if($_POST){ 
if($bsource != 'No Revenue'){
	include 'reportcharts/billsourcechart.php';
}
}?>
</td></tr>
<tr><td colspan="2" style="border-bottom:1px solid #ccc; box-shadow:1px 1px #000"></td></tr>
<tr><td valign="top"><?php if($_POST){ echo $revreport->getDateDangeIncome('eleme',$start,$end,'paid');} ?></td><td align="right"><?php if($_POST){
	$start=$_POST['startdate'];
	$end=$_POST['enddate'];
	if($bsource != 'No Revenue'){
		include 'reportcharts/paidchart.php';}
		}?>
</td></tr>
</table>

<?php exit;}?>


<?php include("datecalendar.php");?>
<script type="text/javascript">
    jQuery(function() {
          jQuery("#startdate,#enddate").datepicker({ dateFormat: 'yy-mm-dd' });
    });
    function start(){
                    $('#inv').html("<em>loading ...</em>");}
    function finished(s) {
            $('#inv').html(s);
    }
</script>


<div id="content" class="login">
<br />
    <div class="reportTitle"><h2>REVENUE REPORT </h2><h3>(by bill source, paid and unpaid)</h3></div>
<div align="right" style="width:300px;height:12px">&nbsp;</div>
<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
 Starting:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
Ending:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
 
<button type="submit" name="submit">Submit</button><hr>
			</form><?php }?>
</head>
<div id="inv">
<table width="100%"><tr><td><?php if($_POST){ echo $revreport->incomeByRevenueSource($hospital,$start,$end);} ?></td><td align="right"><?php if($_POST){ include 'reportcharts/billsourcechart.php';}?>
</td></tr>
<tr><td><?php if($_POST){ echo $revreport->getDateDangeIncome($hospital,$start,$end,'paid');} ?></td><td align="right"><?php if($_POST){ include 'reportcharts/paidchart.php';}?>
</td></tr>
</table>




</div>
<?php ?>

</div>	