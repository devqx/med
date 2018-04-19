<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/14/17
 * Time: 1:29 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
//$pdo->beginTransaction();
$billsSql = " SELECT * FROM bills WHERE item_code IS NULL AND bill_source_id=1 AND transaction_type = 'credit'";
$stmt = $pdo->prepare($billsSql);
$stmt->execute();

echo "Working on ".$stmt->rowCount() . " records...";

?>
<table><thead><tr><th>Description</th><th>Code</th></tr></thead>
	<?php
while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
	$labName = str_replace("Lab charges: ", "", $row['description']);
	$findLab = "SELECT * FROM labtests_config WHERE `name` LIKE '{$labName}%'";
	echo $findLab."<br>";
	
	$stmt2 = $pdo->prepare($findLab);
	$stmt2->execute();
	
	if($row2 = $stmt2->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
		if($stmt2->rowCount()==1){
			$sq = "UPDATE bills SET item_code='{$row2['billing_code']}', quantity=1 WHERE bill_id={$row['bill_id']} AND item_code IS NULL";
			$stmt3 = $pdo->prepare($sq);
			$stmt3->execute();
		}
	}
}