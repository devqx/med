<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ItemDAO
 *
 * @author pauldic
 */
class ItemDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Item.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceBillableItem.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DispensedItems.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceBillableItemDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceItemsCostDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/BillSourceDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemCategoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ServiceCenterDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemBatchDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DispensedItemDAO.php';
			
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addItem($it, $pdo = null)
	{
		$gen_id = $it->getGeneric() ? $it->getGeneric()->getId() : 'NULL';
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$code = ("IT" . generateBillableItemCode('item', $pdo));
			$it->setCode($code);
			$erpProductID = !is_blank($it->getErpProductId()) ? $it->getErpProductId() : 'NULL';
			$sql = "INSERT INTO item (`name`, generic_id, billing_code, description, erp_product_id) VALUES " . "('" . $it->getName() . "',  $gen_id,  '" . $it->getCode() . "', '" . $it->getDescription() . "', '" . $erpProductID ."' )";
			error_log("sql".$sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$it->setId($pdo->lastInsertId());
			$insureBI = new InsuranceBillableItem();
			$insureBI->setItem($it);
			$insureBI->setItemDescription($it->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(11, $pdo));
			
			$clinic = new Clinic(1);
			$insureBI->setClinic($clinic);
			
			$insBI = (new InsuranceBillableItemDAO())->addInsuranceBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($it);
			$insureIC->setSellingPrice($it->getBasePrice());
			$insureIC->setInsuranceScheme(new InsuranceScheme(1));
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->addInsuranceItemsCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			if ($stmt->rowCount() > 0) {
				$pdo->commit();
			} else {
				$it = null;
				$pdo->rollBack();
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$it = null;
		} catch (Exception $e) {
			errorLog($e);
			$it = null;
		}
		
		return $it;
	}
	
	function getPrice($iid, $pid, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN item it ON it.billing_code = c.item_code WHERE it.id = $iid AND c.insurance_scheme_id = (SELECT insurance_scheme FROM insurance WHERE patient_id = '$pid')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			} else {
				$price = $this->getDefaultPrice($iid, $pdo);
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
		}
		return $price;
	}
	
	function getDefaultPrice($iid, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price FROM insurance_items_cost c LEFT JOIN item it ON it.billing_code = c.item_code WHERE it.id = $iid AND c.insurance_scheme_id = 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
		}
		return $price;
	}
	
	function getItemByServiceCenter($center_id, $pdo = null)
	{
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT it.* FROM item_group_sc LEFT JOIN item_group_data ON item_group_sc.group_id=item_group_data.generic_id LEFT JOIN item it ON item_group_data.generic_id=it.generic_id WHERE item_group_sc.service_center_id=$center_id AND it.id IS NOT NULL GROUP BY it.name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$items[] = $this->getItem($row['id']);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$items = [];
		}
		return $items;
	}
	
	function getItemsByGeneric($gid, $pdo = null)
	{
		$items = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item WHERE generic_id=$gid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item = $this->getItem($row['id'], $pdo);
				$items[] = $item;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$items = null;
		}
		return $items;
	}
	
	function find($filter, $pdo = null)
	{
		$pageSize = 50;
		$cats = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item WHERE `name` LIKE '%$filter%' ORDER BY `name`";
			
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cat = $this->getItem($row["id"], $pdo);
				$cats[] = $cat;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$cats = null;
		}
		return $cats;
	}
	
	function getItem($id, $pdo = null)
	{
		$item = new Item();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT d.*, ic.selling_price AS price FROM item d LEFT JOIN insurance_items_cost ic ON ic.item_code=d.billing_code WHERE d.id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item->setId($row["id"]);
				$item->setName($row["name"]);
				$item->setCode($row['billing_code']);
				$item->setErpProductId($row['erp_product_id']);
				$item->setDescription($row['description']);
				$item->setGeneric((new ItemGenericDAO())->get($row['generic_id'], $pdo));
				$item->setBasePrice($row['price']);
				return $item;
				
			} else {
				$item = null;
			}
			$stmt = null;
			
		} catch (PDOException $e) {
			errorLog($e);
			$item = null;
		}
	}
	
	//function getOrCreate($item, $pdo)
	//{
	//	try{
	//		$pdo = $pdo == null ? (new MyDBConnector)->getPDO() : $pdo;
	//		$item_ = $this->find($item->getName(), $pdo)[0];
	//		if(!$item_ == null){
	//			return $item_;
	//		}else{
	//			return $this->addItem($item, $pdo);
	//		}
	//	}catch (PDOException $e){
	//		errorLog($e);
	//		return null;
	//	}
	//
	//}
	
	function findItem($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item WHERE `name` LIKE '%$item%'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->getItem($row["id"], $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getItem_($id, $pdo = null)
	{
		$item = new Item();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT i.*, c.selling_price AS price FROM item i LEFT JOIN insurance_items_cost c ON i.billing_code=c.item_code WHERE i.id=" . $id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item->setId($row["id"]);
				$item->setName($row["name"]);
				$item->setCode($row['billing_code']);
				$item->setErpProductId($row['erp_product_id']);
				$item->setDescription($row['description']);
				$item->setGeneric((new ItemGenericDAO())->get($row['generic_id'], $pdo));
				$item->setBasePrice($row['price']);
				$item->setBatches((new ItemBatchDAO())->getBatchesByItem($row['id'], $pdo));
				return $item;
				
			} else {
				$item = null;
			}
			$stmt = null;
			
		} catch (PDOException $e) {
			errorLog($e);
			$item = null;
		}
		return $item;
	}
	
	function getItems($lastItemId = null, $pdo = null)
	{
		$items = array();
		$pageSize = 50;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($lastItemId !== null) {
				$sql = "SELECT * FROM item WHERE id BETWEEN " . ($lastItemId + 1) . " AND " . ($lastItemId + $pageSize) . " ORDER BY `name` LIMIT $pageSize";
			} else {
				$sql = "SELECT * FROM item ORDER BY `name`";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item = $this->getItem_($row['id'], $pdo);
				$items[] = $item;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$items = [];
		}
		return $items;
	}
	
	function getItemByCode($iCode, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM item WHERE billing_code='" . $iCode . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $this->getItem($row["id"], $pdo);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getItemsForProcedure($pid, $pdo = null)
	{
		$items = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT i.*, pit.quantity AS used FROM `item` i LEFT JOIN patient_procedure_items pit ON pit.item_id=i.id WHERE pit.patient_procedure_id=$pid";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$item = $this->getItem($row["id"], $pdo);
				$items[] = $item;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$items = [];
		}
		return $items;
	}
	
	
	function updateItem($d, $pdo = null)
	{
        $gen_id = $d->getGeneric() ? $d->getGeneric()->getId() : 'NULL';
        $basePrice = $d->getBasePrice() ? $d->getBasePrice() : 0;
        try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$erpProductID = !is_blank($d->getErpProductId()) ? quote_esc_str($d->getErpProductId()) : 'NULL';
			$name = quote_esc_str($d->getName());
			$sql = "UPDATE item SET `name` = $name, generic_id=$gen_id, description='" . $d->getDescription() . "', erp_product_id=$erpProductID WHERE id = " . $d->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$clinic = new Clinic(1);
			$insureBI = (new InsuranceBillableItemDAO())->getInsuranceBillableItemByCode($d->getCode(), true, $pdo);
			$insureBI->setItemDescription($d->getName());
			$insureBI->setItemGroupCategory((new BillSourceDAO())->findSourceById(11, $pdo));
			$insureBI->setClinic($clinic);
			$insBI = (new InsuranceBillableItemDAO())->updateBillableItem($insureBI, $pdo);
			if ($insBI == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			
			$insureIC = new InsuranceItemsCost();
			$insureIC->setItem($d);
			$insureIC->setSellingPrice($basePrice);
			$insureIC->setInsuranceScheme(new InsuranceScheme(1));
			$insureIC->setClinic($clinic);
			$insIC = (new InsuranceItemsCostDAO())->updateInsuranceItemCost($insureIC, $pdo);
			if ($insIC == null) {
				$pdo->rollBack();
				$stmt = null;
				return null;
			}
			$pdo->commit();
			$stmt = null;
		} catch (Exception $e) {
			errorLog($e);
			$d = null;
		}
		return $d;
	}
	
	function updateStock($d, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE item SET quantity=(quantity+" . $d->getQuantity() . ") WHERE id = " . $d->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			
			$stmt = null;
		} catch (Exception $e) {
			$status = false;
		}
		return $status;
	}
	
	
	function dispenseItem($item_, $qty, $batch, $pat, $type= "fill", $pdo = null)
	{
		$status = false;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//get the patient again, so that we go deeper
			$pat = (new PatientDemographDAO())->getPatient($pat->getId(), false, $pdo, null);
			$batch = (new ItemBatchDAO())->getBatch($batch->getId(), $pdo);
			if ($qty > $batch->getQuantity()) {
				$overflow = $batch->getQuantity() - $qty;
				$quantity = $batch->getQuantity();
			} else {
				$overflow = 0;
				$quantity = $qty;
			}
			$disp = (new DispensedItems())->setItem($item_)->setPatient($pat)->setQuantity($quantity)->setBatch($batch)->setUnfiiledQuantity($overflow)//make sure this batch is not expired during usage
			->setBilledTo($pat->getScheme())->setDispensedBy((new StaffDirectoryDAO())->getStaff($_SESSION["staffID"], false, $pdo))->setType($type);
			$item = (new DispensedItemDAO())->add($disp, $pdo);
			
			$stmt = null;
			if ($item !== null) {
				$status = true;
			}
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
		}
		return $status;
	}
	
	
}
