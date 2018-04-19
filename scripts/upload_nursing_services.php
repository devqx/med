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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/NursingService.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/NursingServiceDAO.php';


$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');

$Filepath = "NURSINGSERVICE.ods";
try{
	
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	$Spreadsheet->ChangeSheet(0)	;
	foreach ($Spreadsheet as $key => $cell){
		//if($key !== 0){
			$name = trim($cell[0]);
			$price = trim(parseNumber($cell[1]));
			if(!is_blank($name)  && !is_blank($price)){
				if( (new NursingServiceDAO())->find($name, $pdo) == null){
					$nService = (new NursingService())->setName($name)->setBasePrice($price);
					if((new NursingServiceDAO())->add($nService, $pdo) == null){
						$pdo->rollBack();
						output('Error while creating nursing service');
						exit;
					}
				}else{
					output($name. ' already exists in the nursing service object');
				}
			}
		//}
	}
	$pdo->commit();
	output('Done creating nursing service:::::::');
	
}catch (Exception $E){
	output('An exception occurred: ' . $E->getMessage());
	
}