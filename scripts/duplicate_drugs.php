<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/12/17
 * Time: 5:47 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugManufacturer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugManufacturerDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Drug.php';

$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();

function output($message)
{
    echo '---------------------------------' . PHP_EOL;
    echo $message . PHP_EOL;
    echo '---------------------------------' . PHP_EOL;
}

// get the last row from the drug generic table
$sql1 = $pdo->prepare("SELECT id, name FROM drug_generics ORDER BY id DESC LIMIT 1", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
$sql1->execute();
$row = $sql1->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_FIRST);
$last_id = $row[0];
$last_name = $row[1];
output("The last id from drug generics : $last_id : " . $last_name);
// duplicate

try {
    $sql2 = "INSERT INTO drug_generics(active, name, category_ids, service_centre_ids, who_cat_labels, body_systems_rel, weight, form, description, low_stock_level) SELECT active, name, category_ids, service_centre_ids, who_cat_labels, body_systems_rel, weight, form, description, low_stock_level  FROM drug_generics";
    $stmt = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt->execute();
    if ($stmt->rowCount() > 1) {
        // update where id is greater than $last_id
        output("Done duplicating generic ");
        output("Start updating process!");
        $sql3 = "UPDATE drug_generics SET active = FALSE,  name=CONCAT('=',name) WHERE id > $last_id";
        $stmt3 = $pdo->prepare($sql3, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
        $stmt3->execute();
        if ($stmt3->rowCount() > 1) {
            output("Done updating");
        }
    }
} catch (PDOException $e) {
    output("Error in processing this script");
    error_log($e);
}

/** CREATE MANY TO ONE DRUG TABLE, CREATE THE ITEM COST RELATIONS AS WELL,
 * First, we create sudo manufacturer (Garki Hospital) which will hold the new drugs created,
 */

try {
	//$m_id = null;
   // output("Create drug manufacturer in process -----------");
   // // create sudo manufacturer
	//   $exists = (new DrugManufacturerDAO())->getOrCreate('Garki Hospital');
	//   if(!$exists == null){
	//	   $m_id = $exists->getId();
	//   }
	
    // select the recently created generic id and name (where id > $last_id)

    $gen = "SELECT id, name from drug_generics WHERE id > $last_id"; //
    $stmt5 = $pdo->prepare($gen, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
    $stmt5->execute();
    while ($row = $stmt5->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
	    
        $Generic = (new DrugGenericDAO())->getGeneric($row['id'], FALSE, $pdo);
        if( !$Generic == null){
        $gene_name = $row['name'];
        try { // create first drug
            if ((new DrugDAO())->findDrugByProps($gene_name . ' (Branded)', $Generic, $pdo) == null) {
                $drug = (new Drug())->setName($gene_name . ' (Branded)')->setManufacturer((new DrugManufacturerDAO())->getOrCreate('Garki Hospital', $pdo))->setBasePrice(0.00)->setGeneric($Generic)->setErpProduct(NULL)->setStockUOM(NULL);
	
	            output("Creating ".$gene_name . "Branded");
                $d = (new DrugDAO())->addDrug($drug, $pdo);
                if ($d === null) {
                    $pdo->rollBack();
                    output("error in creating drug " . $drug->getName());
                    exit;
                }
            } else {
                output($gene_name . ' Branded' . " drug already exists ");
            }

        } catch (PDOException $e) {
            error_log($e);
        }

        // create second drug
        try {
            if ((new DrugDAO())->findDrugByProps( $gene_name . ' (Unbranded 1)', $Generic, $pdo) == null) {
                $drug1 = (new Drug())->setName($gene_name . ' (Unbranded 1)')->setManufacturer((new DrugManufacturerDAO())->getOrCreate('Garki Hospital', $pdo))->setBasePrice(0.00)->setGeneric($Generic)->setErpProduct(NULL)->setStockUOM(NULL);
                output("Creating ".$gene_name . "Unbranded 1");
                if (!(new DrugDAO())->addDrug($drug1, $pdo) === null) {
                    $pdo->rollBack();
                    output("error in creating drug " . $drug1->getName());
                    exit;
                }
            } else {
                output($gene_name . ' Unbranded 1' . " drug already exists ");
            }

        } catch (PDOException $e) {
            error_log($e);
        }
        // create third drug
        try {
            if ((new DrugDAO())->findDrugByProps($gene_name . ' (Unbranded 2)', $Generic, $pdo) == null) {
                $drug2 = (new Drug())->setName($gene_name . ' (Unbranded 2)')->setManufacturer((new DrugManufacturerDAO())->getOrCreate('Garki Hospital', $pdo))->setBasePrice(0.00)->setGeneric($Generic)->setErpProduct(NULL)->setStockUOM(NULL);
	             output("Creating ".$gene_name . "Unbranded 2");
	             $d2 = (new DrugDAO())->addDrug($drug2, $pdo);
                if ($d2 === null) {
                    $pdo->rollBack();
                    output("error in creating drug " . $drug2->getName());
                    exit;
                }
            } else {
                output($gene_name . ' Unbranded 2' . " drug already exists ");
            }

        } catch (PDOException $e) {
            error_log($e);
        }
	
        }
    }
    output("DONE!");
    $pdo->commit();
    exit;
	
} catch (PDOException $e) {
    output("Error encountered while creating drugs");
    error_log($e);
}