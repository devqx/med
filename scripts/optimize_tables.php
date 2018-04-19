<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/19/17
 * Time: 11:11 AM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
// $pdo->beginTransaction();

$sql = "show tables";

$stmt = $pdo->prepare($sql);
$stmt->execute();
echo '<table border=1><tr><td>Table</td><td>Status</td></tr>';
while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
	$tableName = $row[0];
	
	$Sql = "ANALYZE TABLE `{$tableName}`";//time comparison part
	$stmt2 = $pdo->prepare($Sql);
	$stmt2->execute();
	echo "<tr><td>{$tableName}</td><td>Optimized</td></tr>";
}
?>
</table>
<script>
	window.onload = function(){
		//setTimeout(function(){
			//location.reload();
		//},1000);
	}
</script>
