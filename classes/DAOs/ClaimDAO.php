<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/4/16
 * Time: 10:03 PM
 */
class ClaimDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Bill.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Claim.php';
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

	function get($id, $pdo = NULL)
	{
		if (is_null($id)) return NULL;
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM claim WHERE id=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$line_ids = $row['line_ids'];
				$lines = [];
				foreach (array_filter(explode(",", $line_ids)) as $item) {
					$lines[] = new Bill($item);
				}
				$claim = (new Claim($row['id']))
					->setCreateDate($row['create_date'])
					->setEncounter(new Encounter($row['encounter_id']))
					->setPatient((new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo))
					->setScheme((new InsuranceSchemeDAO())->get($row['scheme_id'], FALSE, $pdo))
					->setCreateUser(new StaffDirectory($row['create_user_id']))
					->setLines($lines)
					->setTotalPayment($row['total_payment'])
					->setBalance($row['balance'])
					->setTotalCharge($row['total_charge'])
					->setType($row['type'])
					->setStatus($row['status'])
					->setReason($row['reason'])
					->setState($row['_state'])
					->setSignature( (new SignatureDAO())->get($row['signature_id'], $pdo) );

				return $claim;
			}
			return NULL;
		} catch (PDOException $e) {
			errorLog($e);
			return NULL;
		}
	}

	function all($pdo = NULL)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM claim";
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

	function forPatient($pid, $pdo = NULL)
	{
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM claim WHERE patient_id=$pid ORDER BY scheme_id";
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

	function add($claim, $pdo = NULL)
	{
		return $claim->add($pdo);
	}

	function forScheme($sid=null, $insurer=null, $pdo = NULL)
	{

		$filter = "";
		$extraquery = "";
		if($insurer != null &&  $sid == null){
			$filter = "io.id=$insurer";
			$extraquery = "LEFT JOIN insurance_schemes isc ON c.scheme_id=isc.id LEFT JOIN insurance_owners io ON isc.scheme_owner_id=io.id";
		}
		if($sid != null && $insurer == null){
			$filter = "c.scheme_id=$sid";
			$extraquery = "";
		}else if ($sid != null && $insurer != null){
			$filter = "c.scheme_id=$sid AND io.id=$insurer";
			$extraquery = "LEFT JOIN insurance_schemes isc ON c.scheme_id=isc.id LEFT JOIN insurance_owners io ON isc.scheme_owner_id=io.id";

		}
		
		$data = [];
		try {
			$pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT c.id FROM claim c $extraquery WHERE $filter ORDER BY c.patient_id";
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

    function getClaimLine($pid,$lines,$pdo = NULL)
    {

        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT DISTINCT encounter_id FROM claim WHERE patient_id=$pid AND line_ids='$lines' ";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
             $data = $stmt->fetch();
            return $data['encounter_id'];
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
    }
    public function getSignatureId($id,$pdo =NULL)
    {
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM claim WHERE id=$id";
            //error_log($sql);
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $data = $stmt->fetch();
            return $data['signature_id'];
        } catch (PDOException $e) {
            errorLog($e);
            return [];
        }
    }

    public function getClaimsReport($from = null, $to = null, $scheme_id = null, $provider_id = null,  $page, $pageSize, $pdo = NULL){
        $f = ($from == null) ? date("Y-m-d") : $from;
        $t = ($to == null) ? date("Y-m-d") : $to;
        $scid = ($scheme_id == null) ? '' : ' AND c.scheme_id=' . $scheme_id;
        $providerId = ($provider_id == null) ? '' : ' AND inso.id=' . $provider_id;
        $sql = "SELECT c.*, i.coverage_type, it.name AS insurance_type FROM claim c  LEFT JOIN insurance_schemes ins ON c.scheme_id=ins.id LEFT JOIN insurance i ON i.insurance_scheme=ins.id LEFT JOIN insurance_owners inso ON ins.scheme_owner_id=inso.id LEFT JOIN insurance_type it ON ins.insurance_type_id=it.id WHERE (DATE(c.create_date) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "')) $scid $providerId GROUP BY c.id";
        $total = 0;
        try{
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            $total = $stmt->rowCount();
        }catch (PDOException $e){
            errorLog($e);
            error_log("ERROR: Failed to return total number of records");
        }

        $page = ($page > 0) ? $page : 0;
        $offset = ($page > 0) ? $pageSize * $page : 0;

        $claims = array();
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $sql .= " ORDER BY DATE(c.create_date) ASC LIMIT $offset, $pageSize";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $claims[] = (object)$row;

            }
            $stmt = null;
        }catch (PDOException $e){
             $claims = array();
        }
        $results = (object)null;
        $results->data = $claims;
        $results->total = $total;
        $results->page = $page;

        return $results;
    }


}