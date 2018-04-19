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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Procedure.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ProcedureCategoryDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');
$Filepath = "PROCEDURE.ods";
try{
	
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	
	output("Processing procedure Categories");
	$Spreadsheet->ChangeSheet(0);
	foreach ($Spreadsheet as $Key => $Cells) {
		//if ($Key !== 0) {
			$categories = $Cells[0];
			if (!is_blank($categories)) {
				$categories = array_filter(explode(",", $Cells[0]));
	
				foreach ($categories as $category) {
					if ((new ProcedureCategoryDAO())->getOrCreate(trim($category), $pdo) == null) {
						$pdo->rollBack();
						output("Can't process procedure categories: {$category}");
						exit();
					}
				}
			}
		//}
		output("procedure category in process");
	}
	
	output("Processing Procedure");
	$Spreadsheet->ChangeSheet(1)	;
	foreach ($Spreadsheet as $key => $cell){
		//if($key !== 0){
			$name = trim($cell[0]);
			$category = trim($cell[3]);
			$description = trim($cell[1]);
			$surgicalPrice = trim(parseNumber($cell[6]));
			$scrubPrice = trim(parseNumber($cell[7]));
			$theatrePrice = trim(parseNumber($cell[4]));
			$anaesthestistPrice = trim(parseNumber($cell[5]));
			
			if(!is_blank($name) && !is_blank($description) && !is_blank($surgicalPrice)){
				$procedure = new Procedure();
				$procedure->setName($name);
				$procedure->setCategory((new ProcedureCategoryDAO())->getOrCreate($category, $pdo));
				$procedure->setDescription($description);
				$procedure->setBasePrice($surgicalPrice);
				$procedure->setPriceAnaesthesia($scrubPrice);
				$procedure->setPriceTheatre($theatrePrice);
				$procedure->setPriceSurgeon($anaesthestistPrice);
				if((new ProcedureDAO())->getOrCreate($procedure, $pdo) == null){
					$pdo->rollBack();
					output('Error while creating procedure');
					exit;
				}
			}
		//}
	}
	
	$pdo->commit();
	output('Done creating procedure:::::::');
}catch (Exception $E){
	output('An exception occurred: ' . $E->getMessage());
	
}