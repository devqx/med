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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DRT.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DRTDAO.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

date_default_timezone_set('UTC');
$Filepath = "DRG.ods";
try{
	
	$Spreadsheet = new SpreadsheetReader($Filepath);
		output("Processing Sheet: DRG");
		$Spreadsheet->ChangeSheet(0);
		foreach ($Spreadsheet as $key => $cell) {
			//output("Key header". $key);
			//if ($Key == 0) { // headers or first line in the sheet
			//	$size = count($cell);
			//	output("cell count". $size);
			//} else
			//if ($Key == 0) {
			//	output("output cell value ".$cell[0]);
				$name = trim($cell[0]);
				$description = trim($cell[1]);
				$price = trim(parseNumber($cell[2]));
				if (!is_blank($name) && !is_blank($description) && !is_blank($price)) {
					if ((new DRTDAO())->find($name, $pdo)[0] == null) {
						output($name . " Not found so could be processed");
						$drg = new DRT();
						$drg->setName($name);
						$drg->setDescription($description);
						$drg->setBasePrice($price);
						$drg->setCreateUser((new StaffDirectoryDAO())->getStaff(1, false, null));
						if ($drg->add($pdo) == null) {
							$pdo->rollBack();
							output('Error while creating drt');
							exit;
						}
					}else{
						output($name." Found will not create again");
					}
				}
		}
	
	$pdo->commit();
	output('Done creating DRT:::::::');
}catch (Exception $E){
	output('An exception occurred: ' . $E->getMessage());
	
}