<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 9/11/17
 * Time: 9:13 AM
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');

$Filepath = "Pharmacy Inventory - ALEX - 6 September 2017.ods";

try {
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	$cfg = new MainConfig;
	
	//foreach ($Sheets as $Index => $Name) {
	//
	//if ($Index == 0) {
	//check if there are units of measure that we don't have but are in this sheet
	//output("Processing Stock Units of measure");
	$Spreadsheet->ChangeSheet(0);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key !== 0) {
			// skip first row which might be the header
			$uom = $Cells[1];
			//output($uom);
			if (!is_blank($uom)) {
				if (!in_array(strtolower($uom), array_map("strtolower", $cfg::$drug_stock_uom))) {
					error_log(json_encode($cfg::$drug_stock_uom));
					output("$uom is not in the configured Drugs UOM... at Line $Key, ");
					$pdo->rollBack();
					exit();
				}
			}
		}
	}
	
	
	output("Processing Manufacturers");
	$Spreadsheet->ChangeSheet(3);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key !== 0) {
			$manufacturer = $Cells[1];
			if (!is_blank($manufacturer)) {
				if ((new DrugManufacturerDAO())->getOrCreate($manufacturer, $pdo) == null) {
					$pdo->rollBack();
					output("Can't process manufacturer: $manufacturer");
					exit();
				}
			}
		}
	}
	
	
	output("Processing Categories");
	$Spreadsheet->ChangeSheet(2);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key !== 0) {
			$categories = $Cells[1];
			if (!is_blank($categories)) {
				$categories = array_filter(explode(",", $Cells[1]));
				
				foreach ($categories as $category) {
					if ((new DrugCategoryDAO())->getOrCreate(trim($category), $pdo) == null) {
						$pdo->rollBack();
						output("Can't process drug categories: {$category}");
						exit();
					}
				}
			}
		}
	}
	
	output("Processing Inventory");
	$Spreadsheet->ChangeSheet(1);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key != 0) {
			//foreach ($Cells as $i => $cell) {
			//	if (is_blank($cell)) {
			//		$pdo->rollBack();
			//		output("Blank cell encountered at Line $Key, col $i in Inventory sheet ");
			//		exit;
			//	}
			//}
			$erpId = $Cells[0];
			$dName = $Cells[1];
			$manufacturer = $Cells[2];
			$genericName = $Cells[3];
			$genericCategories = $Cells[4];
			$genericForm = $Cells[5];
			$stockUOM = $Cells[6];
			$price = $Cells[9];
			
			
			
			if(!is_blank($dName) && !is_blank($manufacturer) && !is_blank($genericName) && !is_blank($genericCategories) && !is_blank($genericForm)){
				foreach ([1, 2, 3, /*4,*/ 5, 6, 9] as $item) {
					if (is_blank($Cells[$item])) {
						$pdo->rollBack();
						output("Blank cell encountered at Line ".($Key+1).", Col $item in Inventory sheet ");
						exit;
					}
				}
				
				if (!in_array(strtolower(trim($genericForm)), array_map("strtolower", $cfg::$drug_presentations))) {
					output("$genericForm is not available on the system. Please add it.");
					$pdo->rollBack();
					exit;
				}
				$genericCategories_ = [];
				foreach (array_filter(explode(",", $genericCategories)) as $cat) {
					$genericCategories_[] = (new DrugCategoryDAO())->getOrCreate($cat, $pdo);
				}
				
				$Generic = (new DrugGenericDAO())->getOrCreate($genericName, $genericForm, $genericCategories_, $pdo);
				
				if( (new DrugDAO())->findDrugByProps($dName, $Generic, $pdo) == null){
					//output("$dName not configured...");
					$drug = (new Drug())->setName($dName)->setErpProduct($erpId)->setManufacturer((new DrugManufacturerDAO())->getOrCreate($manufacturer, $pdo))->setGeneric($Generic)->setStockUOM($stockUOM)->setBasePrice($price);
					
					$d = (new DrugDAO())->addDrug($drug, $pdo);
					if ($d == null) {
						$pdo->rollBack();
						output("Failed to create drug $dName");
						exit;
					}
				}
			}
		}
	}
	
	output("Done processing Inventory!");
	$pdo->commit();
	exit;
	
} catch (Exception $E) {
	output('An exception occurred: ' . $E->getMessage());
	exit();
}
//$pdo->commit();
//output('Data importation complete! ');