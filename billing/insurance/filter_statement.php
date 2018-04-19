<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/CurrencyDAO.php';

$currency = (new CurrencyDAO())->getDefault();
$page = isset($_POST['page']) ? $_POST['page'] : 0;
$pageSize = isset($_POST['PageSize']) ? $_POST['PageSize'] : 20;
$sid 		=	$_POST['sid'];
$scheme = $_POST['schemeid'];
$from 		= 	$_POST['from'];
$to 		=	$_POST['to'];
$tType		=  $_POST['type'];
$provider = $_POST['provider_id'];
$claimed_state = $_POST['claimed_state'];
$patientId = !is_blank($_POST['patient_id']) ? " AND b.patient_id = " . $_POST['patient_id'] : "";
$sources = !is_blank($_POST['bill_source_ids']) ? " AND bill_source_id IN (". implode(", ", $_POST['bill_source_ids']) .")" : "";
$unclaimed = "";
if($_POST['claimed_state'] && $_POST['claimed_state'] == "claimed"){
	$unclaimed = "AND b.claimed=TRUE";
	
}else if ($_POST['claimed_state'] && $_POST['claimed_state'] == "unclaimed"){
	$unclaimed = "AND b.claimed=FALSE";
}
$extraFilter = "";
$schemeId = "";
$ic = "";
$iso = "";
$insurer_id = "";

if($scheme && $provider == ""){
	$schemeId = "b.billed_to=$scheme";
	$extraFilter = "LEFT JOIN insurance_schemes ic ON b.billed_to=ic.id";
	$ic = "ic.*,";
}else if ($scheme && $provider != ""){
	
	$schemeId = "b.billed_to=$scheme";
}
if($provider != NULL && $provider != "" && $provider != ""){
	$ic = "ic.*,";
	$iso = "iso.*,";
	if($schemeId == ""){
		$insurer_id = "iso.id=$provider";
	}else if ($schemeId != ""){
		$insurer_id = "AND iso.id=$provider";
	}
	$extraFilter ="LEFT JOIN insurance_schemes ic ON b.billed_to=ic.id LEFT JOIN insurance_owners iso ON ic.scheme_owner_id=iso.id";
}


$sql = "SELECT b.*, $ic $iso concat_ws(' ', pd.lname, pd.mname, pd.fname) AS fullname, concat_ws(' ', pd.lname, substr(pd.fname, 1, 1)) as shortname, pd.active FROM bills b LEFT JOIN patient_demograph pd ON pd.patient_ID=b.patient_id $extraFilter WHERE $schemeId $insurer_id AND cancelled_on IS NULL {$sources}{$patientId}{$unclaimed}";

if ($tType != "---" && $tType != "") {
	$sql .= " AND transaction_type IN ('".implode("','", $tType)."')";
}


if($from!=NULL && $to==NULL){
	$sql.=" AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE(NOW())";}
else if ($from==NULL && $to!=NULL){
	$sql.=" AND DATE(transaction_date) BETWEEN DATE(NOW()) AND DATE('$to')";
}else if($from!=NULL && $to!=NULL){
	$sql.=" AND DATE(transaction_date) BETWEEN DATE('$from') AND DATE('$to')";
}
$sql .= " ORDER BY patient_id, billed_to, transaction_date DESC";
$outstanding_total = 0;
require_once $_SERVER['DOCUMENT_ROOT'].'/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
$pdo = (new MyDBConnector())->getPDO();
$sid = trim ( escape($sid) );
$page = ($page > 0) ? $page : 0;
$offset = ($page > 0) ? $pageSize * $page : 0;
$sql .= " LIMIT $offset, $pageSize";
$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

?>
<table class="table table-striped table-bordered">
<thead><tr>
	<th>Bill Item Code</th>
	<th class="hide">Reference</th>
    <th>Patient</th>
	<th>Item</th>
	<th>Date</th>
	<th>Type</th>
	<th nowrap>Amount (<?= $currency ?>)</th>
	<th>Responsible</th>
	<th>Billed To</th>
	<th>Auth Code</th>
</tr></thead>
<!--if num_rows == 0-->
<?php if($stmt->rowCount()==0){ ?>
    <?php }else {
    	do { ?>
    <!-- else start repeat-->
        <tr>
            <td><?= $row['bill_id']; ?></td>
            <td class="hide"><?= $row['payment_reference']; ?></td>
            <td  nowrap><?= $row['patient_id'] != NULL && $row['active']==TRUE ? '<span title="'.$row['fullname'].'">'.$row['shortname'].'.</span>' : '' ?></td>
            <td><?= $row['description']; ?></td>
            <td><?= date("d/M/Y", strtotime($row['transaction_date'])); ?></td>
            <td><?= explode("-", strtoupper($row['transaction_type']))[0] ;?><?php if($row['transaction_type']=='debit'){?><a href="javascript:;" onclick="Print('receipt','<?=$row['bill_id']?>','copy')"><i class="icon-print"></i> Receipt</a>  <?php }?></td>
            <td class="amount"><?= number_format(abs($row['amount']), 2); ?></td>
			<td><?= ($row['receiver']=='')? '': (new StaffDirectoryDAO())->getStaff($row['receiver'])->getShortname(); ?></td>
	        <td><?= $row['scheme_name'] ? $row['scheme_name'] : "N/A" ?></td>
            <td><?= $row['auth_code']?></td>
        </tr>
    <!--end repeat-->
<?php } while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)); } ?>
</table>
<div style="margin-top:10px"><button type="button" class="btn" onclick="Print('stmnt')"><i class="icon-print"></i> PRINT STATEMENT</button><div>
