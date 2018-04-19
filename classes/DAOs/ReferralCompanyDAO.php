<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/15
 * Time: 2:08 PM
 */
class ReferralCompanyDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ReferralCompany.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		if(is_blank($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM referral_company WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				return (new ReferralCompany($row['id']))->setName($row['name'])->setAddress($row['address'])->setAddress($row['address'])->setContactPhone($row['contact_phone'])->setEmail($row['email'])->setBankName($row['bank_name'])->setAccountNumber($row['account_number']);
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function all($page = 0, $pageSize=10, $filter=null, $pdo = null)
	{
		$companies = [];
		$where = $filter ? " WHERE `name` LIKE '%$filter%' OR `address` LIKE '%$filter%' OR `contact_phone` LIKE '%$filter%' OR `email` LIKE '%$filter%' OR `bank_name` LIKE '%$filter%' OR `account_number` LIKE '%$filter%'":"";
		$sql = "SELECT * FROM referral_company{$where}";
		//error_log($sql);
		$total = 0;
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		}catch (PDOException $e){
			errorLog($e);
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0 ) ? $pageSize * $page : 0;

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			error_log(json_encode($sql));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$companies[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$companies = [];
		}
		$results = (object)null;
		$results->data = $companies;
		$results->total = $total;
		$results->page = $page;
		$results->pageSize = $pageSize;
		return $results;
	}
	

	function add($ref, $pdo = null)
	{
		$name = escape($ref->getName());
		$address = escape($ref->getAddress());
		$contact_phone = escape($ref->getContactPhone());
		$email = escape($ref->getEmail());
		$bank_name = escape($ref->getBankName());
		$account_number = escape($ref->getAccountNumber());
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO referral_company (`name`, address, contact_phone, email, bank_name, account_number) VALUES ('$name', '$address', '$contact_phone', '$email', '$bank_name', '$account_number')";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$ref->setId($pdo->lastInsertId());
				return $ref;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
	
	function update($ref, $pdo = null)
	{
		$name = $ref->getName();
		$address = $ref->getAddress();
		$contact_phone = $ref->getContactPhone();
		$email = $ref->getEmail();
		$bank_name = $ref->getBankName();
		$account_number = $ref->getAccountNumber();
		$id = $ref->getId();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE referral_company SET `name` = '$name', address = '$address', contact_phone= '$contact_phone', email='$email', bank_name='$bank_name', account_number='$account_number' WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() >= 0) {
				return $ref;
			}
			return null;
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}