<?php

class StaffManager
{
	function __construct()
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffSpecializationDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}
	
	function doLogin($username, $pswd, $next = null, $pdo = null, $writeSession = true)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		
		if (trim($username) != "" && trim($pswd) != "") {
			$username = escape($username);
			$sql = "SELECT staff_directory.*, onlinestatus.session_id, staff_specialization.staff_type FROM staff_directory LEFT JOIN onlinestatus ON staff_directory.staffId=onlinestatus.staffId LEFT JOIN staff_specialization ON staff_directory.specialization_id=staff_specialization.id WHERE (username = '" . $username . "' OR email = '" . $username . "' OR staff_directory.staffId = '" . $username . "') AND (`status` ='active' OR `status`='reset'/* OR `status`='disabled'*/)";
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if (!$chk) {
				return 'error:Connection error';
			}
			$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
			if ($stmt->rowCount() == 1) {
				if (!isset ($_SESSION)) {
					session_start();
				}
				
				if (password_verify($pswd, $row['pswd'])) {
					if ($row ['status'] == 'disabled') {
						return 'error:Your account is disabled';
					} else {
						if ($row['status'] == "reset" || substr($pswd, 0, strlen($pswd)) === "password" || substr($pswd, 0, strlen($pswd)) === "1234") {
							return 'change:You must change your password before you can continue:' . base64_encode($username) . ':' . base64_encode($pswd);
						} else {
							if ($row['session_id'] !== null) {
								//clear previous session
								return 'session:active:' . $row ['staffId'] . ':' . $row['session_id'];
							} else {
								if ($writeSession) {
									$sql2 = "INSERT INTO onlinestatus (staffId, session_id) VALUES (" . $row ['staffId'] . ", '" . session_id() . "') ON DUPLICATE KEY UPDATE session_id='" . session_id() . "'";
									
									$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
									$stmt2->execute();
									
									$_SESSION['staffID'] = $row ['staffId'];
									$_SESSION['staff_type'] = $row ['staff_type'];
									$_SESSION['lastname'] = $row ['lastname'];
									$_SESSION['specialization_id'] = $row ['specialization_id'];
									$_SESSION['profession'] = $row ['profession'];
									$_SESSION['role'] = $row ['specialization_id'];
									$_SESSION['username'] = $row['username'];
								}
								return 'success:' . $row ['profession'] . (($next != null) ? ':' . $next : '') . ':' . $row ['staffId'];
								// also return the person's role so that you use it
								// in controlling the boxy for subscription
							}
						}
					}
				} else {
					return 'error:Invalid Login details';
				}
			} else {
				return 'error:Account not unique';
			}
		} else {
			return 'error:Enter username and password';
		}
	}
	
	/**
	 * @param      $sID
	 * @param null $pdo
	 *
	 * @return bool
	 */
	function doRoomCleanUp($sID, $pdo = null)
	{
		if ($sID == null) {
			return true;
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		// get the room that staff logged in to
		$sql = "SELECT roomID FROM doctors_subscribed WHERE staffID = " . $sID;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		if ($stmt->rowCount() >= 1) {
			$room = $row['roomID'];
			$pdo->prepare("UPDATE exam_rooms SET available = TRUE, consultant_id = NULL, specialization_id = NULL WHERE room_id = " . $room, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();
			$pdo->prepare("DELETE FROM doctors_subscribed WHERE staffID = $sID", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();
			$pdo->prepare("DELETE FROM patient_in_room WHERE roomID = $sID", array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL))->execute();
			unset($_SESSION['room']);
		}
		
		return true;
	}
	
	function isSubscribedToroom($staffID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT d.staffID FROM doctors_subscribed d LEFT JOIN staff_directory s ON d.staffID = s.staffId WHERE /*LCASE( s.profession ) =  'doctor' AND*/ d.staffID = '$staffID'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return true;
		}
		return false;
	}
	
	
	function checkStaffAccessRoles($staffID, $role)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT roles FROM staff_directory WHERE staffId  = " . $staffID;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result = $stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$row_roles = explode("|", $row[0]);
		$roles = array();
		for ($i = 0; $i < sizeof($row_roles); $i++) {
			$roles[] = $row_roles[$i];
		}
		
		if (in_array($role, $roles)) {
			return true;
		} else {
			return false;
		}
		
	}
	
	function isRoleAllowed($role)
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		if (isset($_SESSION['staffID'])) {
			if (!$this->checkStaffAccessRoles($_SESSION['staffID'], $role)) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
	
	function getHospitalInfo($clinicID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM clinic WHERE clinicId  = " . $clinicID;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$retVal = "";
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		
		if ($stmt->rowCount() >= 1) {
			do {
				$retVal .= $row_data ['name'];
			} while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			return $retVal;
		} else {
			if ($stmt->rowCount() < 1) {
				return '<em>*No Data Found !</em>';
			}
		}
		return '';
	}
	
	function getStaffInfo($sid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		
		$sql = "SELECT * FROM staff_directory WHERE staffID =" . $sid;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row_data;
	}
	
	function getStaffUsername($id)
	{//gets the plain username reference
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		
		$sql = "SELECT * FROM staff_directory WHERE staffID =" . $id;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row_data['username'];
	}
	
	function getDoctorNameFromID($id, $level = 0, $extended = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		
		$sql = "SELECT * FROM staff_directory WHERE staffID =" . $id;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		
		$username = "";
		if ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$username = $row_data ['username'];
			$id = $row_data ['staffId'];
			$fname = $row_data ['firstname'];
			$lname = $row_data ['lastname'];
			$phone = $row_data['phone'];
			return '<a href="/staff_profile.php?id=' . $id . '">' . $username . '</a>';
		}
		return 'N/A';
	}
	
	//FIXME: this search fails because of specialization_id
	function doBasicSearch($param, $type = null, $options = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		if (trim($param) != '' || is_null($param)) {
			$pdo = (new MyDBConnector())->getPDO();
			if ($type != "advanced" || $options == null) {
				$sql = "SELECT * FROM staff_directory WHERE `status` = 'active' AND staffId  LIKE '%" . $param . "%' OR firstname  LIKE '%" . $param . "%' OR lastname  LIKE '%" . $param . "%' OR phone  LIKE '%" . $param . "%' OR specialization_id  LIKE '%" . $param . "%' OR email  LIKE '%" . $param . "%' OR profession  LIKE '%" . $param . "%' OR username  LIKE '%" . $param . "%'";
			} else {
				$sql = "SELECT * FROM staff_directory WHERE staffId  LIKE '%" . $param . "%' OR firstname  LIKE '%" . $param . "%' OR lastname  LIKE '%" . $param . "%' OR ";
				if (strpos($_GET ['options'], "phone") !== false) {
					$sql .= "phone  LIKE '%" . $param . "%' OR ";
				}
				if (strpos($_GET ['options'], "profession") !== false) {
					$sql .= "specialization_id  LIKE '%" . $param . "%' OR ";
				}
				if (strpos($_GET ['options'], "email") !== false) {
					$sql .= "email  LIKE '%" . $param . "%' OR ";
				}
				if (strpos($_GET ['options'], "profession") !== false) {
					$sql .= "profession  LIKE '%" . $param . "%' OR ";
				}
				$sql .= "username  LIKE '%" . $param . "%'";
			}
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
			$str = '';
			if ($stmt->rowCount() > 0) {
				do {
					$str
						.= '<tr><td><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAD5klEQVRoge1YzW7iPBQ9Tk34SU1J
K5qCWAAbgpDa93+AvgCbli7aSl0gqlZRKZUQgeRbjJwvCbaTOMDMSHMkiwOJfe+1D9fXJvf39yH+
YtAw/Kv9h0EIAQAQQpDmomf8e1GuslOGU5VhXWePPVac/5PQ7+bRCoRhGP2YXpWyqxTvf2hO0zoF
9rUrcoYQkosfG5Q7VdRY5sxQikqlAkIIfN/HZrMBkD/wvDyXhPKi0WjAcRzYto1qtZp45vs+PM/D
YrHAarVCGIZ/joQqlQr6/T7a7ba0r2macBwH19fX8DwPz8/P0aqUgRF3qgh4n3q9jtvb28j5+MyK
OCEEtm3j7u4OjLHM97O4oXpJ1OLO12o1TCaTSC6Flp5SjMdjnJ+fF+4b50Y85xdphmFgNBrBNE2t
/oQQUEoxGo1wdnamPcaehFR7QHwVHMeBZVnaS895tVpFr9fTmn0A2RJKP+PodrtaBkX85uYGhmFo
TYIwC2WBMYZarVa4nwyUUti2jc/Pz8J9tSR0cXFROnukebPZFNrL4omNLE8QABKzfwgJAb/ScZYv
uTeyLPCscUjojqkloSAIDi6h3W4ntJfFtSS0Xq8LGcnD1+v16SS0XC4PLiHdMbUktFwusdlsDiah
3W4Hz/OE9rK4kRWh6BgXhiHm87mWQRF/f3+P/gNFIQ0gvhund2YAmM/nWK/X2jUMb77v4+3tTXsC
lCugOtjvdjvMZjNst1ttCQVBgKenJ/i+r3JDCWkAqvsdjp+fHzw8PGC73UaOcWTxIAgwm82wXC4z
baq4toQ4vr+/MZ1O8fX1FQ2a1VarFabTKTzPK/0f0pZQHIwxGIaRCDyL81K8LKjKeRGPw7Zt9Pt9
1Ov1vWeqWWOMgTGGXq+H19fXKIXq3ExIAxDJJR7QcDiE4zilNrRGo4HxeIzFYoGXlxcEQSC0r+LS
ALij/MXoKo9SuK4LxtjeoDozCPw63TUaDTw+PhbOSIWyEKUUk8lE6HxZzhjDZDJBpVKR2hfx3FmI
EALXdWFZVunNS9Ysy4LruolVypqA3FloMBig2WwerP6RccYYBoOByq0EcknItm04jqNV7upwfj0p
8qWwhHjGOZZsZG04HCb2FlnQmRLqdDqJi9pjS4jzarWKTqejcg9AhoQIIYn7n1NJiPNut7tXAaS5
ciO7urqCaZqyV44O0zRxeXmJj4+PhF9xrpRQu90WduKfp+DcBxmkAVBK0Wq1pIOnAzoWb7VaoPR/
oeSWULzC/J0ghIAxJj0zSz3MKhdOJaG0L2lIV8CyrJNKRcXjZ4fc5XStVitVKh8SqrtYqYR4VSjq
xD9PxeO+pPEfwZX73lUl/D0AAAAASUVORK5CYII=" height="32" width="30" /></td><td><a href="/staff_profile.php?id=' . $row_data ['staffId'] . '">' . $row_data ['firstname'] . ' ' . $row_data ['lastname'] . '</a></td><td>' . $row_data ['phone'] . '</td><td>' . $row_data ['email'] . '</td><td>' . $row_data ['profession'] . '</td><td>' . $this->getHospitalInfo($row_data ['clinic_id']) . '</td></tr>';
				} while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			} else {
				$str = '<tr><td colspan="6"><em>No data found for the search</em></td></tr>';
			}
		} else {
			$str = '<tr><td colspan="6"><em>Please enter a search text</em></td></tr>';
		}
		return $str;
	}
	
	function doDeleteUser($userid)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		if ($userid != $_SESSION ['staffID']) {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = (new MyDBConnector())->getPDO();
			$sql = "#DELETE FROM staff_directory WHERE staffId = " . $userid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if ($chk) {
				return 'success12';
			} else {
				return 'error:cant delete user';
			}
		} else {
			return 'error:cant delete user';
		}
	}
	
	function doStaffDisable($userid)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		//you can't disable yourself even if you have the right; right?
		if ($userid != $_SESSION ['staffID']) {
			$pdo = (new MyDBConnector())->getPDO();
			$sql = "UPDATE staff_directory SET `status` = 'disabled' WHERE staffId = " . $userid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if ($chk) {
				$ret = 'success:ok';
			} else {
				$ret = 'error:Failed to disable user';
			}
		} else {
			$ret = 'error:You cannot disable the selected user';
		}
		return $ret;
	}
	
	function doStaffEnable($userid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sdao = new StaffDirectoryDAO();
		
		if (count($sdao->getActiveUsersSlim($pdo)) > MainConfig::$numUsers) {
			return ('error:Number of active users exceeded');
		}
		
		$sql = "UPDATE staff_directory SET `status` = 'active' WHERE staffId = " . $userid;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();
		if ($chk) {
			return 'success:ok';
		} else {
			return 'error:cant enable user';
		}
	}
	
	function getStaffHospitalID($staffid, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$sql = "SELECT clinic_id FROM staff_directory WHERE staffId = " . $staffid;
		
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row_data ['clinic_id'];
	}
	
	function doCreateClinic($hospital, $hospital_address, $location_long, $location_lat, $class, $state, $lga, $hospcode = null)
	{
		//TODO: can we prevent somebody from creating more clinics?
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		if ((trim($hospital) == "") || (trim($hospital_address) == "") || (trim($class) == "")) {
			$ret = 'error:Check Empty Fields';
		} else {
			$sql = "INSERT INTO clinic (`name`,address,phone_no, location_lat,location_long,class,state_id, lga_id, hosp_code ) VALUES ('" . ucwords(escape($hospital)) . "','" . ucwords($hospital_address) . "','" . $location_long . "','" . $location_lat . "','" . $class . "','" . $state . "','" . $lga . "','" . $hospcode . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			//$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
			if (!$chk) {
				$ret = 'error:Database Error->';
			} else {
				$ret = 'success:Clinic created successfully';
			}
		}
		return $ret;
	}
	
	function changePassword($userid, $password, $mode = null)
	{
		//mode can be for the admin to "reset" another user's password
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "UPDATE staff_directory SET `pswd` = '" . password_hash($password, PASSWORD_BCRYPT) . "' WHERE staffId = " . $userid;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();
		if ($chk) {
			return 'success';
		} else {
			return 'error';
		}
	}
	
	function updateStaffProfile($ARR)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		
		$phone = $ARR['phonenumber'];
		if (trim($phone) == "" || !(preg_match('/^[+][2][3][4]+[0-9]{10}$/', $phone) || preg_match('/^[0]+[0-9]{10}$/', $phone) || preg_match('/^\+?(233|0)[235]\d{8}$/', $phone))) {
			return 'error:Invalid phone number';
		}
		if (!empty($ARR['email']) && !filter_var($ARR['email'], FILTER_VALIDATE_EMAIL)) {
			return 'error:Invalid Email Address';
		}
		//FIXME: validate email address as well
		$department_id = !is_blank($ARR['department_id']) ? $ARR['department_id'] : "NULL";
		$fname = !is_blank($ARR['fname']) ? quote_esc_str($ARR['fname']) : "NULL";
		$lname = !is_blank($ARR['lname']) ? quote_esc_str($ARR['lname']) : "NULL";
		$phonenumber = !is_blank($ARR['phonenumber']) ? quote_esc_str($ARR['phonenumber']) : "NULL";
		$email = !is_blank($ARR['email']) ? quote_esc_str($ARR['email']) : "NULL";
		$profession = !is_blank($ARR['profession']) ? quote_esc_str($ARR['profession']) : "NULL";
		$specialization = !is_blank($ARR['specialization']) ? quote_esc_str($ARR['specialization']) : "NULL";
		$sip_user_name = !is_blank($ARR['sip_username']) ? quote_esc_str($ARR['sip_username']) : "NULL";
		$sip_extension = !is_blank($ARR['sip_extension']) ? quote_esc_str($ARR['sip_extension']) : "NULL";
		$sip_password = !is_blank($ARR['sip_password']) ? quote_esc_str($ARR['sip_password']) : "NULL";
		$folioNumber = !is_blank($ARR['folio_number']) ? quote_esc_str($ARR['folio_number']) : "NULL";
		error_log( json_encode( $ARR ) );
		$is_consultant = $ARR['is_consultant'] == 1 ? 1 : 0;


		$id = escape($ARR['id']);
		
		$query = "UPDATE staff_directory SET firstname = $fname, lastname=$lname, phone=$phonenumber, sip_user_name=$sip_user_name, sip_extension=$sip_extension, sip_password=$sip_password, email=$email, profession=$profession, specialization_id=$specialization, department_id=$department_id, folio_number=$folioNumber , is_consultant=$is_consultant WHERE staffId = $id";
		$pdo = (new MyDBConnector())->getPDO();
		$stmt = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();
		if ($chk) {
			return 'success:Updated Profile OK';
		} else {
			return 'error:Failed to update profile';
		}
		
	}
	
	
	function getDoctorWhoSawWho($from = null, $to = null, $specialty_id = null, $staff_id = null, $page, $pageSize, $pdo = null)
	{
		$f = ($from == null) ? date("Y-m-d") : $from;
		$t = ($to == null) ? date("Y-m-d") : $to;
		$spid = ($specialty_id == null) ? '' : ' AND d.specialization_id=' . $specialty_id;
		$stid = ($staff_id == null) ? '' : ' AND d.doctor_id=' . $staff_id;
		
		$sql = "SELECT DISTINCT d.* FROM doctor_who_saw_who d WHERE d.type='Doctors' AND DATE(d.datetime) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$spid}{$stid}";
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
		$staffs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			//$sql = "SELECT d.* FROM doctor_who_saw_who d WHERE DATE(d.datetime) BETWEEN DATE('$f') AND DATE('$t'){$spid}{$stid} ORDER BY DATE(d.datetime) ASC LIMIT $offset, $pageSize";
			
			$sql = "SELECT DISTINCT d.id AS rowId, d.datetime AS dDate, CONCAT_WS(' ', s.firstname, s.lastname) AS staffName, p.staff_type AS specialization, dp.name AS departmentName, CONCAT_WS(' ', pd.fname, pd.mname, pd.lname) AS patientName, pd.sex, d.patient_id, sc.scheme_name AS schemeName, d.amount FROM doctor_who_saw_who d LEFT JOIN staff_directory s ON s.staffId=d.doctor_id LEFT JOIN staff_specialization p ON p.id=d.specialization_id LEFT JOIN departments dp ON dp.id=d.department_id LEFT JOIN patient_demograph pd ON pd.patient_ID=d.patient_id LEFT JOIN insurance_schemes sc ON d.scheme_id=sc.id WHERE DATE(d.datetime) BETWEEN DATE('" . $f . "') AND DATE('" . $t . "'){$spid}{$stid} ORDER BY DATE(d.datetime) ASC LIMIT $offset, $pageSize";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$staff = (object)null;
				$staff->Id = $row['rowId'];
				$staff->Doctor = $row['staffName']; //((new StaffDirectoryDAO())->getStaff($row['doctor_id'], FALSE, $pdo)) ? (new StaffDirectoryDAO())->getStaff($row['doctor_id'], FALSE, $pdo)->getFullname() : 'N/A';
				$staff->Patient = $row['patientName'];// (new PatientDemographDAO())->getPatient($row['patient_id'], FALSE, $pdo, TRUE)->getFullname();
				$staff->PatientID = $row['patient_id'];
				$staff->Sex = $row['sex'];
				$staff->Scheme = $row['schemeName'];//(new InsuranceSchemeDAO())->get($row['scheme_id'], FALSE, $pdo)?(new InsuranceSchemeDAO())->get($row['scheme_id'], FALSE, $pdo)->getName():'N/A';
				$staff->Specialization = $row['specialization']; //((new StaffSpecializationDAO())->getSpecialization($row['specialization_id'], $pdo)) ? (new StaffSpecializationDAO())->getSpecialization($row['specialization_id'], $pdo)->getName(): "N/A";
				$staff->Department = $row['departmentName']; //(new DepartmentDAO())->get($row['department_id'], $pdo) ? (new DepartmentDAO())->get($row['department_id'], $pdo)->getName():'N/A';
				//$staff->Type = $row['type'];
				$staff->Amount = $row['amount'];
				$staff->Date = $row['dDate'];
				$staffs[] = $staff;
			}
		} catch (PDOException $e) {
			$staffs = [];
		}
		$results = (object)null;
		$results->data = $staffs;
		$results->total = $total;
		$results->page = $page;
		
		return $results;
	}
}
