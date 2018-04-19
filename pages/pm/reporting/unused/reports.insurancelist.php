<?php $start='2011-01-01';
$end='2012-08-02';
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.reports.php';
$admissionreport = new Reports();
if($_POST){
$scheme=$_POST['scheme'];
//echo $scheme;
 ?>
<table width="100%"><tr><td><?php ;?></td><td align="right">
</td></tr>
</table>
<?php
echo $admissionreport->getInsuranceList($scheme);
//echo $admissionreport->getInsuranceSchemeRevenue();
?>
<div align="center">

<?php exit;}?>
    
    
<?php include("datecalendar.php");?>
<script type="text/javascript">
jQuery(function() {
      jQuery("#startdate,#enddate").datepicker({ dateFormat: 'yy-mm-dd' });
});
function start(){
	$('#ttty').html("<em>loading ...</em>");}
function finished(s) {
	$('#ttty').html(s);
	$("#admgraph").attr("src","../reportcharts/admissionplot.php?startdate="+$("#startdate").val()+"&enddate="+$("#enddate").val());
}</script>

<div id="content" class="login">
<?php if(!$_POST){ echo  '<strong>Select an Insurance Scheme:</strong><hr>' ?>
<form id="listForm" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete' : finished})">
<select name="scheme" id="scheme" onchange="$('#listShowBtn').click()"><option selected>---</option>
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
<button type="submit" name="submit" id="listShowBtn">Show List</button>
<button type="button" onclick="location.href='/pm/reporting/';">Back to Report Home</button><hr>
			</form><?php }?>
<div id="ttty">
</div>

</div>