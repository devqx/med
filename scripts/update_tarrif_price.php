<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/7/18
 * Time: 6:46 PM
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');

$Filepath = "updateTariff.ods";
// NOTE PLEASE GET INSURANCE SCHEME ID FROM insurance_scheme
try{
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	
	foreach ($Sheets as $Index => $Name){
		output("Processing Sheet: $Name");
		$Spreadsheet->ChangeSheet($Index);
		foreach ($Spreadsheet as $key => $Cell){
		  $size = 0;
		  if($key == 0){
		  	$size = count($Cell);
		  }else if($key != 0){
		  	$item_code = ($Cell[0]);
		  	$price = parseNumber(trim($Cell[1]));
		  	// check if item code exists
			  $item = (new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($item_code, $pdo);
			  if($item && $item != null){
			  	$Inc = new InsuranceItemsCost();
			  	$Inc->setInsuranceCode($item_code);
			  	$Inc->setSellingPrice($price);
			  	$Inc->setInsuranceScheme((new InsuranceSchemeDAO())->get($id, TRUE, $pdo));
			  	$Inc->setClinic(new Clinic(1));
			  	$obj = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($Inc, $pdo);
			  	if( $obj == null){
			  		output("Could not update tarriff insurance::::::: ". $Inc->getInsuranceCode());
			  	}
			  }else{
			  	output("Item Code: $item_code does not exists::::::::");
			  	exit();
			  }
		  }
		}
	}
	
}catch (PDOException $e){
	output('An exception occurred: ' . $E->getMessage());
	exit();
}
$pdo->commit();
output('Data importation complete! ');
