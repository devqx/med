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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Item.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemCategory.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroupData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGeneric.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Department.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/CostCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ServiceCenter.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGrpSc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGrpScDAO.php';


$pdo = (new MyDBConnector())->getPDO();
$pdo->beginTransaction();
date_default_timezone_set('UTC');
$Filepath = "CONSUMABLES.ods";
try{
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$Sheets = $Spreadsheet->Sheets();
	
	output("Processing item Categories");
	$Spreadsheet->ChangeSheet(3);
	foreach ($Spreadsheet as $Key => $Cells) {
		if ($Key !== 0) {
			$categori = trim($Cells[0]);
	
			if (!is_blank($categori)) {
				$categories = array_filter(explode(",", $categori));
				foreach ($categories as $category) {
					if ((new ItemCategoryDAO())->getOrCreate($category, $pdo) == null) {
						$pdo->rollBack();
						output("Can't process item categories: {$category}");
						exit();
					}
				}
			}
		}
	}
	output("DONE Creating Category");
	
	output("Processing Item Generic");
	$Spreadsheet->ChangeSheet(2)	;
	foreach ($Spreadsheet as $key => $cell){
		if($key != 0){
			output("outputsss: $key .  $cell[0]");
			$name = trim($cell[0]);
			$category = trim($cell[1]);
			$group = trim($cell[2]);
			$description = trim($cell[3]);
		if(!is_blank($name) && !is_blank($description) && !is_blank($group) && is_blank($category) ){
				$igen = new ItemGeneric();
				$igen->setName($name);
				$igen->setDescription($description);
				$igen->setCategory((new ItemCategoryDAO())->getOrCreate($category, $pdo));
				$gen_ = $igen->getOrCreate($pdo);
				error_log("geneic:".json_encode($gen_));
				if(!$gen_ == null){ // create groups
					//output("created generics:;;".json_encode($gen_));
					$grp = (new ItemGroupDAO())->getOrCreate($group, $group, $pdo);
					if (!$grp == null){
						//output("created group:::;".$grp);
						$grp_data = new ItemGroupData();
						$grp_data->setGeneric((new ItemGenericDAO())->get($gen_->getId(), $pdo));
						$grp_data->setGroup((new ItemGroupDAO())->getItemGroup($grp->getId(), $pdo));
						$gen_goup = $grp_data->getOrCreate($pdo);
						//output("created group data".$gen_goup);
						if( $gen_goup == null ){
						 $pdo->rollBack();
						 output('Error while creating item group data');
						 exit;
					 }
					}else{
						$pdo->rollBack();
						output('Error while creating item group');
						exit;
					}
				}else{
					$pdo->rollBack();
					output('Error while creating item generic');
					exit;
				}
			
			}
		}
	}
	output("Done Creating Item Generics:::::::::::::;;");
	
	output("Processing service centre"); // incluing cost_centre and department
	$Spreadsheet->ChangeSheet(4);
	foreach ($Spreadsheet as $key => $Cell){
		if ($key != 0){
	   $name = trim($Cell[0]);
	   $department = trim($Cell[1]);
	   $cost_centre = trim($Cell[2]);
	   $group = trim($Cell[3]);
	   if(!is_blank($name) && !is_blank($department) && !is_blank($cost_centre) && !is_blank($group)){
	      //get or create cost centre;
		   $costCent_ = new CostCenter();
		   $costCent_->setName($cost_centre);
		   $costCent_->setDescription($cost_centre);
		   $costCent_->setAnalyticalCode(0);
		   $cost_cent_ob = (new CostCenterDAO())->getOrCreate($costCent_, $pdo);
		   // get or create department
		   if(!$cost_cent_ob == null) {
			   $depts = new Department();
			   $depts->setName($department);
			   $depts->setCostCentre($cost_cent_ob);
			   $depts_obj = (new DepartmentDAO())->getOrCreate($depts, $pdo);
			   if (!$depts_obj == null){
			    // get or create service centre
				   $s_center = new ServiceCenter();
				   $s_center->setName($name);
				   $s_center->setCostCentre($cost_cent_ob);
				   $s_center->setDepartment($depts_obj);
				   $s_center->setType('Item');
				   $sc_obj = (new ServiceCenterDAO())->getOrCreate($s_center, $pdo);
				   $grp = (new ItemGroupDAO())->getOrCreate($group, $group, $pdo); // item group
	
				   if(!$sc_obj == null && !$grp == null){
				   	output("centre created and center group created:");
					   $grsc = new ItemGrpSc();
				   	 $grsc->setServiceCenter($sc_obj);
				   	 $grsc->setItemGroup($grp);
				   	 $grp_src = (new ItemGrpScDAO())->getOrCreate($grsc, $pdo);
				   	 output("created:;;".json_encode($grp_src));
				   	 if($grp_src == null){
				   	 	output('could not process group service center data');
					      $pdo->rollBack();
				   	 	exit;
				     }
				   }
				   //else{
				   //	output("could not create service service center and group");
					   //$pdo->rollBack();
					   //exit;
				   //}
	
			   }else{
			   	$pdo->rollBack();
			   	output("could not process department");
			   	exit;
			   }
		   }else{
		   	$pdo->rollBack();
		   	output("could not process cost centers");
		   	exit;
		   }
	
	   }
	}
	}
	output("DONE PROCESSING SERVICE CENTER");
	output("Processing Item ");
	$Spreadsheet->ChangeSheet(0)	;
	foreach ($Spreadsheet as $key => $cell){
		if($key != 0){
			$name = trim($cell[0]);
			$description = trim($cell[1]);
		  $geneic = trim($cell[2]);
		$erp_id = trim($cell[3]);
			$price = trim(parseNumber($cell[4]));
			if(!is_blank($name) && !is_blank($price) && !is_blank($description) && !is_blank($geneic)){
				$igen = new ItemGeneric();
				$igen->setName($geneic);
				$igen->setDescription($geneic);
				$igen->setCategory((new ItemCategoryDAO())->getOrCreate($geneic, $pdo));
				$gen_ = $igen->getOrCreate($pdo);
				$item_ = (new Item())->setName($name)->setDescription($description)->setErpProductId($erp_id)->setGeneric($gen_)->setBasePrice($price);
				if((new ItemDAO())->findItem($name, $pdo) == null) {
					$data = (new ItemDAO())->addItem($item_, $pdo);
					if ($data == null) {
						$pdo->rollBack();
						output('Could not create items sheet: ');
						exit;
					}
	
				}else{
					output($name." Found will not create again");
				}
			}
		}
	}
	
	$pdo->commit();
	output('Done creating consumables :::::::');
}catch (Exception $E){
	output('An exception occurred: ' . $E->getMessage());
	
}