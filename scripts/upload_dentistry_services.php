<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/4/18
 * Time: 12:35 PM
 */

header('Content-Type: text/plain');

function output($message)
{
	echo '---------------------------------' . PHP_EOL;
	echo $message . PHP_EOL;
	echo '---------------------------------' . PHP_EOL;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/SpreadsheetReader.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Dentistry.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DentistryCategory.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

date_default_timezone_set('UTC');
$filePath = 'DENTALSERVICES.ods';

try{
	$spreadSheet = new SpreadsheetReader($filePath);
	
	output("upload in process please wait.....");
	$spreadSheet->ChangeSheet(0);
	foreach ($spreadSheet as $key => $cell) {
		 output("Key::::: $key. $cell[0]");
		if($key != 0){
			$s_name = trim($cell[0]);
			$category = trim($cell[1]);
			$price = trim(parseNumber($cell[2]));
			if(!is_blank($s_name) && !is_blank($category)){
				$d_cat = new DentistryCategory();
				$d_cat->setName($category);
			  $dentistry = new Dentistry();
				$dentistry->setName($s_name);
				$dentistry->setCategory((new DentistryCategoryDAO())->getOrCreate($d_cat, $pdo));
				$dentistry->setBasePrice($price);
				output("creating");
				if((new DentistryDAO())->getOrCreate($dentistry, $pdo) == null){
					$pdo->rollBack();
					output("Could not create dentistry service...");
					exit;
				}
			}
		}
	}
	
$pdo->commit();
output("Dentistry service upload DONE!");
}catch (PDOException $e){
	$pdo->rollBack();
	output("Fatal error: An Exception occurred:: ".$e->getMessage());
}