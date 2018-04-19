<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 3:18 PM
 */
class SelfRegisterPatientDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/SelfRegisteredPatient.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/SelfRegisterPatientContactDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StateDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LGADAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DistrictDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ReligionDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CountryDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/KinRelationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IndustryDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR::' . $e->getMessage());
		}

	}

	function getSelfRegisteredPatientAll($pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM fake_patient fp LEFT JOIN fake_contact fc ON fp.id=fc.fake_patient_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$patient = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$patient[] = (new SelfRegisteredPatient($row['id']))->setTitle($row['title'])->setFname($row['fname'])->setLname($row['lname'])->setSex($row['sex'])->setContact($row['phone']);
//				setContact((new SelfRegisterPatientContactDAO())->getSelfRegPatientContact($row['id'], $pdo));

			}
			return $patient;
		} catch (PDOException $e) {
			errorLog($e);
			$patient = null;
		}
		return [];
	}


	function get($x, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM fake_patient WHERE id=$x";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pat = new SelfRegisteredPatient();
				$pat->setId($row['id']);
				$pat->setTitle($row['title']);
				$pat->setFname($row['fname']);
				$pat->setLname($row['lname']);
				$pat->setSex($row['sex']);
				$pat->setEmail($row['email']);
				$pat->setOccupation($row['occupation']);
				$pat->setBloodGroup($row['blood_group']);
				$pat->setGenotype($row['geno_type']);
				$pat->setDateOfBirth($row['date_of_birth']);
				$pat->setWorkAddress($row['work_address']);
				$pat->setResAddress($row['res_address']);
				$pat->setNextKinFname($row['next_kin_fname']);
				$pat->setNextKinLname($row['next_kin_lname']);
				$pat->setNextKinPhone($row['next_kin_phone']);
				$pat->setNextKinAddress($row['next_kin_address']);
				$pat->setContact((new SelfRegisterPatientContactDAO())->getSelfRegPatientContact($row['id'], $pdo));
				$country = (new CountryDAO())->getCountry($row['country_id'], $pdo);
				$state = (new StateDAO())->getState($row['state_id'], $pdo);
				$lga = (new LGADAO())->getLGA($row['lga_id'], $pdo);
				$district = (new DistrictDAO())->get($row['district_id'], $pdo);
				$industry = (new IndustryDAO())->get($row['industry_id'], $pdo);
				$res_state = (new StateDAO())->getState($row['res_state_id'], $pdo);
				$res_lga = (new LGADAO())->getLGA($row['res_lga_id'], FALSE, $pdo);
				$res_district = (new DistrictDAO())->get($row['res_dist_id'], $pdo);
				$religion = (new ReligionDAO())->getReligion($row['religion_id'], $pdo);
				$relation = (new KinRelationDAO())->get($row['relationship_id'], $pdo);
				$pat->setCountry($country);
				$pat->setState($state);
				$pat->setLga($lga);
				$pat->setDistrict($district);
				$pat->setIndustry($industry);
				$pat->setResState($res_state);
				$pat->setResLga($res_lga);
				$pat->setResDistrict($res_district);
				$pat->setRelationship($relation);
				$pat->setReligion($religion);
				return $pat;
			} else {
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
			return null;
		}
	}
}