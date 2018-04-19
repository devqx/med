<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 8/31/17
 * Time: 12:52 PM
 */
header('Content-Type: text/plain');

function output($message){
	echo '---------------------------------'.PHP_EOL;
	echo $message.PHP_EOL;
	echo '---------------------------------'.PHP_EOL;
}

require $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/php-excel-reader/excel_reader2.php';
require $_SERVER['DOCUMENT_ROOT'] . '/libs/spreadsheet-reader-master/SpreadsheetReader.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');

$Filepath = "MedicPlus Kelina Schemes_NEW.ods";//$_GET['File'];

try {
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	foreach ($Sheets as $Index => $Name) {
		output("Processing Sheet: $Name");
		$scheme = (new InsuranceSchemeDAO())->findInsuranceScheme($Name, FALSE, $pdo);
		if($scheme  != null ){
			$Spreadsheet->ChangeSheet($Index);
			//for this scheme, delete all items
			(new InsuranceItemsCostDAO())->clearSchemeItems($scheme->getId(), $pdo);
			foreach ($Spreadsheet as $Key => $Cells) {
				$size = 0;
				if($Key == 0){ // headers or first line in the sheet
					$size = count($Cells);
				} else if($Key != 0){
					$itemCode = ($Cells[0]);
					$description = trim($Cells[2]);
					$sellingPrice = parseNumber(trim($Cells[3]));
					$followUp = trim(parseNumber($Cells[4]));
					$surgeon = trim(parseNumber($Cells[5]));
					$anaesthesia = trim(parseNumber($Cells[6]));
					$theatre = trim(parseNumber($Cells[7]));
					$type = trim($Cells[8]); //Primary or Secondary
					
					$capitation = isset($Cells[9]) && trim($Cells[9])=='Capitated' ? TRUE : FALSE;
					if (!is_blank($itemCode)) {
						$item = getItem($itemCode, $pdo);
						if ($item == null) {
							$pdo->rollBack();
							output("Failed to determine item with code: $itemCode found on Line $Key");
							exit;
						} else {
							if(is_blank($sellingPrice) || !is_real_number($sellingPrice)){
								$pdo->rollBack();
								output("Invalid Price: $sellingPrice for $description");
								exit;
							} else {
								// add the new items
								$insureIC = (new InsuranceItemsCost())
									->setItem(getItem($itemCode, $pdo))
									->setSellingPrice (parseNumber($sellingPrice))
									->setSurgeonPrice(parseNumber($surgeon))
									->setAnesthesiaPrice(parseNumber($anaesthesia))
									->setTheatrePrice(parseNumber($theatre))
									->setType($type == 'Primary' ? 'Primary' : 'Secondary')
									->setCapitated($capitation)
									->setInsuranceScheme($scheme)
									->setClinic(new Clinic(1));
								$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
							}
						}
					} else {
						/*$pdo->rollBack();
						output("Blank Item Code found on Line ".($Key+1));
						exit;*/
					}
				}
			}
			
			output("Done with $Name!");
		} else {
			$pdo->rollBack();
			output("No scheme with name: $Name; callback aborted!");
			exit();
		}
	}
	
} catch (Exception $E) {
	output('An exception occurred: '.$E->getMessage());
	exit();
}
$pdo->commit();
output('Data importation complete! ');
