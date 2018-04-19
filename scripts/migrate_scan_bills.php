<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/19/17
 * Time: 11:11 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
// $pdo->beginTransaction();

$sql = "SELECT ps.* FROM patient_scan ps LEFT JOIN scan sc ON ps.scan_ids=sc.id WHERE ps.bill_line_id IS NULL ORDER BY RAND() LIMIT 5000";

$stmt = $pdo->prepare($sql);
$stmt->execute();
echo '<table border=1><tr><td>line Id#</td><td>PID</td><td>RQ Date</td><td>Item Code</td><td>Bill(s)</td></tr>';
while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
	//now search for any bill for this lab on this patient on the lab request day
	$requestDate = $row['request_date'];
	$patient = $row['patient_id'];
	$scan = (new ScanDAO())->getScan($row['scan_ids'], $pdo);
	$itemCode = $scan->getCode();
	
	$scanBillSql = "SELECT bill_id FROM bills WHERE (item_code='{$itemCode}') AND patient_id={$patient} AND bill_source_id=7 AND DATE_FORMAT(transaction_date, '%Y-%m-%d %H:%i')=DATE_FORMAT('{$requestDate}', '%Y-%m-%d %H:%i') AND transaction_type = 'credit'";//time comparison part
	echo '<tr><td>'.$row['id'].'</td><td>'.$patient.'</td><td>'.$requestDate.'</td><td>'.$itemCode.' ['.$scan->getName().']</td><td>--';
	$stmt2 = $pdo->prepare($scanBillSql);
	$stmt2->execute();
	
	while ($row2 = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0)) {
		$updateSql = "UPDATE patient_scan SET bill_line_id='". implode(",", array_map('intval', $row2)) ."' WHERE id={$row['id']}";
		$stmt3 = $pdo->prepare($updateSql);
		$stmt3->execute();
		echo implode(",", array_map('intval', $row2));
	}
	
	echo '</td></tr>';
}
?>
</table>
<script>
	window.onload = function(){
		setTimeout(function(){
			location.reload();
		},1000);
	}
</script>