<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/24/17
 * Time: 5:02 PM
 */
$schemeSourceId = 8;
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
//:the ones to affect
$sql = "SELECT id FROM insurance_schemes WHERE ...";
$stmt = $pdo->prepare($sql);//, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
//$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
	$sql1 = "DELETE FROM insurance_items_cost WHERE insurance_scheme_id={$row['id']}";
	$stmt1 = $pdo->prepare($sql1, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt1->execute();
	
	$sql2 = "INSERT INTO insurance_items_cost (item_code, selling_price, followUpPrice, theatrePrice, anaesthesiaPrice, surgeonPrice, co_pay, insurance_scheme_id, insurance_code, type, capitated, hospid) SELECT item_code, selling_price, followUpPrice, theatrePrice, anaesthesiaPrice, surgeonPrice, co_pay, ".$row['id'].", insurance_code, type, capitated, 1 FROM insurance_items_cost WHERE insurance_scheme_id=$schemeSourceId";
	$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$stmt2->execute();
}
$pdo->commit();
exit('Done!');