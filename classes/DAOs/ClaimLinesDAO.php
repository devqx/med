<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 3/22/18
 * Time: 1:03 PM
 */

class ClaimLinesDAO
{
	
	private $conn = null;
	
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Claim.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/ClaimLines.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Encounter.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/InsuranceScheme.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/InsuranceSchemeDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SignatureDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($id, $pdo=null){
		if (is_null($id)) return NULL;
		
		try{
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
		  $sql = "SELECT * FROM claim_bill_lines WHERE id=$id";
		  $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		  $stmt->execute();
		  if($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){
		   $caim_line = (new ClaimLines($row['id']))
			   ->setBillLine($row['bill_line_id'])
			   ->setClaim($row['claim_id'])
			   ->setAmount($row['amount']);
		   return $caim_line;
			}
			return null;
			
		}catch (PDOException $e){
			errorLog($e);
			return null;
		}
	}
	
	function getLines($claimId, $pdo=null){
		
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM claim_bill_lines  WHERE claim_id=$claimId";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$data[] = $this->get($row['id'], $pdo);
			}
			return $data;
		} catch (PDOException $e) {
			errorLog($e);
			return [];
		}
	}
	
}