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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

date_default_timezone_set('UTC');
$Filepath = "CONSULTATION.ods";
try {
	
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	
	output("Processing Consultation");
	$Spreadsheet->ChangeSheet(0);
	foreach ($Spreadsheet as $key => $cell) {
		$name = trim($cell[0]);
		$basePrice = trim(parseNumber($cell[1]));
		$followUpPrice = trim(parseNumber($cell[2]));
		if (!is_blank($name) && (!is_blank($basePrice) || is_real_number($basePrice))) {
			if((new StaffSpecializationDAO())->find($name, $pdo) == null) {
				output("not found please co ahead");
				 $consult = new StaffSpecialization();
				 $consult->setName($name);
					if ((new StaffSpecializationDAO())->uploadConsultation($consult, $basePrice, $followUpPrice, $pdo) == null) {
						$pdo->rollBack();
						output('Error while creating staff specialization');
						exit;
					}
			   }else{
				output($name." Found will not create again");
			}
			   
		}
	}
	
	
	$pdo->commit();
	output('Done creating specialization:::::::');
} catch (Exception $E) {
	output('An exception occurred: ' . $E->getMessage());
	
}