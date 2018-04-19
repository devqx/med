<?php 
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$visitreport = new Reports();
if($_POST){
	$yr=$_POST['year'];
	$start=$yr."-01-01";
	$end=$yr."-12-31";

$hospital='Government';

?>
<div align="center"><?php 
$staff= $visitreport->daterangevisits($hospital,$start,$end);
if($staff != 0){
	include "reportcharts/visitshichart.php";
}
else{
	echo " <strong>No visits throughout this year</strong>";
}
?></div>
<?php exit;}?>


<?php include("datecalendar.php");?>
<script type="text/javascript">
    function start(){
            $('#rev').html("<em>loading ...</em>");}
    function finished(s) {
            $('#rev').html(s);
    }
</script>


<div id="content" class="login">
<script type="text/javascript">
</script><br/>

<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
<strong> Monthly Visits, Admissions and Discharges for year:</strong><hr/>
<!-- Starting:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
Ending:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/> -->
 <select name="year"> <?php for ($i=2010;$i<=2099;$i++){?><option value="<?php echo $i ?>"><?php echo $i ?></option><?php }?>
         </select>
<input type="submit" value="View Report" name="submit"/><hr>
			</form><?php }?>
<div id="rev" align="center">
</div>
</div>