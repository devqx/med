<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DrugGenericDAO
 *
 * @author pauldic
 */
class DrugGenericDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugGeneric.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DrugCategory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBodySystemDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	
	function getGenerics($lastItemId = null, $active = true, $pdo = null)
	{
		$pageSize = 50;
		$gens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($lastItemId === null) {
				$sql = "SELECT * FROM drug_generics WHERE " . ($active ? " active IS TRUE" : "1") . " ORDER BY `name` ASC";
			} else {
				$sql = "SELECT * FROM drug_generics WHERE " . ($active ? " active IS TRUE AND " : "") . " id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " /*ORDER BY `name`*/ LIMIT $pageSize";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen = $this->getGeneric($row['id'], false, $pdo);
				$gens[] = $gen;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gens = null;
		}
		return $gens;
	}
	
	
	function getSlim($lastItemId = null, $active = true, $pdo = null)
	{
		$pageSize = 50;
		$gens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($lastItemId === null) {
				$sql = "SELECT * FROM drug_generics WHERE " . ($active ? " active IS TRUE" : "1") . " ORDER BY `name` ASC";
			} else {
				$sql = "SELECT * FROM drug_generics WHERE " . ($active ? " active IS TRUE AND " : "") . " id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " /*ORDER BY `name`*/ LIMIT $pageSize";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen = $this->getSlimOne($row['id'], false, $pdo);
				$gens[] = $gen;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gens = null;
		}
		return $gens;
	}
	
	function find($filter, $pdo = null)
	{
		$pageSize = 50;
		$gens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_generics WHERE `name` LIKE '%$filter%' ORDER BY `name` ASC";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen = $this->getGeneric($row['id'], false, $pdo);
				$gens[] = $gen;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gens = null;
		}
		return $gens;
	}
	
	function getGeneric($id, $getFull = true, $pdo = null)
	{
		$gen = new DrugGeneric();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_generics WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen->setId($row["id"]);
				$gen->setName($row["name"]);
				$gen->setLowStockLevel($row['low_stock_level']);
				
				$cat_ids = explode(",", $row['category_ids']);
				$drug_cat_s = array();
				foreach ($cat_ids as $cat_id) {
					if ($getFull) {
						$drug_cat_s[] = (new DrugCategoryDAO())->getCategory($cat_id, $pdo);
					} else {
						$cat = new DrugCategory();
						$cat->setId($cat_id);
						$drug_cat_s[] = $cat;
					}
				}
				$gen->setCategories($drug_cat_s);
				$bs = explode(",", $row['body_systems_rel']);
				$body_systems = array();
				foreach ($bs as $i => $b) {
					$body_systems[] = (new DrugBodySystemDAO())->getBodySystem($bs[$i], $pdo);
				}
				$gen->setBodySystems($body_systems);
				$gen->setForm($row['form']);
				$gen->setDescription($row['description']);
				$gen->setWeight($row['weight']);
				$gen->setActive((bool)$row['active']);
				$gen->setServiceCentreId(explode(',', $row['service_centre_ids']));
			} else {
				$gen = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gen = null;
		}
		return $gen;
	}
	
	function getSlimOne($id, $getFull = true, $pdo = null)
	{
		$gen = new DrugGeneric();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_generics WHERE id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen->setId($row["id"]);
				$gen->setName($row["name"]);
				$gen->setLowStockLevel($row['low_stock_level']);
				
				/*$cat_ids = explode(",", $row['category_ids']);
				$drug_cat_s = array();
				foreach($cat_ids as $cat_id){
						if($getFull){
								$drug_cat_s[] = (new DrugCategoryDAO())->getCategory($cat_id, $pdo);
						}else {
								$cat = new DrugCategory();
								$cat->setId($cat_id);
								$drug_cat_s[] = $cat;
						}
				}
				$gen->setCategories( $drug_cat_s );
				$bs = explode(",",$row['body_systems_rel']);
				$body_systems = array();
				foreach ($bs as $i=>$b) {
						$body_systems[] = (new DrugBodySystemDAO())->getBodySystem($bs[$i], $pdo);
				}
				$gen->setBodySystems( $body_systems );*/
				$gen->setForm($row['form']);
				$gen->setDescription($row['description']);
				$gen->setWeight($row['weight']);
				$gen->setActive((bool)$row['active']);
				$gen->setServiceCentreId(explode(',', $row['service_centre_ids']));
			} else {
				$gen = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gen = null;
		}
		return $gen;
	}
	
	function getByName($name, $pdo = null)
	{
		$gen = new DrugGeneric();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_generics WHERE `name`='$name'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$gen->setId($row["id"]);
				$gen->setName($row["name"]);
				$gen->setLowStockLevel($row['low_stock_level']);
				$gen->setForm($row['form']);
				$gen->setDescription($row['description']);
				$gen->setWeight($row['weight']);
				$gen->setActive((bool)$row['active']);
				$gen->setServiceCentreId(explode(',', $row['service_centre_ids']));
			} else {
				$gen = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$gen = null;
		}
		return $gen;
	}
	
	function all($pdo = null)
	{
		$centres = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM drug_generics";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$centres[] = $this->getGeneric($row['id'], $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		return $centres;
	}
	
	
	function addGeneric($generic, $pdo = null)
	{
		$service = $generic->getServiceCentreId() ? $generic->getServiceCentreId() : "NULL";
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO drug_generics SET `name` = '" . $generic->getName() . "', category_ids='" . implode(",", $generic->getCategories()) . "', service_centre_ids='" . implode(",", $service) . "',  body_systems_rel = '" . implode(",", $generic->getBodySystems()) . "', weight='" . $generic->getWeight() . "', form='" . $generic->getForm() . "', description='" . $generic->getDescription() . "', low_stock_level = '" . $generic->getLowStockLevel() . "'";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$generic->setId($pdo->lastInsertId());
			} else {
				$generic = null;
			}
			$stmt = null;
			return $generic;
		} catch (PDOException $e) {
			errorLog($e);	return null;
		}
	}
	
	function updateGeneric($generic, $pdo = null)
	{
		$active = var_export($generic->getActive(), true);
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE drug_generics SET active=$active, `name` = '" . $generic->getName() . "', category_ids='" . implode(",", $generic->getCategories()) . "', body_systems_rel = '" . implode(",", $generic->getBodySystems()) . "', weight='" . $generic->getWeight() . "', form='" . $generic->getForm() . "', description='" . $generic->getDescription() . "', low_stock_level = '" . $generic->getLowStockLevel() . "' WHERE id= " . $generic->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$stmt = null;
			return $generic;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function filterDrugGenerics($pharmacy, $pdo = null)
	{
		$data = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pharmacy_id = filter_var($pharmacy, FILTER_VALIDATE_INT);
			$sql = "SELECT * FROM drug_generics  WHERE FIND_IN_SET($pharmacy_id, service_centre_ids) AND active=TRUE ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->getSlimOne($row['id'], false, $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
	function getOrCreate($name, $form = null, $categories = [], $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$return = $this->getByName($name, $pdo);
			
			if ($return != null) {
				return $return;
			} else {
				error_log("Came to create Generic... $name...");
				$g = new DrugGeneric();
				$g->setName($name);
				$g->setForm($form);
				$categories_ = [];
				foreach ($categories as $category) {
					$categories_[] = $category->getId();
				}
				$g->setCategories(implode(",", $categories_));
				
				return $this->addGeneric($g, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
}
