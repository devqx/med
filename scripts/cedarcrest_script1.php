<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/17/17
 * Time: 4:07 PM
 */

header('Content-Type: text/plain');

function output($message)
{
	echo '---------------------------------' . PHP_EOL;
	echo $message . PHP_EOL;
	echo '---------------------------------' . PHP_EOL;
}

require $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
require $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/SpreadsheetReader.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');

$Filepath = "SCHEME_EXPORTED.ods";
$schemes = [];
try {
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	$cfg = new MainConfig;
	
	$Spreadsheet->ChangeSheet(0);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key !== 0) {
			$schemeName = $Cells[0];
			$regPrice = parseNumber($Cells[1]);
			if (!is_blank($schemeName)) {
				$scheme = (new InsuranceSchemeDAO())->findInsuranceScheme($schemeName, false, $pdo);
				if ($scheme) {
					$schemes[]/*$schemeId*/ = $scheme->getId();
					//$sql = "UPDATE insurance_items_cost SET `type`='primary' WHERE insurance_scheme_id=$schemeId";
					//$sql2 = "UPDATE insurance_schemes SET reg_cost_company=$regPrice WHERE id=$schemeId";
					//$stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					//$stmt1->execute();
					// = $schemeId;
				} else {
					output("Scheme not found for ($schemeName)");
					$pdo->rollBack();
				}
			}
		}
	}
	
	$sql = "UPDATE insurance_items_cost SET `type`='primary' WHERE insurance_scheme_id IN (" . implode(",", $schemes) . ")";
	//$sql2 = "UPDATE insurance_schemes SET reg_cost_company=$regPrice WHERE id=$schemeId";
	$stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$ret = $stmt1->execute();
	if ($ret) {
		output("Done!");
		$pdo->commit();
	} else {
		output("FAILED!");
	}
	exit;
	
} catch (Exception $E) {
	output('An exception occurred: ' . $E->getMessage());
	exit();
}
//$pdo->commit();
//output('Data importation complete! ');