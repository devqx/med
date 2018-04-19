<?php 
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$visitreport = new Reports();
$hospital='Government';
if($_POST){
$start=$_POST['startdate'];
$end=$_POST['enddate'];?>
<table width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td align="left" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="5"><thead><tr class="fancy" ><th>Visits</th><th>Admissions</th><th>Discharges</th></tr></thead>
<tr><td align="right"><?php $staff= $visitreport->daterangevisits($hospital,$start,$end); echo $staff;?></td>
<td align="right"><?php $b= $visitreport-> alladmissionswithindate($hospital,$start,$end); echo $b;?></td>
<td align="right"><?php $c =  $visitreport->dayrangeadmissions($hospital,$start,$end,'discharged'); echo $c;?></td>
</tr></table></td>
<td>
<?php 
if(($staff+$b+$c)!=0){

include 'reportcharts/admissionvisitplot.php';
}
?></td></tr></table>
<?php exit; }?>

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

<div id="content" class="login">
<script type="text/javascript">

</script><br/>
<div class="reportTitle"><h2>ADMISSION &amp; VISITS</h2><h3>(by date range)</h3></div>
<?php if(!$_POST){ echo  '<strong>Visitors Between:</strong><hr>' ?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",mktime(0, 0, 0, date("m") , date("d")-2, date("Y")));?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit">Submit</button><hr>
			</form><?php }?>
<div id="ttty" align="center">
</div>

</div>
