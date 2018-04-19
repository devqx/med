<?php 

require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$visitreport = new Reports();

if($_POST){
$reportType=$_POST['tType'];
$scheme=$_POST['scheme'];
$start=$_POST['startdate']. " 00:00:00";
$end=$_POST['enddate']." 23:59:59";
echo $visitreport->getTransactionByDateForScheme($scheme,$reportType,$start,$end);
//echo $start.$end;
exit; }
?>


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
    <div class="reportTitle"><h2>Insurance reports</h2><h3>(by date)</h3></div>
<?php if(!$_POST){ echo  '<strong>Select an Insurance Scheme:</strong><hr>' ?>
<form action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
<select name="scheme" id="scheme"><option selected>---</option>
     <?php require  $_SERVER ['DOCUMENT_ROOT'] ."/Connections/dbconnection.php";
	mysql_select_db ( $database_dbconnection, $dbconnection );
	$sql = "SELECT * FROM insurance_programs";
	$chk = mysql_query ( $sql, $dbconnection );
	$row_data = mysql_fetch_assoc ( $chk );
	$retVal = "";
	do {
	$retVal .= '<option value="'.$row_data['scheme_id'].'">'.$row_data ['scheme_name'] . '</option>';
} while ( $row_data = mysql_fetch_assoc ( $chk ) );
echo $retVal;?>
    </select>
Start Date:<input type="text" style="width:200px" id="startdate" name="startdate" 	value="<?php echo date("Y-m-d",time());?>"/>
End Date:<input type="text" style="width:200px" id="enddate" name="enddate" value="<?php echo date("Y-m-d",time());?>"/>
<select name="tType" id="tType"><option selected>---</option>
      <option value="credit">Bills</option>
      <option value="debit">Payments</option>
      <option value="discount">Discounts</option>
      <option value="refund">Refunds</option>
    </select>
<button type="submit" name="submit">Go</button><hr>
			</form><?php }?>
<div id="ttty" align="center">
</div>

</div>