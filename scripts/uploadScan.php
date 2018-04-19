<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/15/17
 * Time: 12:09 PM
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ScanCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Scan.php';


$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');
$Filepath = "RADIOLOGY.ods";
try{
	
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	//foreach ($Sheets as $Index => $Name) {
	//	output("Processing Sheet: $Name");
	
		$Spreadsheet->ChangeSheet(0);
	output("processing category......");
	foreach ($Spreadsheet as $Key => $Cells) {
			//if ($Key !== 0) {
				$categories = $Cells[0];
				if (!is_blank($categories)) {
					$categories = array_filter(explode(",", $Cells[0]));
					foreach ($categories as $category) {
						if ((new ScanCategoryDAO())->getOrCreate(trim($category), $pdo) == null) {
							$pdo->rollBack();
							output("Can't process scan categories: {$category}");
							exit();
						}
					}
				}
			//}
		}
		output("Done processing category!");
		
		output("Processing Scan");
		$Spreadsheet->ChangeSheet(1);
		foreach ($Spreadsheet as $key => $cell) {
			//if ($key !== 0) {
			$name = trim($cell[0]);
			$category = trim($cell[1]);
			$price = trim(parseNumber($cell[2]));
			if (!is_blank($name) && !is_blank($price)) {
				$scans = new Scan();
				$scans->setName($name);
				$scans->setCategory((new ScanCategoryDAO())->getOrCreate($category, $pdo));
				$scans->setBasePrice($price);
				if ((new ScanDAO())->findScans($name, $pdo)[0] == null) {
					if ((new ScanDAO())->addScan($scans, $pdo) == null) {
						$pdo->rollBack();
						output('Error while creating scan');
						exit;
					}
					
				} else {
					output($name . " Found will not create again...");
				}
				
			}
		}
	
	$pdo->commit();
	output('Done creating scan:::::::');
	
}catch (Exception $E){
	$pdo->rollBack();
	output('An exception occurred: ' . $E->getMessage());
	exit;
}