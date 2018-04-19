<?php if($_POST){
    //$start='2011-01-01';
    //$end='2012-08-02';
    require '/classes/class.reports.php';
    $invreport = new Reports();
    $hospital="Government";
    $start=$_POST['startdate'];
    $end=$_POST['enddate'];

?>
<br/>
<table width="100%"><tr><td><?php $expdrug= $invreport->drugexpiry($hospital,$start,$end);
if($expdrug =='No Data'){
echo '<table  width="100%" border="0" cellspacing="0" cellpadding="5"><tr><td><Strong>No Drugs expires within these dates</Strong></td></tr></table>';
}
?></td><td align="right">
<?php 
if($expdrug !='No Data'){
include '/pages/pm/reporting/reportcharts/inventorychartbyexpirydate.php';
}
?>
</td></tr>
</table>
<?php exit;}?>



<?php include("datecalendar.php");?>
<script type="text/javascript">
function start(){
		$('#inv').html("<em>loading ...</em>");}
function finished(s) {
		$('#inv').html(s);
	}
</script>


<div id="content" class="login">
<br/>
<div class="reportTitle"><h2>DRUG INVENTORY REPORT </h2><h3>(by expiry date)</h3></div>
<?php if(!$_POST){?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",mktime(0, 0, 0, date("m") , date("d")-1, date("Y")));?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<button type="submit" name="submit">Submit</button><hr></form><?php }?>
<div id="inv" align="center">
<?php 
?>
</div>
</div>
