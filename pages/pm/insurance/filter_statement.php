<?php 
sleep(1);
$pid 		=	$_POST['pid'];
$from 		= 	$_POST['from'];
$to 		=	$_POST['to'];
$tType		= 	$_POST['tType'];

$sql = "SELECT * FROM bills WHERE billed_to = $pid AND cancelled_on IS NULL ";

if($tType!="---"){
	$sql.=" AND transaction_type ='$tType'";
}

if($from!=NULL && $to==NULL){
	$sql.=" AND transaction_date BETWEEN '$from' AND NOW()";}
else if ($from==NULL && $to!=NULL){
	$sql.=" AND transaction_date BETWEEN NOW() AND '$to'";
}else if($from!=NULL && $to!=NULL){
	$sql.=" AND transaction_date BETWEEN '$from' AND '$to'";
}
$outstanding_total = 0;
require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
mysql_select_db ( $database_dbconnection, $dbconnection );
$pid = trim ( mysql_real_escape_string($pid) );
$result = mysql_query($sql,$dbconnection);
$row = mysql_fetch_assoc($result);?>
<tr>
	<td>Bill Item Code</td>
	<td>Item</td>
	<td>Transaction Date</td>
	<td>Transaction Type</td>
	<td>Amount (=N=)</td>
</tr>
<!--if num_rows == 0-->
<?php if(mysql_num_rows($result)==0){?>
   <tr>
      <td colspan="5"><em>No bill items matching the filter</em></td>
      <td>&nbsp;</td>
    </tr>
    <!--end if-->
    <?php }else {
    	do {?>
    <!-- else start repeat-->
    <tr>
      <td><?php echo $row['bill_id'];?></td>
      <td><?php echo $row['description'];?></td>
      <td><?php echo date("jS M, Y",strtotime($row['transaction_date']));?></td>
      <td><?php echo strtoupper($row['transaction_type']);?></td>
      <td><?php echo number_format(abs($row['amount']), 2);
      $outstanding_total = $row['amount']+$outstanding_total;?></td>
    </tr>
    <!--end repeat-->
<?php } while($row = mysql_fetch_assoc($result));
    }
?>
