<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/28/17
 * Time: 12:37 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
// $pdo->beginTransaction();

$sql = "SELECT prd.*, pr.patient_id FROM patient_regimens_data prd LEFT JOIN drugs dr ON prd.drug_id=dr.id LEFT JOIN patient_regimens pr ON pr.group_code=prd.group_code WHERE /*prd.bill_line_id IS NULL AND */ prd.drug_id IS NOT NULL ORDER BY RAND() LIMIT 5000";

$stmt = $pdo->prepare($sql);
$stmt->execute();
echo '<table border=1><tr><td>line Id#</td><td>PID</td><td>RQ Date</td><td>Item Code</td><td>Bill(s)</td></tr>';
while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
	//now search for any bill for this lab on this patient on the lab request day
	$requestDate = $row['filled_on'];
	$patient = $row['patient_id'];
	$drug = (new DrugDAO())->getDrug($row['drug_id'], false, $pdo);
	$itemCode = $drug->getCode();
	
	$drugBillSql = "SELECT bill_id FROM bills WHERE (item_code='{$itemCode}') AND patient_id={$patient} AND bill_source_id=2 AND DATE_FORMAT(transaction_date, '%Y-%m-%d %H:%i')=DATE_FORMAT('{$requestDate}', '%Y-%m-%d %H:%i') AND transaction_type = 'credit'";//time comparison part
	echo '<tr><td>' . $row['id'] . '</td><td>' . $patient . '</td><td>' . $requestDate . '</td><td>' . $itemCode . ' [' . $drug->getName() . ']</td><td>--';
	$stmt2 = $pdo->prepare($drugBillSql);
	$stmt2->execute();
	
	while ($row2 = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0)) {
		$updateSql = "UPDATE patient_regimens_data SET bill_line_id='" . implode(",", array_map('intval', $row2)) . "' WHERE id={$row['id']}";
		$stmt3 = $pdo->prepare($updateSql);
		$stmt3->execute();
		echo implode(",", array_map('intval', $row2));
	}
	
	echo '</td></tr>';
}
?>
</table>
<script>
	window.onload = function () {
		setTimeout(function () {
			location.reload();
		}, 1000);
	}
</script>