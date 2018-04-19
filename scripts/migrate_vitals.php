<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 5/18/17
 * Time: 5:21 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
$pdo = (new MyDBConnector())->getPDO();
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
$pdo->beginTransaction();
$types = getTypeOptions('type', 'vital_sign', $pdo);
$sql = "INSERT INTO vital (`name`) VALUES ";
$sp = [];
foreach ($types as $type){
	$sp[] = "(". quote_esc_str($type) . ")";
}$sql .= implode(", ", $sp);

$stmt = $pdo->prepare($sql);//, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)
$stmt->execute();
$pdo->commit();
exit('Done. Run the `upgrade_vitals` sql file');