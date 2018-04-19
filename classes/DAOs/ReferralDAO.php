<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/21/15
 * Time: 1:41 PM
 */
class ReferralDAO
{
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Referral.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReferralCompanyDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function get($id, $pdo = null)
	{
		$ref = new Referral();
		if ($id === null || empty($id))
			return null;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM referral WHERE id = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ref->setId($row['id']);
				$ref->setCompany((new ReferralCompanyDAO())->get($row['referral_company_id'], $pdo));
				$ref->setName($row['name']);
				$ref->setPhone($row['phone']);
				$ref->setEmail($row['email']);
				$ref->setSpecialization((new StaffSpecializationDAO())->get($row['specialization_id'], $pdo));
				$ref->setBankName($row['bank_name']);
				$ref->setAccountNumber($row['account_number']);
			} else {
				$ref = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo('ERROR: ' . $e->getMessage());
			$ref = null;
		}
		return $ref;
	}
	
	function all($page = 0, $pageSize=10, $filter=null, $pdo = null)
	{
		$refs = [];
		$where = $filter ? " LEFT JOIN staff_specialization s ON s.id=r.specialization_id WHERE s.`staff_type` LIKE '%$filter%' OR r.`name` LIKE '%$filter%' OR `phone` LIKE '%$filter%' OR `bank_name` LIKE '%$filter%' OR `account_number` LIKE '%$filter%'":"";
		$sql = "SELECT r.* FROM referral r {$where}";
		$total = 0;
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$total = $stmt->rowCount();
		} catch (PDOException $e) {
		}
		$page = ($page > 0) ? $page : 0;
		$offset = ($page > 0) ? $pageSize * $page : 0;
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql .= " LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$refs[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
			$refs = [];
		}
		$results = (object)null;
		$results->data = $refs;
		$results->total = $total;
		$results->page = $page;
		return $results;
		
	}
	
	function by_company($company_id, $pdo = null)
	{
		$refs = [];
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM referral WHERE referral_company_id = " . $company_id;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$refs[] = $this->get($row['id'], $pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			echo 'ERROR: ' . $e->getMessage();
			$refs = [];
		}
		return $refs;
	}
	
	function add($ref, $pdo = null)
	{
		$referral_company_id = $ref->getCompany()->getId();
		$email = $ref->getEmail() != null ? $ref->getEmail() : "NULL";
		$name = escape($ref->getName());
		$phone = escape($ref->getPhone());
		$specialization_id = $ref->getSpecialization()->getId();
		$bank_name = escape($ref->getBankName());
		$account_number = escape($ref->getAccountNumber());
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "INSERT INTO referral (referral_company_id, `name`, phone, specialization_id, bank_name, account_number, email) VALUES ($referral_company_id, '$name', '$phone', $specialization_id, '$bank_name', '$account_number', '$email')";
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
		//        $ref = new Referral();
		$referral_company_id = $ref->getCompany()->getId();
		$name = $ref->getName();
		$specialization_id = $ref->getSpecialization()->getId();
		$bank_name = $ref->getBankName();
		$account_number = $ref->getAccountNumber();
		$id = $ref->getId();
		$phone = escape($ref->getPhone());
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE referral SET referral_company_id = $referral_company_id, `name` = '$name', phone= '$phone', specialization_id = $specialization_id, bank_name='$bank_name', account_number='$account_number' WHERE id=$id";
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