<?php

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/2/15
 * Time: 4:26 PM
 */
class PatientProcedureRegimenDAO
{
	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedure.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientProcedureRegimen.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Prescription.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PrescriptionData.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientProcedureDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugGenericDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DrugBatchDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PrescriptionDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function getProcedureRegimens($p_procedure, $pdo = null)
	{
		$regimens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_regimen WHERE patient_procedure_id = " . $p_procedure->getId() . " ORDER BY request_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$regimen = new PatientProcedureRegimen($row['id']);
				$regimen->setDrugGeneric((new DrugGenericDAO())->getGeneric($row['generic_id'], true, $pdo));
				$regimen->setQuantity($row['quantity']);
				$regimen->setStatus($row['status']);
				$regimen->setBillLine($row['bill_line_id']);
				$regimen->setRequestTime($row['request_time']);
				//$regimen->setBatch($row['batch']);
				//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
				$regimen->setRequestingUser((new StaffDirectoryDAO())->getStaff($row['request_user_id'], false, $pdo));
				
				$regimens[] = $regimen;
			}
		} catch (PDOException $e) {
			return [];
		}
		return $regimens;
	}
	
	function getProcedureRegimen($id, $pdo = null)
	{
		$regimen = new PatientProcedureRegimen();
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_regimen WHERE id = " . $id . " ORDER BY request_time";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$regimen->setId($row['id']);
				$regimen->setDrugGeneric((new DrugGenericDAO())->getGeneric($row['generic_id'], true, $pdo));
				$regimen->setBatch((new DrugBatchDAO())->getBatch($row['batch'], $pdo) );
				$regimen->setQuantity($row['quantity']);
				$regimen->setStatus($row['status']);
				$regimen->setBillLine($row['bill_line_id']);
				$regimen->setRequestTime($row['request_time']);
				//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
				$regimen->setRequestingUser((new StaffDirectoryDAO())->getStaff($row['request_user_id'], false, $pdo));
				
			}
			$stmt = null;
			
		} catch (PDOException $e) {
			return null;
		}
		return $regimen;
		
	}
	
	function all($pdo = null)
	{
		$regimens = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_procedure_regimen ORDER BY patient_procedure_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$regimen = new PatientProcedureRegimen($row['id']);
				$regimen->setDrug((new DrugDAO())->getDrug($row['drug_id'], true, $pdo));
				$regimen->setBatch((new DrugBatchDAO())->getBatch($row['batch_id'], $pdo));
				$regimen->setQuantity($row['quantity']);
				$regimen->setRequestTime($row['request_time']);
				//                $note->setPatientProcedure( (new PatientProcedureDAO())->getProcedure($row['patient_procedure_id'], $pdo) );
				$regimen->setRequestingUser((new StaffDirectoryDAO())->getStaff($row['request_user_id'], false, $pdo));
				
				$regimens[] = $regimen;
			}
		} catch (PDOException $e) {
		
		}
		return $regimens;
	}
	
	function add($regimen, $pdo = null)
	{
		//$regimen = new PatientProcedureRegimen();
		$pharmacy = $regimen->getPharmacy();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			
			$pdo->beginTransaction();
			$dose = quote_esc_str($regimen->getQuantity());
			$sql = "INSERT INTO patient_procedure_regimen (patient_procedure_id, generic_id, quantity, generic_note, request_time, request_user_id, drugs, batch, units,status, bill_line_id) VALUES (" . $regimen->getPatientProcedure()->getId() . ", " .
				$regimen->getDrugGeneric()->getId() . ", " . $dose . ", '" . $regimen->getNote() . "', NOW()," .
				$regimen->getRequestingUser()->getId() . ",".
				($regimen->getDrug() ? $regimen->getDrug()->getId() : 'NULL').",".$regimen->getBatch()->getId().",'".$regimen->getUnit()."','".$regimen->getStatus()."','NULL') ";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			
			if ($stmt->rowCount() == 1) {
				$regimen->setId($pdo->lastInsertId());
				//
				//$pres = new Prescription();
				//$pres->setPatient(new PatientDemograph($regimen->getPatientProcedure()->getPatient()->getId()));
				//$pres->setRequestedBy($regimen->getRequestingUser());
				//$pres->setNote("For Use in Procedure: " . $regimen->getPatientProcedure()->getRequestCode());
				//$pres->setHospital($regimen->getRequestingUser()->getClinic());
				//
				//$pd = new PrescriptionData();
				//
				//$pd->setDrug($regimen->getDrug());
				//$pd->setNote($regimen->getNote());
				//$pd->setGeneric($regimen->getDrug()->getGeneric());
				//$pd->setDose($regimen->getQuantity());
				//$pd->setBatch($regimen->getBatch());
				//$pd->setDuration(1);
				//$pd->setFrequency(1 . ' x ' . ' in procedure');
				//$pd->setRefillable(false);
				//$pd->setRequestedBy($regimen->getRequestingUser());
				//$pd->setHospital($regimen->getRequestingUser()->getClinic());
				//
				//$pres->setData([$pd]);// only one drug
				//$pres->setServiceCentre($pharmacy);
				//
				//$p = (new PrescriptionDAO())->addPrescription($pres, $pdo);
				//if ($p === null) {
				//	error_log("error:Unable to save regimen");
				//	$pdo->rollBack();
				//	return null;
				//} else {
				$pdo->commit();
				return $regimen;
				//                }
			} else {
				error_log("error.........?");
				$pdo->rollBack();
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}

	}

    function updateStatus($regimen, $pdo = null)
    {
        try {
            $pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
            $bill_line = $regimen->getBillLine() ? $regimen->getBillLine() : 'NULL';
            $sql = "UPDATE patient_procedure_regimen SET status = '" . $regimen->getStatus() . "', bill_line_id=$bill_line WHERE id = " . $regimen->getId();
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();
            if ($stmt->rowCount() == 1) {
                return $regimen;
            } else {
                return null;
            }
        } catch (PDOException $e) {
            errorLog($e);
            return null;
        }
    }
}