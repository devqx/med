<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of StaffDAO
 *
 * @author pauldic
 */
class StaffDirectoryException extends Exception
{
}


class StaffDirectoryDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/CareTeam.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Department.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffSpecialization.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/CareTeamDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/DepartmentDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/StaffRolesDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/StaffRole.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
			@session_start();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addStaff($staff, $pdo = null)
	{
		if (count($this->getActiveUsers($pdo)) > MainConfig::$numUsers) {
			throw new StaffDirectoryException('Number of active users exceeded. ' . count($this->getActiveUsers($pdo)) . "/" . MainConfig::$numUsers);
		}
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$folioNumber = !is_blank($staff->getFolioNumber()) ? quote_esc_str($staff->getFolioNumber()) : 'NULL';
			$sql = "INSERT INTO staff_directory SET firstname='" . $staff->getFirstName() . "', lastname='" . $staff->getLastName() . "', phone='" . $staff->getPhone() . "',clinic_id=" . ($staff->getClinic() != null ? "'" . $staff->getClinic()->getId() . "'" : 'NULL') . ",specialization_id=" . ($staff->getSpecialization() != null ? "'" . $staff->getSpecialization()->getId() . "'" : 'NULL') . ", email='" . $staff->getEmail() . "', pswd='" . $staff->getPassword() . "', profession='" . $staff->getProfession() . "', username='" . $staff->getUsername() . "', sip_user_name='" .$staff->getSipUserName(). "', sip_password='" . $staff->getSipPassword(). "',  sip_extension='". $staff->getSipExtension()."',  `status`='" . $staff->getStatus() . "', folio_number=$folioNumber";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				$staff->setId($pdo->lastInsertId());
				$stmt = null;
				return $staff;
			} else {
				$stmt = null;
				return null;
			}
		} catch (PDOException $e) {
			errorLog($e);
		}
		return null;
	}

	function getActiveUsers($pdo = null)
	{
		$users = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE `status` = 'active'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$users[] = $this->getStaff($row['staffId'], FALSE, $pdo);
			}
		} catch (PDOException $e) {
			return [];
		}
		return $users;
	}

	function getStaff($id, $getFull = FALSE, $pdo = null)
	{
		if ($id === null || is_blank($id)) {return null;}
		$staff = new StaffDirectory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE staffId = $id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setSipUserName($row['sip_user_name']);
				$staff->setSipExtension($row['sip_extension']);
				$staff->setSipPassword($row['sip_password']);
				$staff->setFolioNumber($row['folio_number']);
				$staff->setIsConsultant($row['is_consultant']);

				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic);   //Obj
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setProfession($row["profession"]);
				$staff->setUsername($row["username"]);
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setStatus($row["status"]);
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
			} else {
				$staff = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$staff = null;
		}
		return $staff;
	}

	function searchStaffNames($term, $limit = 100, $asArray = FALSE, $pdo = null, $all = null)
	{ //$all=NULL: means ONLY Active, $all=TRUE: means BOTH Active And InActive, $all=FALSE: InActive
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.staffId, s.firstname, s.lastname, s.phone, c.id, c.staff_type, s.roles FROM staff_directory s LEFT JOIN staff_specialization c ON s.specialization_id = c.id  WHERE " . "((s.staffId LIKE '%$term%' OR s.firstname LIKE '%$term%' OR s.lastname  LIKE '%$term%' OR s.phone LIKE '%$term%' OR c.staff_type LIKE '%$term%') AND " . ($all == null ? "s.status='active'" : ($all ? "(s.status='active' OR s.status='disabled' OR s.status='reset'))" : "s.status='disabled'")) . ") LIMIT " . (isset($limit) ? $limit : 100);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($asArray) {
				while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$staff = array();
					$staff['id'] = $row["staffId"];
					$staff['phone'] = $row["phone"];
					if ($row['id'] !== null) {
						$spe = [];
						$spe['id'] = $row['id'];
						$spe['name'] = $row['staff_type'];
					} else {
						$spe = null;
					}
					$staff['specialization'] = $spe;
					$staff['fullname'] = $row["lastname"] . " " . $row["firstname"];
					$staff['careTeam'] = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
					$staffs[] = $staff;
				}
			} else {
				while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
					$staff = new StaffDirectory();
					$staff->setId($row["staffId"]);
					$staff->setPhone($row["phone"]);
					if ($row['id'] !== null) {
						$spe = new StaffSpecialization($row['id']);
						$spe->setName($row['staff_type']);
					} else {
						$spe = null;
					}
					$staff->setSpecialization($spe);
					$staff->setFirstName($row["firstname"]);
					$staff->setLastName($row["lastname"]);
					$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
					$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
					$staff->setDepartment((new DepartmentDAO())->get($row['department_id']));
					$staff->setCareTeams((new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo));
					$staffs[] = $staff;
				}
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$staffs = [];
		}
		return $staffs;
	}

	function getStaffMin($id, $pdo = null)
	{
		$staff = new StaffDirectory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE staffId=$id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setUsername($row["username"]);
			} else {
				$staff = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staff = null;
		}
		return $staff;
	}

	function getStaffMinByIds($ids, $pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE staffId  IN (" . (is_array($ids) ? implode(", ", $ids) : $ids) . ")  ORDER BY lastname, firstname";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setProfession($row["profession"]);

				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$staffs = [];
		}
		return $staffs;
	}


	function getStaffs($getFull = FALSE, $pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE `status` = 'active'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setSipUserName($row['sip_user_name']);
				$staff->setSipExtension($row['sip_extension']);
				$staff->setSipPassword($row['sip_password']);
				$staff->setFolioNumber($row['folio_number']);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic);   //Obj
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setProfession($row["profession"]);
				$staff->setUsername($row["username"]);
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setStatus($row["status"]);
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staffs = array();
		}
		return $staffs;
	}

	function getAllStaffs($getFull = FALSE, $pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setSipUserName($row['sip_user_name']);
				$staff->setSipExtension($row['sip_extension']);
				$staff->setSipPassword($row['sip_password']);
				$staff->setFolioNumber($row['folio_number']);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic);
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setProfession($row["profession"]);
				$staff->setUsername($row["username"]);
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setStatus($row["status"]);
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staffs = array();
		}
		return $staffs;
	}

	function getDoctors($term = null, $getFull = FALSE, $pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			if ($term) {
				$sql = "SELECT s.*, c.id, c.staff_type FROM staff_directory s LEFT JOIN staff_specialization c ON s.specialization_id = c.id  WHERE s.profession='Doctor' AND (s.staffId LIKE '%$term%' OR s.firstname LIKE '%$term%' OR s.lastname  LIKE '%$term%' OR s.phone LIKE '%$term%' OR c.staff_type LIKE '%$term%') AND s.status='active'";
			} else {
				$sql = "SELECT * FROM staff_directory WHERE profession='Doctor'";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setFolioNumber($row['folio_number']);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic);
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staffs = array();
			$stmt = null;
		}
		return $staffs;
	}

	function getPatientCareMembers($ipid, $pdo = null)
	{
		$cMembers = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, pc.id as pct_id, (SELECT pc.care_member_ids LIKE CONCAT('%',s.staffId, '%')) as is_part_of FROM staff_directory s LEFT JOIN patient_care_team pc ON TRUE WHERE pc.in_patient_id=$ipid AND pc.status='Active' ORDER BY is_part_of DESC";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();

				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setProfession($row["profession"]);
				$staff->setAnId($row['is_part_of'] == 0 ? null : $row['pct_id']);

				$cMembers[] = $staff;
			}
			if (count($cMembers) === 0) {
				$cMembers = $this->getStaffsMin($pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cMembers = [];
		}
		return $cMembers;
	}

	function getStaffsMin($pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE `status`='active' ORDER BY lastname, firstname";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setProfession($row["profession"]);

				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$staffs = [];
		}
		return $staffs;
	}

	function getPatientCareMembersList($ipid, $pdo = null)
	{
		$cMembers = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT s.*, cm.id as pct_id, st.staff_id as is_part_of  FROM staff_directory s LEFT JOIN patient_care_member cm ON cm.care_member_id=s.staffId AND cm.in_patient_id =$ipid  AND cm.status='Active' LEFT JOIN staff_care_team st ON st.staff_id=cm.care_member_id AND st.team_id=cm.care_team_id ORDER BY s.lastname";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setProfession($row["profession"]);
				$staff->setAnId($row['pct_id']);
				$cMembers[] = $staff;
			}
			if (count($cMembers) === 0) {
				$cMembers = $this->getStaffsMin($pdo);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$stmt = null;
			$cMembers = [];
		}
		return $cMembers;
	}

	function getStaffsIn($ids, $getFull = FALSE, $pdo = null)
	{
		$staffs = array();
		try {
			$pdo = $pdo === null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE (staffId IN (" . $ids . "))";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = new StaffDirectory();
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				$staff->setFolioNumber($row['folio_number']);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic);
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setProfession($row["profession"]);
				$staff->setUsername($row["username"]);
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setStatus($row["status"]);
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
				$staffs[] = $staff;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staffs = array();
			$stmt = null;
		}
		return $staffs;
	}

	function staffExists($username, $pswd, $getFull = FALSE, $pdo = null)
	{
		$staff = new StaffDirectory();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE (username = '" . $username . "' OR email = '" . $username . "' )# AND pswd = MD5('" . $pswd . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$i = 0;
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff->setId($row["staffId"]);
				$staff->setFirstName($row["firstname"]);
				$staff->setLastName($row["lastname"]);
				$staff->setPhone($row["phone"]);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row["clinic_id"], $getFull, $pdo);
					$spe = (new StaffSpecializationDAO())->get($row['specialization_id'], $pdo);
					$dept = (new DepartmentDAO())->get($row['department_id'], $pdo);
					$careTeam = (new CareTeamDAO())->getStaffCareTeamsAsArray($row["staffId"], $pdo);
				} else {
					$clinic = new Clinic($row["clinic_id"]);
					$spe = new StaffSpecialization($row['specialization_id']);
					$dept = new Department($row['department_id']);
					$careTeam = new CareTeam($row["staffId"]);
				}
				$staff->setClinic($clinic); //Obj
				$staff->setSpecialization($spe);
				$staff->setEmail($row["email"]);
				$staff->setPassword($row["pswd"]);
				$staff->setProfession($row["profession"]);
				$staff->setUsername($row["username"]);
				$staff->setRoles((new StaffRolesDAO())->getStaffRoles($row['staffId'], $pdo));
				$staff->setRolesRaw( array_filter(explode("|", $row['roles'])) );
				$staff->setStatus($row["status"]);
				$staff->setDepartment($dept);
				$staff->setCareTeams($careTeam);
			} else {
				$staff = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$staff = null;
			$stmt = null;
		}
		return $staff;
	}

	function resetPassword($staff_id, $new_password, $pdo = null)
	{

		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
			$protect = new Protect();
			$this_user = $this->getStaff($_SESSION['staffID'], FALSE, $pdo);

			if (!$this_user->hasRole($protect->mgt)) {
				return FALSE;
			}
			$sql = "UPDATE staff_directory SET pswd = '" . password_hash($new_password, PASSWORD_BCRYPT) . "' WHERE staffId = :staff_id";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->bindParam(':staff_id', $staff_id, PDO::PARAM_STR, 11);
			$stmt->execute();
			if ($stmt->rowCount() == 1) {
				return TRUE;
			}
		} catch (PDOException $e) {
			return FALSE;
		}
		return FALSE;
	}

	function getActiveUsersSlim($pdo = null)
	{
		$users = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM staff_directory WHERE `status` = 'active'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$users[] = $row;
			}
		} catch (PDOException $e) {
			return [];
		}
		return $users;
	}

	function getProfessions($pdo = null)
	{
		$p = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SHOW COLUMNS FROM staff_directory WHERE Field = 'profession'";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
			preg_match('/^enum\((.*)\)$/', $row["Type"], $matches);
			foreach (explode(',', $matches[1]) as $value) {
				$p[] = trim($value, "'");
			}

		} catch (PDOException $e) {
			return [];
		}
		return $p;
	}
}