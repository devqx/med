<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InsuranceItemsCostDAO
 *
 * @author pauldic
 */
class InsuranceItemsCostDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceItemsCost.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function addInsuranceItemsCost($inIC, $pdo = null)
	{
		 //$inIC = new InsuranceItemsCost();
		$type = $inIC->getType() ? quote_esc_str($inIC->getType()) : "'primary'";
		$capitated = $inIC->getCapitated() ? (bool)$inIC->getCapitated() : "false";
		$insure = $inIC->getInsuranceCode() ? $inIC->getInsuranceCode() : "NULL";
		$followup =  $inIC->getFollowUpPrice() != null ? $inIC->getFollowUpPrice() : 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO insurance_items_cost (item_code, selling_price, insurance_scheme_id, insurance_code, hospid, followUpPrice, theatrePrice, surgeonPrice, anaesthesiaPrice, capitated, `type` )  VALUES " . "('" . $inIC->getItem()->getCode() . "', " . quote_esc_str($inIC->getSellingPrice()) . ", " . $inIC->getInsuranceScheme()->getId() . ", $insure, " . $inIC->getClinic()->getId() . ", '". $followup ."', " . quote_esc_str(($inIC->getTheatrePrice() != null ? $inIC->getTheatrePrice() : 0)) . ", " . quote_esc_str(($inIC->getSurgeonPrice() != null ? $inIC->getSurgeonPrice() : 0)) . ", " . quote_esc_str(($inIC->getAnesthesiaPrice() != null ? $inIC->getAnesthesiaPrice() : 0)) . ", $capitated, $type )";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$inIC->setId($pdo->lastInsertId());
			} else {
				$inIC = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$inIC = null;
		} catch (Exception $e) {
			errorLog($e);
			$inIC = null;
		}
		return $inIC;
	}
	
	function addInsuranceItemsCosts($items)
	{
		try {
			$pdo = $this->conn->getPDO();
			$pdo->beginTransaction();
			foreach ($items as $it) {
				// $it = new InsuranceItemsCost();
				$sql = "INSERT INTO insurance_items_cost (item_code, selling_price, insurance_scheme_id, insurance_code, hospid, capitated, type)  VALUES " . "('" . $it->getItem()->getCode() . "', " . $it->getSellingPrice() . ", '" . $it->getInsuranceScheme()->getId() . "',  " . ($it->getInsuranceCode() ? quote_esc_str($it->getInsuranceCode()) : "NULL") . ",  " . $it->getClinic()->getId() . ", " . var_export($it->getCapitated(), true) . ", '" . ($it->getType()) . "')";
				// error_log($sql);
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
			}
			$pdo->commit();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$status = false;
		}
		return $status;
	}
	
	function updateInsuranceItemCostById($id, $obj, $pdo = null)
	{
		$status = false;
		$insuranceCode = $obj->insurance_code ? quote_esc_str($obj->insurance_code) : 'null';
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE insurance_items_cost SET selling_price={$obj->price['selling_price']}, followUpPrice={$obj->price['followUp']}, theatrePrice={$obj->price['theatre']}, anaesthesiaPrice={$obj->price['anaesthesia']}, surgeonPrice={$obj->price['surgeon']}, type=" . quote_esc_str($obj->type) . ", capitated=" . var_export($obj->capitated, true) . ", insurance_code=$insuranceCode, co_pay={$obj->co_pay} WHERE id=$id";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$status = true;
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			errorLog($e);
		} catch (Exception $e) {
			errorLog($e);
			$stmt = null;
		}
		return $status;
	}
	
	function updateInsuranceItemCost($inIC, $pdo = null)
	{
		//        $inIC = new InsuranceItemsCost();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($inIC instanceof stdClass) {
				$sql = "UPDATE insurance_items_cost SET selling_price = '" . $inIC->selling_price . "', followUpPrice='" . ($inIC->followUpPrice != null ? $inIC->followUpPrice : 0) . "', theatrePrice=" . ($inIC->theatrePrice != null ? $inIC->theatrePrice : 0) . ", anaesthesiaPrice=" . ($inIC->anaesthesiaPrice != null ? $inIC->anaesthesiaPrice : 0) . ", surgeonPrice=" . ($inIC->surgeonPrice != null ? $inIC->surgeonPrice : 0) . " WHERE item_code = '" . $inIC->item_code . "' AND insurance_scheme_id = " . $inIC->insurance_scheme_id . " AND hospid = " . $inIC->hospid;
			} else {
				$sql = "UPDATE insurance_items_cost SET selling_price = '" . $inIC->getSellingPrice() . "', followUpPrice='" . ($inIC->getFollowUpPrice() != null ? $inIC->getFollowUpPrice() : 0) . "', theatrePrice=" . ($inIC->getTheatrePrice() != null ? $inIC->getTheatrePrice() : 0) . ", anaesthesiaPrice=" . ($inIC->getAnesthesiaPrice() != null ? $inIC->getAnesthesiaPrice() : 0) . ", surgeonPrice=" . ($inIC->getSurgeonPrice() != null ? $inIC->getSurgeonPrice() : 0) . " WHERE item_code = '" . $inIC->getItem()->getCode() . "' AND insurance_scheme_id = " . $inIC->getInsuranceScheme()->getId() . " AND hospid = " . $inIC->getClinic()->getId();
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$stmt = null;
		} catch (PDOException $e) {
			$inIC = null;
			errorLog($e);
		} catch (Exception $e) {
			errorLog($e);
			$inIC = null;
		}
		return $inIC;
	}
	
	function removeItem($item, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM insurance_items_cost WHERE id = " . $item->getId(); // and insurance scheme? but the id is more direct
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return true;
			}
			return false;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getItemPriceByCode($code, $pid, $getDefault = true, $pdo = null)
	{
		
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT selling_price FROM insurance_items_cost c LEFT JOIN insurance i ON i.insurance_scheme=c.insurance_scheme_id WHERE c.item_code='" . $code . "' AND i.patient_id='" . $pid . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
				return $price;
			} else {
				if ($getDefault) {
					$price = $this->getItemDefaultPriceByCode($code, $pdo);
					return $price;
				} else {
					if (!$getDefault) {
						$price = null;
						return $price;
					}
				}
			}
			$stmt = null;
			return $price;
		} catch (PDOException $e) {
			$price = null;
			$stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getItemPricesByCode($code, $pid, $getDefault = true, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price, c.followUpPrice, c.theatrePrice, c.surgeonPrice, c.anaesthesiaPrice FROM insurance_items_cost c LEFT JOIN insurance i ON i.insurance_scheme=c.insurance_scheme_id WHERE c.item_code='" . $code . "' AND i.patient_id='" . $pid . "'";
			//  error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$price = (object)null;
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price->sellingPrice = $row['selling_price'];
				$price->followUpPrice = $row['followUpPrice'];
				$price->theatrePrice = $row['theatrePrice'];
				$price->surgeonPrice = $row['surgeonPrice'];
				$price->anaesthesiaPrice = $row['anaesthesiaPrice'];
			} else {
				//price if this item is not found under the patient insurance
				$price = $this->getItemDefaultPricesByCode($code, $getDefault, $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$price = null;
			$stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getItemDefaultPricesByCode($code, $getDefault = true, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.selling_price, c.followUpPrice, c.theatrePrice, c.surgeonPrice, c.anaesthesiaPrice FROM insurance_items_cost c LEFT JOIN insurance i ON i.insurance_scheme=c.insurance_scheme_id WHERE c.item_code='" . $code . "' AND c.insurance_scheme_id = 1";
			//            error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			$price = (object)null;
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price->sellingPrice = $row['selling_price'];
				$price->followUpPrice = $row['followUpPrice'];
				$price->theatrePrice = $row['theatrePrice'];
				$price->surgeonPrice = $row['surgeonPrice'];
				$price->anaesthesiaPrice = $row['anaesthesiaPrice'];
			}
			$stmt = null;
		} catch (PDOException $e) {
			$price = null;
			$stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getItemFollowUpPriceByCode($code, $pid, $getDefault = true, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT followUpPrice FROM insurance_items_cost c LEFT JOIN insurance i ON i.insurance_scheme=c.insurance_scheme_id WHERE c.item_code='" . $code . "' AND i.patient_id='" . $pid . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['followUpPrice'];
			} else {
				if ($getDefault) {
					$price = $this->getItemDefaultFollowUpPriceByCode($code, $pdo);
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$price = null;
			$stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getItemDefaultPriceByCode($code, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT selling_price FROM insurance_items_cost WHERE insurance_scheme_id=1 AND item_code='" . $code . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['selling_price'];
			} else {
				$price = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getItemDefaultFollowUpPriceByCode($code, $pdo = null)
	{
		$price = null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT followUpPrice FROM insurance_items_cost WHERE insurance_scheme_id=1 AND item_code='" . $code . "'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$price = $row['followUpPrice'];
			} else {
				$price = null;
			}
			
			$stmt = null;
		} catch (PDOException $e) {
			$price = $stmt = null;
			errorLog($e);
		}
		return $price;
	}
	
	function getInsuranceItemsCost($iicid, $getFull = false, $pdo = null)
	{
		$insIC = new InsuranceItemsCost();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_items_cost WHERE id=" . $iicid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$insIC->setId($row['id']);
				$insIC->setItem(getItem($row['item_code'], $pdo));
				$insIC->setSellingPrice($row['selling_price']);
				if ($getFull) {
					$insScheme = (new InsuranceSchemeDAO())->get($row['insurance_scheme_id'], false, $pdo);
					$clinic = (new ClinicDAO())->getClinic($row['hospid']);
				} else {
					$insScheme = new InsuranceScheme();
					$insScheme->setId($row['insurance_scheme_id']);
					$clinic = new Clinic();
					$clinic->setId($row['hospid']);
				}
				$insIC->setInsuranceScheme($insScheme);
				$insIC->setClinic($clinic);
				$insIC->setDefaultPrice($this->getItemDefaultPriceByCode($row['item_code'], $pdo));
				$insIC->setFollowUpPrice($row['followUpPrice']);
				$insIC->setSurgeonPrice($row['surgeonPrice']);
				$insIC->setAnesthesiaPrice($row['anaesthesiaPrice']);
				$insIC->setTheatrePrice($row['theatrePrice']);
				$insIC->setCapitated($row['capitated']);
				$insIC->setType($row['type']);
			} else {
				$insIC = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$insIC = null;
			errorLog($e);
		}
		return $insIC;
	}
	
	function getInsuranedItemCostByCode($code, $sid, $getDefaut = true, $getFull = false, $pdo = null)
	{
		$insIC = new InsuranceItemsCost();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM insurance_items_cost WHERE item_code='" . $code . "' AND insurance_scheme_id=" . $sid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (object)$row;
				/*$insIC->setId($row['id']);
				$insIC->setItem(getItem($row['item_code'], $pdo));
				$insIC->setSellingPrice($row['selling_price']);
				if ($getFull) {
						$insScheme = (new InsuranceSchemeDAO())->get($row['insurance_scheme_id'], FALSE, $pdo);
						$clinic = (new ClinicDAO())->getClinic($row['hospid'], TRUE, $pdo);
				} else {
						$insScheme = new InsuranceScheme();
						$insScheme->setId($row['insurance_scheme_id']);
						$clinic = new Clinic();
						$clinic->setId($row['hospid']);
				}
				$insIC->setInsuranceScheme($insScheme);
				$insIC->setClinic($clinic);
				$insIC->setDefaultPrice($this->getItemDefaultPriceByCode($row['item_code'], $pdo));
				$insIC->setFollowUpPrice($row['followUpPrice']);*/
			} else {
				if ($getDefaut) {
					$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
					$sql = "SELECT * FROM insurance_items_cost WHERE item_code='" . $code . "' AND insurance_scheme_id=1";
					//error_log($sql);
					$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
					$stmt->execute();
					if ($row2 = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
						return (object)$row2;
						/*$insIC->setId($row['id']);
						$insIC->setItem(getItem($row['item_code'], $pdo));
						$insIC->setSellingPrice($row['selling_price']);
						if ($getFull) {
							$insScheme = (new InsuranceSchemeDAO())->get($row['insurance_scheme_id'], FALSE, $pdo);
							$clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
						} else {
							$insScheme = new InsuranceScheme($row['insurance_scheme_id']);
							$clinic = new Clinic($row['hospid']);
						}
						$insIC->setInsuranceScheme($insScheme);
						$insIC->setClinic($clinic);
						$insIC->setDefaultPrice($this->getItemDefaultPriceByCode($row['item_code'], $pdo));
						$insIC->setFollowUpPrice($row['followUpPrice']);
						$insIC->setSurgeonPrice($row['surgeonPrice']);
						$insIC->setAnesthesiaPrice($row['anaesthesiaPrice']);
						$insIC->setTheatrePrice($row['theatrePrice']);*/
					}
				} else {
					$insIC = null;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$insIC = null;
			errorLog($e);
		}
		return $insIC;
	}
	
	function getInsuranceItem($code, $pid, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.*, b.item_description, sch.pay_type FROM insurance_items_cost c LEFT JOIN insurance_billable_items b ON b.item_code=c.item_code LEFT JOIN insurance i ON c.insurance_scheme_id=i.insurance_scheme LEFT JOIN insurance_schemes sch ON sch.id=c.insurance_scheme_id WHERE i.patient_id=$pid AND c.item_code='$code'";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (object)$row;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getInsuredItemsCostsByScheme($sid, $pdo = null)
	{
		$insICs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$scheme = (new InsuranceSchemeDAO())->get($sid, false, $pdo);
			$sql = "SELECT itc.*, ib.item_group_category_id, ib.item_description FROM insurance_items_cost itc LEFT JOIN insurance_billable_items ib ON ib.item_code=itc.item_code WHERE insurance_scheme_id=" . $sid . " ORDER BY item_code";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				/*$insIC = new InsuranceItemsCost();
				$insIC->setId($row['id']);
				$insIC->setItem(getItem($row['item_code'], $pdo));
				$insIC->setSellingPrice($row['selling_price']);
				$insIC->setInsuranceScheme($scheme);
				//save space
				//$insIC->setClinic($scheme->getHospital());
				$insIC->setDefaultPrice($this->getItemDefaultPriceByCode($row['item_code'], $pdo));
				$insIC->setFollowUpPrice($row['followUpPrice']);

				$insIC->setSurgeonPrice($row['surgeonPrice']);
				$insIC->setAnesthesiaPrice($row['anaesthesiaPrice']);
				$insIC->setTheatrePrice($row['theatrePrice']);

				$insIC->setServiceGroup((new BillSourceDAO())->getBillSource($row['item_group_category_id'], $pdo));
				$insIC->setCapitated($row['capitated']);
				$insIC->setType($row['type']);
				$insICs[] = $insIC;*/
				
				$row['default_price'] = $this->getItemDefaultPriceByCode($row['item_code'], $pdo);
				// $row['item_id'] = getItem($row['item_code'], $pdo)->id;
				$row['item_id'] = (!getItem($row['item_code'], $pdo) instanceof stdClass) ? getItem($row['item_code'], $pdo)->getId() : getItem($row['item_code'], $pdo)->id;
				
				$insICs[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$insICs = array();
			errorLog($e);
		}
		return $insICs;
	}
	
	
	function getInsuredItemCostsByScheme($itemId, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$scheme = (new InsuranceSchemeDAO())->get($sid, FALSE, $pdo);
			$sql = "SELECT itc.*, ib.item_group_category_id, ib.item_description FROM insurance_items_cost itc LEFT JOIN insurance_billable_items ib ON ib.item_code=itc.item_code WHERE itc.id=$itemId ORDER BY item_code";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$row['default_price'] = $this->getItemDefaultPriceByCode($row['item_code'], $pdo);
				$row['item_extra_details'] = (getItem($row['item_code'], $pdo) instanceof Drug) ? getItem($row['item_code'], $pdo)->getGeneric()->getName() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getForm() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getWeight() : '';
				$row['item_id'] = (!getItem($row['item_code'], $pdo) instanceof stdClass) ? getItem($row['item_code'], $pdo)->getId() : getItem($row['item_code'], $pdo)->id;
				
				return (object)$row;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function getNonInsuredItemsCostByScheme($sid, $group=null, $page=0, $pageSize=10, $filter=null, $pdo = null)
	{
		$insICs = array();
		$length = strlen($group);
		$strFilter = $filter != null ? " AND ibi.item_description LIKE '%".escape($filter)."%'":"";
		$sql = "SELECT itc.*, ibi.item_description FROM insurance_items_cost itc LEFT JOIN insurance_billable_items ibi ON ibi.item_code=itc.item_code WHERE itc.item_code NOT IN (SELECT item_code FROM insurance_items_cost WHERE insurance_scheme_id=$sid) AND insurance_scheme_id=1 AND SUBSTRING(ibi.item_code, 1, $length) = '$group'{$strFilter} ORDER BY item_description";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
			errorLog($e);
		}
		
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			//$scheme = (new InsuranceSchemeDAO())->get($sid, true, $pdo);
			//error_log("===================".$sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				/*$insIC = new InsuranceItemsCost();
				$insIC->setId($row['id']);
				$insIC->setItem(getItem($row['item_code'], $pdo));
				$insIC->setSellingPrice($row['selling_price']);
				$insIC->setInsuranceScheme($scheme);
				$insIC->setClinic($scheme->getHospital());
				$insIC->setDefaultPrice($this->getItemDefaultPriceByCode($row['item_code'], $pdo));
				$insIC->setFollowUpPrice($row['followUpPrice']);
				$insIC->setSurgeonPrice($row['surgeonPrice']);
				$insIC->setAnesthesiaPrice($row['anaesthesiaPrice']);
				$insIC->setTheatrePrice($row['theatrePrice']);*/
				$row['default_price'] = $this->getItemDefaultPriceByCode($row['item_code'], $pdo);
				// $row['item_id'] = getItem($row['item_code'], $pdo)->id;
				
				if(getItem($row['item_code'], $pdo)==null){
					error_log($row['item_code'].': this item is returning NULL');
				}
				
				if(getItem($row['item_code'], $pdo) == null){
					error_log($row['item_code'].': this item is returning NULL for generic');
				}
				$row['item_id'] = (!getItem($row['item_code'], $pdo) instanceof stdClass) ? getItem($row['item_code'], $pdo)->getId() : getItem($row['item_code'], $pdo)->id;
				$row['item_description'] = (getItem($row['item_code'], $pdo) instanceof Drug) ? $row['item_description'] . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getName() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getForm() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getWeight() : $row['item_description'];
				$row['item_extra_details'] = (getItem($row['item_code'], $pdo) instanceof Drug) ? getItem($row['item_code'], $pdo)->getGeneric()->getName() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getForm() . ' ' . getItem($row['item_code'], $pdo)->getGeneric()->getWeight() : '';
				
				$insICs[] = (object)$row;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$insICs = array();
		}
		$results = (object)null;
		$results->data = $insICs;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
	
	//renamed from getUpdatePrice
	function updatePrice($itemCost, $pdo = null)
	{
		$status = true;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE insurance_items_cost SET selling_price=" . $itemCost->getPrice() . " WHERE id=" . $itemCost->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$stmt = null;
		} catch (PDOException $e) {
			$status = false;
			errorLog($e);
		}
		return $status;
	}
	
	function getCoPayPriceByFamily($codePrefix, $scheme_id, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT co_pay FROM insurance_items_cost WHERE item_code LIKE '$codePrefix%' AND insurance_scheme_id = $scheme_id GROUP BY insurance_scheme_id LIMIT 1";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return $row['co_pay'];
			}
			return 0;
			
		} catch (PDOException $e) {
			return false;
		}
	}
	
	function updateCoPayPriceByFamily($codePrefix, $scheme_id, $amount, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE insurance_items_cost SET co_pay=$amount WHERE item_code LIKE '$codePrefix%' AND insurance_scheme_id = $scheme_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() >= 0) {
				return $this->getCoPayPriceByFamily($codePrefix, $scheme_id, $pdo);
			}
			return null;
			
		} catch (PDOException $e) {
			return null;
		}
	}
	
	
	function getItemCosts($schemeID, $source=NULL, $pdo = null)
	{
		$filter = ($source != null) ? " AND ibi.item_group_category_id=$source":"";
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT iic.item_code, iic.insurance_code, iic.selling_price, iic.anaesthesiaPrice, iic.followUpPrice, iic.surgeonPrice, iic.theatrePrice, ibi.item_description, ibi.item_group_category_id, bs.name AS item_category, iic.capitated, iic.type FROM insurance_items_cost iic LEFT JOIN insurance_billable_items ibi ON iic.item_code=ibi.item_code LEFT JOIN bills_source bs ON ibi.item_group_category_id=bs.id WHERE insurance_scheme_id=$schemeID{$filter} ORDER BY bs.name";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = (object)$row;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
		
		return $data;
	}
	function clearSchemeItems($schemeID, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM insurance_items_cost WHERE insurance_scheme_id=$schemeID";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			return TRUE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}
	
	function deleteItemForScheme($itemCode, $schemeId, $pdo=null){
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "DELETE FROM insurance_items_cost WHERE insurance_scheme_id=$schemeId AND item_code='$itemCode'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			return TRUE;
		} catch (PDOException $e) {
			errorLog($e);
			return FALSE;
		}
	}
	
	
}
