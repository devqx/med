<?php

class Manager
{
	public $idLength = 10;
	public $PAGE_RESULT_COUNT = 10;
	public $maxVaccineStages;

	/*public $vaccineStageLabels = array(
			'Birth' => "0|WEEK",
			'6 wks' => "6|WEEK",
			'10 wks' => "10|WEEK",
			'14 wks' => "14|WEEK",
			'9 months' => "9|MONTH",
			'12 months' => "12|MONTH",
			'15 months' => "15|MONTH",
			'18 months' => "18|MONTH",
			'2 years' => "2|YEAR",
			'3 years' => "3|YEAR",
			'10 years' => "10|YEAR",
			'11 years ' => "11|YEAR",
	);*/

	public $vaccineStageLabels = array('Birth' => "0|WEEK", '6 wks' => "6|WEEK", '10 wks' => "10|WEEK", '14 wks' => "14|WEEK", '6 months' => "6|MONTH", '9 months' => "9|MONTH", '12 months' => "12|MONTH", '15 months' => "15|MONTH", '18 months' => "18|MONTH", '2 years' => "2|YEAR", '3 years' => "3|YEAR", '10 years' => "10|YEAR", '11 years' => "11|YEAR", '12 yrs' => "12|YEAR", 'Above 12' => "13|YEAR");
	public $ERROR_PATIENT_INVALID_AGE_FOR_VACCINE = "<div class='notify-bar'><span>*</span> <em>There's no vaccine to show for this patient, at this time</em></div>";
	public $ERROR_PATIENT_NO_DUE_VACCINE = '<span>*</span> <em>No due vaccine</em>';
	public $STAFF;
	public $ROLE_ACCESS_DENIED = '<div class="warning-bar">You do not have the rights to access this function. Please contact your administrator.</div>';
	public static $SESSION_EXPIRED = '<div class="warning-bar">Your session has expired. You can login in another tab, and then re-submit this form, so as to preserve data</div>';

	function __construct()
	{
		date_default_timezone_set("Africa/Lagos");
		if (!isset($_SESSION)) {
			@session_start();
		}
		$this->maxVaccineStages = $this->getMaxVaccinesCount();
		require_once "class.staff.php";
		require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
		$this->STAFF = new StaffManager();
	}

	function doFindPatientForEnrollment($patientInfo, $type = null, $pdo = null)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LabourEnrollmentDAO.php';
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/IVFEnrollmentDAO.php';
		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) {
			return $protect->ACCESS_DENIED;
		}
		$retVal = '';
		try {
			$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
			$sql = "SELECT * FROM patient_demograph WHERE (fname  LIKE '%" . $patientInfo . "%' OR lname LIKE '%" . $patientInfo . "%' OR patient_ID LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '%" . $patientInfo . "%')";
			$sql .= ($type == 'antenatal' || $type == 'labour' || 'ivf') ? ' AND sex="female" ' : '';

			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

			do {
				$pat = (new PatientDemographDAO())->getPatient($row['patient_ID'], FALSE, $pdo);
				if ($pat) {
					if ($type == 'immunization' && !$this->isImmunization($row ['patient_ID']) /*&& $this->getPatientAgeInMonths($patientInfo) <= (18 * 12)*/) { //patients that are 18 yrs old and below
						$retVal .= '<tr><td><input type="checkbox" name="patient_to_be_enrolled[]" value="' . $row ['patient_ID'] . '"></td>';
						$retVal .= '<td><a href="/patient_profile.php?id=' . $row ['patient_ID'] . '">' . $row ['patient_ID'] . '</a></td><td>' . $row ['fname'] . '</td><td>' . $row ['lname'] . '</td><td>' . $row ['sex'] . '</td><td>' . $row ['date_of_birth'] . '</td><td>' . $row ['phonenumber'] . '</td></tr>';
					} else if ($type == 'antenatal' && !$this->isAntenatal($row ['patient_ID'])) {
						$retVal .= '<tr><td><a data-title="Antenatal Enrollment: ' . $pat->getFullname() . '" href="javascript:;" data-id="' . $row ['patient_ID'] . '" class="action enrollBtn">Enroll</a></td>';
						$retVal .= '<td><a href="/patient_profile.php?id=' . $row ['patient_ID'] . '">' . $row ['patient_ID'] . '</a></td><td>' . $row ['fname'] . '</td><td>' . $row ['lname'] . '</td><td>' . ucwords($row ['sex']) . '</td><td>' . $row ['date_of_birth'] . '</td><td>' . $row ['phonenumber'] . '</td></tr>';
					} else if ($type == 'labour' && !(new LabourEnrollmentDAO())->isEnrolled($row ['patient_ID'])) {
						$retVal .= '<tr><td><a data-patient="' . ucwords($pat->getFullname()) . '" data-title="Labour Mgt Enrollment: ' . $pat->getFullname() . '" href="javascript:;" data-id="' . $row ['patient_ID'] . '" class="btn enrollBtn">Enroll</a></td>';
						$retVal .= '<td><a href="/patient_profile.php?id=' . $row ['patient_ID'] . '">' . $row ['patient_ID'] . '</a></td><td>' . $row ['fname'] . '</td><td>' . $row ['lname'] . '</td><td>' . ucwords($row ['sex']) . '</td><td>' . $row ['date_of_birth'] . '</td><td>' . $row ['phonenumber'] . '</td></tr>';
					} else if ($type == 'sti' /*&& !(new LabourEnrollmentDAO())->isEnrolled($row ['patient_ID'])*/) {
						$retVal .= '<tr><td><a data-patient="' . ucwords($pat->getFullname()) . '" data-title="STI Clinic Enrollment: ' . $pat->getFullname() . '" href="javascript:;" data-id="' . $row ['patient_ID'] . '" class="btn enrollBtn">Enroll</a></td>';
						$retVal .= '<td><a href="/patient_profile.php?id=' . $row ['patient_ID'] . '">' . $row ['patient_ID'] . '</a></td><td>' . $row ['fname'] . '</td><td>' . $row ['lname'] . '</td><td>' . ucwords($row ['sex']) . '</td><td>' . $row ['date_of_birth'] . '</td><td>' . $row ['phonenumber'] . '</td></tr>';
					} else if ($type == 'ivf' && !(new IVFEnrollmentDAO())->getActiveInstance($row ['patient_ID'], FALSE, $pdo)) {
						$retVal .= '<tr><td><a data-patient="' . ucwords($pat->getFullname()) . '" data-title="IVF Clinic Enrollment: ' . $pat->getFullname() . '" href="javascript:;" data-id="' . $row ['patient_ID'] . '" class="btn enrollBtn">Enroll</a></td>';
						$retVal .= '<td><a href="/patient_profile.php?id=' . $row ['patient_ID'] . '">' . $row ['patient_ID'] . '</a></td><td>' . $row ['fname'] . '</td><td>' . $row ['lname'] . '</td><td>' . ucwords($row ['sex']) . '</td><td>' . $row ['date_of_birth'] . '</td><td>' . $row ['phonenumber'] . '</td></tr>';
					}
				}
			} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			if ($stmt->rowCount() == 0) {
				return '<tr><td colspan="7"><em class="notify-bar">No Result for the search !</em></td></tr>';
			}
			return $retVal;
		} catch (PDOException $e) {
			errorLog($e);
			return '<tr><td colspan="7"><em class="warning-bar">Error Occurred during search</em></td></tr>';
		}
	}

	function doFindPatient($patientInfo, $type = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$patientInfo_ = quote_esc_str($patientInfo);
		$patientInfo = escape($patientInfo);

		//todo refine the search:
		//two parameters every time: search mode: antenatal|labour|immunization
		//and the patient details or empty search details

		$aiSearch = "";// !is_blank($patientInfo) ? "MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ('".$patientInfo."' IN BOOLEAN MODE) AS Relevance, ":"";
		if ($type == "antenatal") {
			//$sql = "SELECT e.id AS instance, d.patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex FROM patient_demograph d LEFT JOIN enrollments_antenatal e ON d.patient_ID=e.patient_id WHERE d.active=1 AND e.active IS TRUE AND d.sex='female'";
			$sql = "SELECT {$aiSearch}e.id AS instance, d.patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex FROM patient_demograph d LEFT JOIN enrollments_antenatal e ON d.patient_ID=e.patient_id WHERE d.active=1 AND e.active IS TRUE AND d.sex='female'";
			if (!is_blank($patientInfo)) {
				$sql .= " AND (fname  LIKE '%" . $patientInfo . "%' OR mname  LIKE '%" . $patientInfo . "%' OR lname LIKE '%" . $patientInfo . "%' OR d.patient_ID LIKE '%" . $patientInfo . "%' OR legacy_patient_id LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '%" . $patientInfo . "%')";
				//$sql .= " AND (d.patient_ID=$patientInfo_ OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ($patientInfo_ IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			}
		} else if ($type == "labour") {
			$sql = "SELECT {$aiSearch}e.id AS instance, d.patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex FROM patient_demograph d LEFT JOIN enrollments_labour e ON d.patient_ID=e.patient_id WHERE d.active=1 AND e.active IS TRUE AND d.sex='female'";
			if (!is_blank($patientInfo)) {
				$sql .= " AND (fname  LIKE '" . $patientInfo . "%' OR mname  LIKE '" . $patientInfo . "%' OR lname LIKE '" . $patientInfo . "%' OR d.patient_ID LIKE '%" . $patientInfo . "%' OR legacy_patient_id LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '" . $patientInfo . "%')";
				//$sql .= " AND (d.patient_ID=$patientInfo_ OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ($patientInfo_ IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			}
		} else if ($type == "immunization") {
			$sql = "SELECT {$aiSearch}NULL AS instance, d.patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex FROM patient_demograph d LEFT JOIN enrollments_immunization e ON d.patient_ID=e.patient_id WHERE d.active=1 AND e.enrolled_on IS NOT NULL";
			if (!is_blank($patientInfo)) {
				$sql .= " AND (fname  LIKE '" . $patientInfo . "%' OR mname  LIKE '" . $patientInfo . "%' OR lname LIKE '" . $patientInfo . "%' OR d.patient_ID LIKE '%" . $patientInfo . "%' OR legacy_patient_id LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '" . $patientInfo . "%')";
				//$sql .= " AND (d.patient_ID=$patientInfo_ OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ($patientInfo_ IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			}
		}  else if ($type == "ivf") {
			$sql = "SELECT {$aiSearch}e.id AS instance, d.patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex FROM patient_demograph d LEFT JOIN enrollments_ivf e ON d.patient_ID=e.patient_id WHERE d.active=1 AND e.active IS TRUE";
			if (!is_blank($patientInfo)) {
				$sql .= " AND (fname  LIKE '" . $patientInfo . "%' OR mname  LIKE '" . $patientInfo . "%' OR lname LIKE '" . $patientInfo . "%' OR d.patient_ID LIKE '%" . $patientInfo . "%' OR legacy_patient_id LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '" . $patientInfo . "%')";
				//$sql .= " AND (d.patient_ID=$patientInfo_ OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ($patientInfo_ IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			}
		} else {
			$sql = "SELECT {$aiSearch}patient_ID, PATIENT_SCHEME(d.patient_ID) AS patientScheme, fname, mname, lname, date_of_birth, MASK(phonenumber) AS phonenumber, sex, NULL AS instance FROM patient_demograph d WHERE active=1";
			if (!is_blank($patientInfo)) {
				$sql .= " AND (fname  LIKE '" . $patientInfo . "%' OR mname  LIKE '" . $patientInfo . "%' OR lname LIKE '" . $patientInfo . "%' OR d.patient_ID LIKE '%" . $patientInfo . "%' OR legacy_patient_id LIKE '%" . $patientInfo . "%' OR phonenumber LIKE '" . $patientInfo . "%')";
				//$sql .= " AND (d.patient_ID=$patientInfo_ OR MATCH (`fname`, `lname`, `legacy_patient_id`, `mname`, `phonenumber`) AGAINST ($patientInfo_ IN BOOLEAN MODE)) ORDER BY Relevance DESC";
			}
		}
		//error_log($sql);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();

		$retVal = "";
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		switch ($type) {
			case "labour":
				$lp = [];
				if ($stmt->rowCount() >= 1) {
					$lp['error'] = FALSE;
					while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
						$patient = (new PatientDemographDAO())->getPatient($row_data ['patient_ID'], FALSE);

						$lp_data = (object)null;
						$lp_data->EMRID = $patient->getId();
						$lp_data->Name = $patient->getFullname();
						$lp_data->Sex = $patient->getSex();

						$lp['data'][] = $lp_data;
					}
				} else {
					if ($stmt->rowCount() < 1) {
						$lp['error'] = TRUE;
						$lp['data'] = 'No Patient Found!';
					}
				}
				return $lp;
				break;
			default:
				$protect = new Protect();
				$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
				if ($stmt->rowCount() >= 1) {
					// PatientID | First Name | middle Name | Last Name | Age #
					do {
						$instance = "";
						if (isset($row_data['instance']) && $row_data['instance'] != null) {
							$instance = "&aid=" . $row_data['instance'];
						}
						$retVal .= '<tr><td><img class="profile_thumbnail" alt="img" src="' . $this->getPatientImage($row_data['patient_ID']) . '"/></td><td>
						<a href="' . ($type != null ? 'patient_' . $type . '_profile.php' : 'patient_profile.php') . '?id=' . $row_data ['patient_ID'] . $instance . '">' . $row_data ['patient_ID'] . '</a></td><td>' . $row_data ['fname'] . '</td>
				<td>' . $row_data ['mname'] . '</td><td>' . $row_data ['lname'] . '</td><td>' . $row_data ['sex'] . '</td><td data-dob="true">' . $row_data ['date_of_birth'] . '</td><td>' . $row_data ['phonenumber'] . '</td><td>' . $row_data['patientScheme'] . '</td>
				<td>' . (($type == null && $this_user->hasRole($protect->nurse)) ? '<a class="action round_btn" href="javascript:void(0)" title="start New Visit" onclick="Boxy.load(\'/boxy.startnewvisit.php?id=' . $row_data ['patient_ID'] . '\',{title: \'Start New Visit\'});"><i class="icon-edit"></i></a>' : '') . (($type == "immunization" && $this_user->hasRole($protect->nurse)) ? '<a class="action round_btn" title="Prepare Due vaccines" href="javascript:void(0)" onclick="Boxy.load(\'/immunization/boxy.prepare_due_vaccines.php?id=' . $row_data ['patient_ID'] . '\',{title:\'Prepare Due Vaccines\'})"><i class="icon-play"></i></a>' : '') . '</td></tr>';
					} while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
					return $retVal;
				} else {
					if ($stmt->rowCount() < 1) {
						return '<tr><td colspan="10"><em>No Data Found !</em></td></tr>';
					}
				}
		}
		return 0;
	}

	function getPatientImage($pID)
	{
		if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/profiles/" . $pID . "_profile.jpg")) {
			$img = "/img/profiles/" . $pID . "_profile.jpg";
		} else if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/img/profiles/" . (int)$pID . "_profile.jpg")) {
			$img = "/img/profiles/" . (int)$pID . "_profile.jpg";
		} else {
			$img = "/img/profiles/" . $this->getPatientSex($pID) . ".jpg";
		}
		return $img;
	}

	function in8nPhone($phone_)
	{
		//TODO: get the dialling code of this patient's' country
		//FIX, let it be Nigerian numbers only
		$rPLUS = "/^\+/";
		$phone = preg_replace($rPLUS, "", $phone_); //strip the plus, if there
		$ptn = "/^0/"; // Regex
		$str = $phone; //
		$rpltxt = "234"; // Replacement string
		$i81n_phone = preg_replace($ptn, $rpltxt, $str);
		if (strlen($i81n_phone) != 13) {
			exit("SMS to phone number:$i81n_phone not supported");
		} else {
			return $i81n_phone;
		}
	}

	static function getPatientName($patientID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDemographDAO.php";
		$patient = (new PatientDemographDAO())->getPatient($patientID, FALSE, null, "TRUE");
		return $patient->getFullname();
	}

	function getPatientAgeInDays($patientID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';

		$sql = "SELECT DATEDIFF(NOW(), date_of_birth)  AS age FROM patient_demograph WHERE patient_ID = '" . $patientID . "'";
		$pdo = (new MyDBConnector())->getPDO();
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$retVal = $row_data ['age'];
		return $retVal;
	}

	function getPatientAgeInMonths($patientID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM CURRENT_DATE()), EXTRACT(YEAR_MONTH FROM (SELECT date_of_birth FROM patient_demograph WHERE patient_ID = $patientID))) AS age";

		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$retVal = $row_data ['age'];
		return $retVal;
	}

	//TODO: this function now returns array properly.
	//retrace all references to it and make adjustments
	function getPatientInfo($patientID, $returnAllData = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM patient_demograph WHERE patient_ID = " . $patientID;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();

		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

		if ($returnAllData == null) {
			$data['fname'] = $row_data ['fname'];
			$data['lname'] = $row_data ['lname'];
			$data['name'] = $row_data ['fname'] . ', ' . $row_data ['lname'];
			$data['date_of_birth'] = date("jS M, Y", strtotime($row_data ['date_of_birth']));
			$data['sex'] = $row_data ['sex'];
			$data['email'] = $row_data ['email'];
			$data['address'] = $row_data ['address'];
			$data['phonenumber'] = $row_data ['phonenumber'];

			return $data;
		} else {
			return $row_data;
		}
	}

	function getPatientSex($patientID)
	{
		$row = $this->getPatientInfo($patientID);
		return $row['sex'];
	}

	function generatePatientID()
	{
		$genID = '';
		for ($i = 1; $i <= $this->idLength; $i++) {
			$genID .= rand(0, 9);
		}
		return $genID;
	}

	function getPatientVisitNotes($patientID, $type = null)
	{
		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->doctor_role) && !$this_user->hasRole($protect->nurse)) {
			return $protect->ACCESS_DENIED;
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();

		$sql = "SELECT patient_visit_notes.id AS noteID, patient_visit_notes.date_of_entry AS w, patient_visit_notes.note_type AS ntype, patient_visit_notes.description AS description,	patient_visit_notes.noted_by AS who	FROM patient_visit_notes WHERE patient_visit_notes.patient_ID =" . $patientID . "	/*UNION	SELECT patient_diagnoses.date_of_entry AS w, patient_diagnoses.`diag-type` AS ntype, patient_diagnoses.diagnosis AS description, patient_diagnoses.diagnosed_by AS who FROM patient_diagnoses WHERE patient_diagnoses.patient_ID ='" . $patientID . "'*/ " . (($type == "antenatal") ? " AND reason='antenatal'" : "") . " ORDER BY w DESC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$total_items = $stmt->rowCount();
		$limit = $this->PAGE_RESULT_COUNT; //records per page
		$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
		$total_pages = ceil($total_items / $limit);
		$set_limit = ($page * $limit) - $limit;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();

		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

		if ($stmt->rowCount() < 1) {
			$retVal = '<div class="warning-bar">This patient does not have any ' . (($type != null) ? $type : '') . ' visit record </div>';
		} else {
			$retVal = '<table class="table table-striped" id="visits">';
			$retVal .= '<thead><tr><th align="left" class="nowrap">Date</th><!--<th>Type</th>--><th align="left">Notes</th><th align="left">Noted By</th></tr></thead>';
			require_once 'class.assessments.php';
			$assessment = new Assessments();
			$_SESSION['w'] = null;
			do {

				// date | type | description | by #
				$nType = $row_data ['ntype'];

				$edit_option = ((date("d/m/Y", strtotime($row_data ['w'])) == date("d/m/Y")) && $row_data ['who'] == $this_user->getId()) ? ' | <a href="javascript:;" onClick="Boxy.load(\'/boxy.doctor.note.edit.php?note_id=' . $row_data['noteID'] . '\',{title:\'Edit Doctor Note\'})">Edit</a>' : '';

				$retVal .= '<tr' . ((date("jS M, Y", strtotime($row_data ['w'])) != $_SESSION['w']) ? ' style="border-top:solid 2px #bbb"' : '') . '><td class="nowrap">' . date("jS M, Y", strtotime($row_data ['w'])) . '</td><!--<td>' . ($noteType) . '</td>--><td>';
				if ($nType == 'o') {
					$retVal .= $assessment->formatObjectiveNote($row_data ['description']);
				} else {
					if ($nType == 'd') {
						//error_log(json_encode(array(date("d/m/Y", strtotime($row_data ['w'])), date("d/m/Y"), $row_data ['who'], $this_user->getId())));
						$retVal .= '<span class="doc_note">Doc Note:</span>' . $row_data ['description'] . $edit_option;
					} else {
						if ($nType == 'p') {
							$retVal .= '<span class="plan_note">Plan:</span>' . $row_data['description'] . $edit_option;
						} else {
							if ($nType == 'a') {
								$retVal .= '<span class="diag_note">Diagnosis:</span>' . $row_data['description'];
							} else {
								if ($nType == 'i') {
									$retVal .= '<span class="inv_note">Investigation:</span>' . $row_data['description'];
								} else {
									if ($nType == 'g') {
										$retVal .= $row_data['description'];
									} else {
										if ($nType == 'e') {
											$retVal .= '<span class="inv_note">Examination:</span>' . $row_data['description'];
										} else {
											if ($nType == 'r') {
												$retVal .= '<span class="ref_note">Referral:</span>' . $row_data['description'];
											} else {
												if ($nType == 'v') {
													$retVal .= '<span class="review_note">Systems Review:</span>' . $row_data['description'];
												} else {
													if ($nType == 'x') {
														$retVal .= '<span class="review_note">Physical Exam:</span>' . $row_data['description'];
													} else {
														$retVal .= '<span class="com_note">Complaint:</span>' . $row_data['description'] . $edit_option;
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}
				$retVal .= '</td><td valign="top">' . $this->STAFF->getDoctorNameFromID($row_data ['who'], 0, 'adv') . '</td></tr>';
				$_SESSION['w'] = date("jS M, Y", strtotime($row_data ['w']));
			} while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			unset($_SESSION['w']);
			$retVal .= '</table>';
		}
		return $retVal;
	}

	function getPatientCounsellings($patient)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$counsellings = array();
		$sql = "SELECT a.`id`, a.`entry_date`, b.description AS `lmp_pmtct_link`, c.`description` AS `fp_method` FROM  hiv_counsellings a LEFT JOIN lmp_pmtct_links b ON b.id = a.lmp_pmtct_link_id LEFT JOIN fp_methods c ON c.id = a.fp_method_id WHERE patient_id = '" . escape($patient) . "' ";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();
		while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$counsellings[] = $row_data;
		}
		return $counsellings;
	}

	function getPatientPrimaryHealthCareCenterMobile($patientID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT c.clinicID AS id, c.FullName AS name FROM patient_demograph p LEFT JOIN clinic c ON p.transferedto=c.clinicID WHERE p.patient_ID = '$patientID'";
		//        error_log($sql);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row_data;
	}

	//	 ADDED:
	function getPatientVaccineMap($patientID)
	{
		if (!isset($_SESSION)) {
			@session_start();
		}
		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->records) && !$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->doctor_role)) // TODO: if a staff that has access or the patient who is logged in
		{
			return $protect->ACCESS_DENIED;
		}
		if (isset($_SESSION['staffID']) || isset($_SESSION['patientID'])) {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			$pdo = (new MyDBConnector())->getPDO();
			$query = "SELECT * FROM patient_vaccine WHERE is_booster IS FALSE AND patient_id =" . $patientID;
			$stmt = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$count = $stmt->rowCount();
			if ($count > 0) {
				$sql = "SELECT * FROM vaccines WHERE active='1'";
				$stmt1 = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt1->execute();
				$fields_arr = $stmt1->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
				$fields_label = $fields_id = $fields_descr = array();
				do {
					$fields_label [] = $fields_arr ['label'];
					$fields_id [] = $fields_arr ['id'];// todo use this as $i always
					$fields_descr [] = $fields_arr ['description'];
				} while ($fields_arr = $stmt1->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));

				$maxVaccineStages = $this->maxVaccineStages;
				$ret = '<table id="vTable" class="table table-hover table-bordered table-striped">';
				$ret .= '<thead><tr><th>Vaccine</th>';

				$stageLabels = array_keys($this->vaccineStageLabels);

				for ($i = 0; $i < $maxVaccineStages; $i++) {
					$ret .= '<th>' . $stageLabels [$i] . '</th>';
				}

				$ret .= '</tr></thead><tbody>';
				for ($i = 1; $i <= count($fields_label); $i++) {
					$ret .= '<tr><td class="fixedColumn">' . $fields_label [$i - 1] . ' (' . $fields_descr [$i - 1] . ')</td>';
					for ($j = 1; $j <= $maxVaccineStages; $j++) {
						$ret .= $this->getVaccineDetail($patientID, $fields_id[$i - 1], $j);
					}
					$ret .= '</tr>';
				}
				$ret .= '</tbody></table>';
			} else {
				$ret = $this->ERROR_PATIENT_INVALID_AGE_FOR_VACCINE;
			}
		} else {
			$ret = 'Your session has expired. Please <a href>Login</a> again.';
		}
		return $ret;
	}

	function getPatientBoosterVaccines($pid)
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		if (!isset($_SESSION['staffID'])) {
			return (new Protect())->SESSION_EXPIRED;
		}
		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->records) && !$this_user->hasRole($protect->nurse)) {
			return $protect->ACCESS_DENIED;
		}

		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM patient_vaccine WHERE is_booster IS TRUE AND patient_id =" . $pid;

		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return json_encode($data);
	}

	function getPatientVaccineReminder($id)
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		if (!isset($_SESSION['staffID'])) {
			return (new Protect())->SESSION_EXPIRED;
		}

		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) {
			return $protect->ACCESS_DENIED;
		}

		require $_SERVER['DOCUMENT_ROOT'] . '/classes/MessageQueue.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$msgQs = array();
		$sql = "SELECT * FROM message_queue_temp WHERE patient=" . $id . " ORDER BY date_generated";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		if ($stmt->rowCount() >= 1) {
			do {
				$msgQs[] = new MessageQueue($row_data['id'], $row_data['source'], $row_data['message_content'], $row_data['message_status'], $row_data['patient'], $row_data['date_generated']);
			} while ($row_data = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
			return $msgQs;
		} else {
			if ($stmt->rowCount() < 1) {
				return array();
			}
		}
		return array();
	}

	// ADDED:
	function getMaxVaccinesCount()
	{
		return count($this->vaccineStageLabels);
	}

	// ADDED:
	function getVaccineDetail($pid, $v_id, $level)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		if (!isset ($_SESSION ['taken_id'])) {
			$_SESSION ['taken_id'] = 0;
		}
		if (!isset ($_SESSION ['colspan'])) {
			$_SESSION ['colspan'] = 0;
		}
		// check and get if the vaccine has been taken, missed or is due
		// to be taken
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT vaccine_levels.id AS id2, vaccines.label, vaccine_levels.* FROM `vaccine_levels` JOIN vaccines ON vaccines.id=vaccine_levels.vaccine_id WHERE vaccine_levels.`vaccine_id` = " . $v_id . " AND  vaccine_levels.`start_index` <= " . ($level - 1) . " AND  vaccine_levels.`end_index` >= " . $level . " AND vaccine_levels.id <> " . $_SESSION ['taken_id'] . " LIMIT 1";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result = $stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$colspan = $row ['end_index'] - $row ['start_index'];
		$ret = '';
		if ($stmt->rowCount() > 0) {
			$_SESSION ['taken_id'] = $row ['id2'];
			$_SESSION ['colspan'] = $colspan;
			return '<td colspan="' . $colspan . '">' . $this->formatVaccineDoneOrNot($pid, $v_id, $row['level']) . '</td>';
		} else {
			$_SESSION['colspan'] = $_SESSION['colspan'] - 1;
			//subtract 1 from the num. of cols. spanned then loop over it.
			//maybe the loop isnt necessary
			for ($i = ($_SESSION ['colspan']); $i <= 0; $i--) {
				return '<td class="empty">&nbsp;x&nbsp;</td>';
			}
		}
		return '';
	}

	// ADDED:
	function formatVaccineDoneOrNot($pid, $v_id, $level)
	{ // or missed
		$patient_age_in_month = $this->getPatientAgeInMonths($pid);
		$vName = "";
		$level_ = "";
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT vaccines.label, vaccine_levels.* FROM `vaccine_levels` JOIN vaccines ON vaccines.id=vaccine_levels.vaccine_id WHERE vaccine_levels.`vaccine_id` = " . $v_id;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

		// do the grouping of bars by the level
		do {
			$vName = $row ['label'];
			//$level_ = $row ['level'];
		} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
		$sql2 = "SELECT * FROM patient_vaccine WHERE `patient_id` = " . $pid . " AND vaccine_id = " . $v_id . " AND vaccine_level = " . $level;
		$stmt2 = $pdo->prepare($sql2, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result2 = $stmt2->execute();

		$row2 = $stmt2->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$status = null;
		$color = null;
		$route = null;
		$dosage = null;
		do {
			$date_taken = $row2 ['real_administer_date'];
			$expiration_date = $row2 ['expiration_date'];
			$due_date = $row2 ['due_date'];
			$take_type = $row2['take_type'];
			$takenBy = $row2['taken_by'];
			$internal = $row2['internal'];
			$id = $row2['id'];
			$route = $row2['route'];
			$dosage = $row2['dosage'];
		} while ($row2 = $stmt2->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));

		$now = strtotime(date("Y-m-d", time()));

		if ($date_taken != null) {
			$status = 'done';
			$color = 'green';
		} else if (strtotime($due_date) <= $now && strtotime($expiration_date) >= $now) {
			$status = 'notdone';
			$color = 'yellow';
		} else if (strtotime($due_date) <= $now && strtotime($expiration_date) <= $now) {
			$status = 'overdue';
			$color = 'red';
		} else {
			$status = 'notregistered';//not due actually
			$color = 'gray';
		}
		// 		return '<div class="vaccine-block ' . $color . '">' .$status.$v_l.'?'. $vname.'?' .(($status == 'done')?('<small><em style="color:#fff">'.date("jS M, Y",strtotime($date_taken))).'</em></small>':''). '</div>';
		return '<div data-id="' . $id . '" class="vaccine-block ' . $color . '' . ($status == 'done' ? ' done ' : '') . ' type_' . $take_type . '' . ($internal == 1 ? ' internal' : '') . '" title="' . $this->formatVaccineToolTip($vName, $level, $status, $date_taken, $internal, $route, $dosage, $takenBy) . '">' . (($status == 'done') ? date("d/m/y", strtotime($date_taken)) : "&nbsp;") . '</div>';

	}

	function getPatientAvailableVaccines($pid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM patient_vaccine WHERE  patient_id=$pid";// TODO: get the due vaccines for the patient that are missed or taken?
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = [];
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return $data;
	}

	function formatVaccineToolTip($vName, $stage, $status, $real_administer_date, $internal, $route, $dosage, $takenBy)
	{
		$ret = null;
		if ($status == "done") {
			$ret .= $vName . ', stage ' . $stage . '; ' . strtoupper($status) . '<br>';
			if ($internal) {
				$ret .= '<em>Shot administered</em>: HERE<br>';
			} else {
				$ret .= '<em>Shot administered</em>: EXTERNAL<br>';
			}
			$ret .= 'by ' . (new StaffDirectoryDAO())->getStaff($takenBy, FALSE)->getFullname() . '<br>';
			$ret .= '<em>Shot date</em>: ' . date("jS M, Y", strtotime($real_administer_date)) . '<br>';
			$ret .= 'Route: ' . $this->getRouteName($route) . '; ' . (trim($dosage) != "" ? $dosage : 0) . 'ml';
			return $ret;
		}
		return $vName . ', stage ' . $stage . '; ' . strtoupper($status);
	}

	function getRouteName($value)
	{
		if (!class_exists('Vaccine')) {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
		}
		if (trim($value) != "") {
			return Vaccine::$routes[$value];
		} else {
			return "N/A";
		}
	}

	function savePatientVisitNote($pid, $note, $type)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$hospid = $this->getStaffHospitalID($_SESSION ['staffID'], $pdo);
		if (trim($note) == "") {
			return 'Enter the note description';
		} else {
			$ntype = '';
			if ($type == 'obj') {
				$ntype = 'o';
			} else if ($type == 'subj') {
				$ntype = 's';
			} else if ($type == 'asst') {
				$ntype = 'a';
			} else if ($type == 'plan') {
				$ntype = 'p';
			} else if ($type == 'doc') {
				$ntype = 'd';
			} else if ($type == 'inv') {
				$ntype = 'i';
			} else if ($type == 'diag_note') {
				$ntype = 'g';
			} else if ($type == 'exam') {
				$ntype = 'e';
			} else if ($type == 'ref') {
				$ntype = 'r';
			} else if ($type == 'revw') {
				$ntype = 'v';
			} else if ($type == 'ph_ex') {
				$ntype = 'x';
			}
		}

		$sourceApp = (isset($_SERVER['HTTP_X_APP']) ? "'" . $_SERVER['HTTP_X_APP'] . "'" : 'NULL'); //used to track the source.//th X-App header was set in the ajax to save note from the mobile app
		$sql = "INSERT INTO patient_visit_notes (patient_ID, noted_by, description, `note_type`, hospitalvisited, sourceapp) VALUES ('" . escape($pid) . "','" . escape($_SESSION ['staffID']) . "','" . escape($note) . "', '$ntype','$hospid',$sourceApp)";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk = $stmt->execute();
		if ($chk) {
			return 'ok';
		} else {
			return 'error:Failed to save visit note';
		}
	}

	/*function savePatientAllergen($pid, $allergen, $reaction, $severity)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		if (trim($allergen) != "" && trim($reaction) != "") {
			$hospid = $this->getStaffHospitalID($_SESSION ['staffID'], $pdo);

			$sql = "INSERT INTO patient_allergen (patient_ID, allergen, reaction, severity, noted_by,hospid, category_id, drug_super_gen_id) VALUES ('" . $pid . "','" . $allergen . "','" . $reaction . "','" . $severity . "','" . $_SESSION ['staffID'] . "'," . $hospid . ", '". $categoy_id ."')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if ($chk) {
				return 'ok';
			} else {
				return 'error:Failed to save allergen';
			}
		} else {
			return 'error:Allergen description required';
		}
	}*/

	function savePatientPreCondition($pid, $condition, $diag_date, $severity, $therapy, $response, $therapy_start_date, $history = 0)
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
		if (!isset($_SESSION['staffID'])) {
			return 'error:Your session has expired';
		}
		$error = "";
		if (!trim($condition) != "") {
			$error = "error:Enter Condition";
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		if ($error == "") {
			$hospid = $this->getStaffHospitalID($_SESSION ['staffID'], $pdo);

			$sql = "INSERT INTO `patient_pre_conditions` (`patient_id`,	`_condition`,`diag_date` ,`severity` ,`therapy` ,`response` ,`therapy_start_date`, `history`, `entered_by` ,`date_entered`,hospid) VALUES (" . $pid . ", '" . $condition . "', '" . $diag_date . "', '" . $severity . "', '" . $therapy . "', '" . $response . "', '" . $therapy_start_date . "', '" . $history . "', '" . $_SESSION ['staffID'] . "', CURRENT_TIMESTAMP," . $hospid . " )";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if ($chk) {
				return 'ok';
			} else {
				return 'error:Failed to save precondition';
			}
		} else {
			return $error;
		}
	}

	function getStaffHospitalID($staffid, $pdo = null)
	{
		require_once 'class.staff.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		return (new StaffManager())->getStaffHospitalID($staffid, $pdo);
	}

	function saveVitalSign($type, $patientID, $value, $inPatientId = null, $pdo = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.vitals.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AlertDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Alert.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

		$pdo = $pdo == null ? (new MyDBConnector())->getPDO() : $pdo;
		$hospid = $this->getStaffHospitalID($_SESSION ['staffID'], $pdo);

		$types = ["pulse", "rp", "bp", "temp", "weight", "height", "bmi", "fundus_height", "glucose", "protein", "fhr", "dilation", "mid-arm-circumference", "head-circumference", "length-of-arm", "pcv", "urine", "spo2", "pain_scale"];

		if (trim($value) == '') {
			return 'error:What of value ?';
		} else if (in_array($type, $types)) {
			if ($type == 'pulse') {
				if (is_real_number($value)) {
					$sql = "INSERT INTO vital_sign (patient_id, `value`, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "','" . escape($value) . "'," . $hospid . ", 'Pulse', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "' )";
					if ($value > VitalsConfig::$maxPulse || $value < VitalsConfig::$minPulse) {
						$alert = new Alert();
						$alert->setMessage("Pulse Value ($value) is not within the NORMAL range");
						$alert->setType("Pulse");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));

						@(new AlertDAO())->add($alert, $pdo);
					}
				} else {
					return 'error:Pulse must be numeric';
				}
			} else if ($type == 'rp') {
				if (is_real_number($value)) {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "'," . escape($value) . "," . $hospid . ", 'Respiration', " . ($inPatientId != null ? $inPatientId : 'NULL') . " , '" . $_SESSION ['staffID'] . "')";
				} else {
					return 'error:Respiratory rate must be numeric';
				}
			} else if ($type == 'bp') {
				if (preg_match('/(\d+)\/(\d+)/', $value)) {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "','" . $value . "'," . $hospid . ", 'Blood Pressure', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "' )";

					$bp = explode("/", $value);
					$bp_min_range = explode("/", VitalsConfig::$minNormalBP);
					$bp_max_range = explode("/", VitalsConfig::$maxNormalBP);
					$abnormal_high = ($bp[0] >= $bp_max_range[0] || $bp[1] >= $bp_max_range[1]) ? TRUE : FALSE;
					$abnormal_low = ($bp[0] <= $bp_min_range[0] || $bp[1] <= $bp_min_range[1]) ? TRUE : FALSE;
					$abnormal = ($abnormal_high === TRUE || $abnormal_low === TRUE) ? TRUE : FALSE;

					if ($abnormal === TRUE) {
						$alert = new Alert();
						$alert->setMessage("B/P Value ($value) is not within the NORMAL range");
						$alert->setType("Blood Pressure");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));

						@(new AlertDAO())->add($alert, $pdo);
					}
				} else {
					return 'error:Blood Pressure reading invalid format';
				}
			} else if ($type == 'temp') {
				if (!is_real_number($value)) {
					return 'error:Temperature must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "','" . escape($value) . "'," . $hospid . ", 'Temperature' , " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
					if (floatval($value) > VitalsConfig::$maxNormalTemperature || floatval($value) < VitalsConfig::$minNormalTemperature) {
						$alert = new Alert();
						$alert->setMessage("Temperature Value ($value) is not within the NORMAL range");
						$alert->setType("Temperature");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));

						@(new AlertDAO())->add($alert, $pdo);
					}
				}
			} else if ($type == 'weight') {
				if (!is_real_number($value)) {
					return 'error:Weight must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "','" . escape($value) . "'," . $hospid . ", 'Weight', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'height') {
				if (!is_real_number($value)) {
					return 'error:Height must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "','" . escape($value) . "'," . $hospid . ", 'Height', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "' )";
				}
			} else if ($type == 'bmi') {
				if (!is_real_number($value)) {
					return 'error:BMI must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'BMI', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'fundus_height') {
				if (!is_real_number($value)) {
					return 'error:Fundus Height must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Fundus Height', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'glucose') {
				if (!is_real_number($value)) {
					return 'error:Glucose must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Glucose', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
					if (floatval($value) > VitalsConfig::$normalGlucose) {
						$alert = new Alert();
						$alert->setMessage("Blood Glucose value of " . $value . " is not normal ");
						$alert->setType("Glucose");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));

						@(new AlertDAO())->add($alert, $pdo);
					}
				}
			} else if ($type == 'protein') {
				if (!is_real_number($value)) {
					return 'error:Protein must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Protein', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
					if (floatval($value) > VitalsConfig::$normalProtein) {
						$alert = new Alert();
						$alert->setMessage("Blood Protein value of " . $value . " is not normal ");
						$alert->setType("Protein");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));

						@(new AlertDAO())->add($alert, $pdo);
					}
				}
			} else if ($type == 'fhr') {
				if (!is_real_number($value)) {
					return 'error:Fetal Heart Rate value must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Fetal Heart Rate', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'dilation') {
				if (!is_real_number($value)) {
					return 'error:Dilation value must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Dilation', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'mid-arm-circumference') {
				if (!is_real_number($value)) {
					return 'error:Value must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Mid-Arm Circumference', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'head-circumference') {
				if (!is_real_number($value)) {
					return 'error:Value must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Head Circumference', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'length-of-arm') {
				if (!is_real_number($value)) {
					return 'error:Value must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Length of Arm', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
				}
			} else if ($type == 'pcv') {
				if (is_real_number($value)) {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "'," . escape($value) . "," . $hospid . ", 'PCV', " . ($inPatientId != null ? $inPatientId : 'NULL') . " , '" . $_SESSION ['staffID'] . "')";
				} else {
					return 'error:PCV must be numeric';
				}
			} else if ($type == 'urine') {
				if (is_real_number($value)) {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "'," . escape($value) . "," . $hospid . ", 'Urine', " . ($inPatientId != null ? $inPatientId : 'NULL') . " , '" . $_SESSION ['staffID'] . "')";
				} else {
					return 'error:Urine must be numeric';
				}
			} else if ($type == 'spo2') {
				if (is_real_number($value)) {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "'," . escape($value) . "," . $hospid . ", 'SpO2', " . ($inPatientId != null ? $inPatientId : 'NULL') . " , '" . $_SESSION ['staffID'] . "')";
				} else {
					return 'error:SpO2 must be numeric';
				}
			} else if ($type == 'pain_scale') {
				if (!is_real_number($value)) {
					return 'error:Pain Scale must be numeric';
				} else {
					$sql = "INSERT INTO vital_sign (patient_id, value, hospital_id, type, in_patient_id, read_by) VALUES ('" . escape($patientID) . "', '" . escape($value) . "', " . $hospid . ", 'Pain Scale', " . ($inPatientId != null ? $inPatientId : 'NULL') . ", '" . $_SESSION ['staffID'] . "')";
					if (floatval($value) > VitalsConfig::$tolerablePain) {
						$alert = new Alert();
						$alert->setMessage("Pain Scale of " . $value . " is not normal ");
						$alert->setType("Protein");
						$alert->setPatient((new PatientDemographDAO())->getPatient(escape($patientID), FALSE, $pdo, null));
						
						@(new AlertDAO())->add($alert, $pdo);
					}
				}
			}
		} else {
			return 'error:Invalid type specified for vital sign element';
		}

		if (isset ($sql)) {
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$chk = $stmt->execute();
			if ($chk) {
				return 'ok';
			}
		}
		return 'error:An error has occurred';
	}

	function saveBMIVitalSign($type, $patientID, $w_value, $h_value, $inPatientId = null)
	{
		if (trim($w_value) == '' || trim($h_value) == '') {
			return 'error:What of value?';
		} else if ($type == 'bmi') {
			if (!is_real_number($h_value) || $h_value == 0) {
				return 'error:Your Height value must be a number or cannot be zero';
			} else if (!is_real_number($w_value) || $w_value == 0) {
				return 'error:Your Weight value must be a number or cannot be zero';
			}
			$value = number_format(($w_value / ($h_value * $h_value)), 1);
			if ($value <= 0) {
				return 'error:BMI must be numeric';
			} else {
				$save_bmi = $this->saveVitalSign($type, $patientID, $value, $inPatientId);
				if ($save_bmi == 'ok') {
					$this->saveVitalSign('height', $patientID, $h_value, $inPatientId);
					$this->saveVitalSign('weight', $patientID, $w_value, $inPatientId);
				} else {
					return 'error:Cant save BMI';
				}
			}
		} else {
			return 'error:Invalid type specified for vital sign element';
		}
		return 'ok:';
	}

	function isAdmitted($pid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM in_patient WHERE patient_id = $pid AND `status` = 'Active' # date_discharged IS NULL";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		// $row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		if ($stmt->rowCount() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function isAntenatal($pid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT patient_id FROM enrollments_antenatal WHERE patient_id = $pid AND active IS TRUE";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		// $row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		if ($stmt->rowCount() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function isImmunization($pid)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM enrollments_immunization WHERE patient_id = " . $pid;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		// $row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);

		if ($stmt->rowCount() > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function enrollPatientToProgram($patientID, $program)
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		if (!isset($_SESSION ['staffID'])) {
			return 'error:Sorry, restart the application. Your session has timed out';
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();

		$enrolled_by = $_SESSION ['staffID'];

		//get the hospital where this patient was enrolled
		$enrolled_at = $this->STAFF->getStaffHospitalID($enrolled_by);
		$sql = "INSERT IGNORE INTO enrollments_" . $program . " (`patient_id`, `enrolled_by`, `enrolled_on`,`enrolled_at`) VALUES ('" . $patientID . "','" . $enrolled_by . "',CURRENT_TIMESTAMP, '" . $enrolled_at . "')";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$chk2 = $stmt->execute();
		if ($chk2) {
			return '';
		} else {
			return 'error';
		}
	}

	function showDueVaccinesForPatient($pid, $mode = null, $enableCatchup = null, $direct_id = null)
	{
		require_once 'class.vaccines.php';
		$VACCINE = new Vaccine_();
		$protect = new Protect();
		$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION ['staffID']);
		if (!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->records)) {
			return $protect->ACCESS_DENIED;
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
		$pdo = (new MyDBConnector())->getPDO();
		$direct_id = escape($direct_id);
		//the due or expired ones that have been "paid" for

		$filter1 = $mode != 'preparation' ? " AND a.`billed` IS TRUE " : " AND a.`billed` IS FALSE";
		$filter2 = $enableCatchup != TRUE ? " AND (DATE(NOW()) BETWEEN a.`due_date` AND a.`expiration_date`)" : " AND a.`due_date` <= DATE(NOW())";

		$sql = "SELECT a.*, b.label FROM `patient_vaccine` a, `vaccines` b WHERE b.`active`='1' AND a.`patient_id` = " . $pid . " AND a.`entry_date` IS NULL $filter1 $filter2 AND b.id = a.`vaccine_id` ORDER BY due_date ASC";
		if ($direct_id) {
			$sql = "SELECT a.*, b.label FROM `patient_vaccine` a, `vaccines` b WHERE b.`active`='1' AND a.`patient_id` = " . $pid . " AND a.`entry_date` IS NULL AND a.id = $direct_id $filter1 $filter2 AND b.id = a.`vaccine_id` ORDER BY due_date ASC";
		}
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result = $stmt->execute();
		if ($mode == 'preparation') {
			$form_action = 'summarize_vaccine()';
			$action = 'summarize_vaccine';
		} else {
			$form_action = 'take_vaccine()';
			$action = 'take_vaccine';
		}
		$uid = uuid();

		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$ret = '';
		$ret .= '<input type="hidden" name="patient" value="' . $pid . '"><input type="hidden" name="totalCost" value="0"/>
				<input type="hidden" name="action" value="' . $action . '">';
		$ret .= '<table class="table table-hover catchUpTable">';
		$ret .= '<thead><th>*</th><th>Vaccine</th><th>Stage</th><th>Due Date</th><th class="amount">Amount</th></thead>';
		if ($stmt->rowCount() > 0) {
			$_SESSION['vac_id'] = 0;
			do {
				$vac_id = $row ['vaccine_id'];
				$lev = $row ['vaccine_level'];
				$due_date = $row ['due_date'];
				if ($_SESSION ['vac_id'] == $row ['vaccine_id']) {
					// skip this row so that the other stages wont show
				} else {
					$fdata = $VACCINE->getVaccineDetails($vac_id);
					$price = $fdata['price'];
					$ret .= '<tr>
						<td><input id="vv_' . $uid . '_' . $row['vaccine_id'] . '_' . $row ['vaccine_level'] . '" data-price="' . money_format($price, 2) . '" type="checkbox" name="vaccine[]" value="' . $row['id'] . '"/>
						</td><td><label for="vv_' . $uid . '_' . $row['vaccine_id'] . '_' . $row ['vaccine_level'] . '">' . $row ['label'] . '</label></td>
						<td><label for="vv_' . $uid . '_' . $row['vaccine_id'] . '_' . $row ['vaccine_level'] . '">' . $lev . '</label></td><td><label for="vv_' . $uid . '_' . $row['vaccine_id'] . '_' . $row ['vaccine_level'] . '">' . date("jS M, Y", strtotime($due_date)) . '</label></td>
						<td class="amount"><label for="vv_' . $uid . '_' . $row['vaccine_id'] . '_' . $row ['vaccine_level'] . '">' . number_format($price, 2) . '</label></td>
						</tr>';
				}
				$_SESSION['vac_id'] = $row ['vaccine_id'];
			} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
		} else {
			$ret .= '<tr><td colspan="5">' . $this->ERROR_PATIENT_NO_DUE_VACCINE . '</td></tr>';
		}
		$ret .= '</table>';

		return $ret;
	}

	function getAllSocioEco($returnType = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$all = array();
		$query = "SELECT * FROM socio_economic_status";
		$stmt = $pdo->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result = $stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$arr = array();
			$data[] = $row;
			$arr[] = $row['id'];
			$arr[] = $row['name'];
			$all[] = $arr;
		}
		if ($returnType == 'json') {
			return ($data);
		}
		return $all;
	}

	function getAllLifeStyle($returnType = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$all = array();
		$sql = "SELECT * FROM life_style"; //correct this to life_styles
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$result = $stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$data = array();
		do {
			$arr = array();
			$data[] = $row;
			$arr[] = $row['id'];
			$arr[] = $row['title'];
			$all[] = $arr;
		} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
		if ($returnType == 'json') {
			return ($data);
		}
		return $all;
	}

	static function login($POST_DATA)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$username = escape(trim($POST_DATA['pid']));
		$password = escape(trim($POST_DATA['password']));
		$sql = "SELECT l.alias,l.password FROM login l LEFT JOIN patient_demograph p ON l.id = p.login_id WHERE l.alias = '$username' AND l.password = PASSWORD('$password')";
		if ($username == "" || $password == "") {
			return "error:Login ID and password must be entered";
		} else {
			if (strlen(trim($POST_DATA['pid'])) != 10 & !is_real_number($POST_DATA['pid'])) {
				return 'error:Invalid Patient Login ID';
			} else {
				$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
				$stmt->execute();
				if ($stmt->rowCount() > 0) {
					$_SESSION['patientID'] = $username;
					return "ok";
				} else {
					return "error:Invalid Login ID and password combination";
				}
			}
		}
	}

	function changePasword($pid, $newPwd)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$conn = new MyDBConnector();
		$pdo = $conn->getPDO();
		$sql = "UPDATE login SET `password`=PASSWORD('" . $newPwd . "') WHERE alias=" . $pid;
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			return "ok";
		} else {
			return "Password change failed";
		}
	}

	public function getStateLGA($patient_ID)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT p.state_id,p.lga_id,s.name AS state, l.name AS lga FROM patient_demograph p LEFT JOIN state s ON s.id=p.state_id LEFT JOIN lga l ON l.id=p.lga_id WHERE p.patient_ID ='$patient_ID'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		return $row;
	}

	public function _getVisitSummary($patientId, $begin, $end)
	{
		$thisMonth = date("Y-m-01");
		$data['notes'] = [];
		if (!is_null($begin) && !is_null($end)) {
			$start = $begin;
			$stop = $end;
		} else {
			$start = date("Y-m-d", strtotime("$thisMonth first monday")); //the first monday of this month
			$stop = date("Y-m-d");
		}
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VisitNotesDAO.php';
		$notes = (new VisitNotesDAO())->getPatientNotes($patientId, $page = 0, $pageSize = 999999999, TRUE);
		foreach ($notes->data as $d) {
			//            $d->setPatient(NULL);
			//            $d->setHospital(NULL);
			if (date("Y-m-d", strtotime($d->date_of_entry)) >= $start && date("Y-m-d", strtotime($d->date_of_entry)) <= $stop) {
				$data['notes'][] = $d;
			}
		}

		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
		$data['vitals'] = (new VitalSignDAO())->getPatientLastVitalSigns($patientId, null, FALSE, []);

		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDiagnosisDAO.php';
		$data['diagnoses'] = (new PatientDiagnosisDAO())->oneByDate($patientId, $start, $stop);
		return $data;
	}

	public function getHMOReports_($from = null, $to = null, $scheme = null)
	{ //main method for spreadsheet
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		if ($from == null || $from == '') {
			$start = '1970-01-01';
		} else {
			$start = date("Y-m-d", strtotime($from));
		}
		if ($to == null || $to == '') {
			$stop = date("Y-m-d");
		} else {
			$stop = date("Y-m-d", strtotime($to));
		}

		if (isset($start, $stop)) {
			//swap the dates, since mysql does not really obey negative date between`s
			//and assign in a single line. double line assignment fails
			//because by the time the later comparison is called,
			//they would be equal and things are not consistent anymore
			list($dateStart, $dateStop) = [min($start, $stop), max($start, $stop)];
		}

		$scheme = ($scheme == null) ? '' : $scheme;

		$sql = "SELECT `pid`, `sex`, `when`, GROUP_CONCAT(enrollee_number) AS `enrollee_number`, `legacy_patient_id`, GROUP_CONCAT(CONCAT_WS('=', `bill_source`, `amount`)) AS `bill_source_amount`, GROUP_CONCAT(DISTINCT auth_code SEPARATOR ', ') AS `auth_code`, GROUP_CONCAT(DISTINCT bp SEPARATOR ', ') AS `bp`, GROUP_CONCAT(`case` SEPARATOR ', ') AS `case`, GROUP_CONCAT(DISTINCT consultant) AS `consultant`, GROUP_CONCAT(CONCAT_WS('=', `name`, `generic_name`)) AS `drug_name`, GROUP_CONCAT(DISTINCT lab_test SEPARATOR ', ') AS `lab_test` FROM (SELECT `b`.`patient_id` AS `pid`,`p`.`sex` AS `sex`,cast(`b`.`transaction_date` AS DATE) AS `when`,`i`.`enrollee_number` AS `enrollee_number`,`p`.`legacy_patient_id` AS `legacy_patient_id`,`e`.`name` AS `bill_source`,`b`.`amount` AS `amount`,`b`.`auth_code` AS `auth_code`,NULL AS `bp`,NULL AS `case`,NULL AS `consultant`,NULL AS `name`,NULL AS `generic_name`,`s`.`id` AS `scheme_id`,`s`.`scheme_name` AS `scheme_name`,`pl`.`lab_group_id` AS `lab_test` FROM (((((`patient_demograph` `p` LEFT JOIN `bills` `b` ON((`p`.`patient_ID` = `b`.`patient_id`))) LEFT JOIN `bills_source` `e` ON((`b`.`bill_source_id` = `e`.`id`))) LEFT JOIN `insurance` `i` ON((`p`.`patient_ID` = `i`.`patient_id`))) LEFT JOIN `insurance_schemes` `s` ON((`s`.`id` = `i`.`insurance_scheme`))) LEFT JOIN `patient_labs` `pl` ON((`pl`.`patient_id` = `p`.`patient_ID`))) WHERE ((`s`.`pay_type` = 'insurance') AND (`pl`.`_status` <> 'cancelled')) UNION SELECT `v`.`patient_id` AS `pid`,`p`.`sex` AS `sex`,cast(`v`.`read_date` AS DATE) AS `when`,`i`.`enrollee_number` AS `enrollee_number`,`p`.`legacy_patient_id` AS `legacy_patient_id`,NULL,NULL,NULL,`v`.`value` AS `bp`,NULL,cast(`v`.`read_by` AS CHAR(11)) AS `consultant`,NULL,NULL,`s`.`id` AS `scheme_id`,`s`.`scheme_name` AS `scheme_name`,NULL FROM (((`patient_demograph` `p` LEFT JOIN `vital_sign` `v` ON((`p`.`patient_ID` = `v`.`patient_id`))) LEFT JOIN `insurance` `i` ON((`p`.`patient_ID` = `i`.`patient_id`))) LEFT JOIN `insurance_schemes` `s` ON((`s`.`id` = `i`.`insurance_scheme`))) WHERE ((`s`.`pay_type` = 'insurance') AND (`v`.`type` = 'Blood Pressure')) UNION SELECT `pd`.`patient_ID` AS `pid`,`p`.`sex` AS `sex`,cast(`pd`.`date_of_entry` AS DATE) AS `when`,`i`.`enrollee_number` AS `enrollee_number`,`p`.`legacy_patient_id` AS `legacy_patient_id`,NULL,NULL,NULL,NULL,`d`.`case` AS `case`,`pd`.`diagnosed_by` AS `consultant`,NULL,NULL,`s`.`id` AS `scheme_id`,`s`.`scheme_name` AS `scheme_name`,NULL FROM ((((`patient_demograph` `p` LEFT JOIN `patient_diagnoses` `pd` ON((`p`.`patient_ID` = `pd`.`patient_ID`))) LEFT JOIN `diagnoses` `d` ON((`pd`.`diagnosis` = `d`.`id`))) LEFT JOIN `insurance` `i` ON((`p`.`patient_ID` = `i`.`patient_id`))) LEFT JOIN `insurance_schemes` `s` ON((`s`.`id` = `i`.`insurance_scheme`))) WHERE ((`s`.`pay_type` = 'insurance') AND (`pd`.`_status` <> 'history')) UNION SELECT cast(`pr`.`patient_id` AS CHAR(11)) AS `pid`,`p`.`sex` AS `sex`,cast(`pr`.`when` AS DATE) AS `when`,`i`.`enrollee_number` AS `enrollee_number`,`p`.`legacy_patient_id` AS `legacy_patient_id`,NULL,NULL,NULL,NULL,NULL,`pr`.`requested_by` AS `consultant`,`m`.`name` AS `name`,`mg`.`name` AS `generic_name`,`s`.`id` AS `scheme_id`,`s`.`scheme_name` AS `scheme_name`,NULL FROM ((((((`patient_demograph` `p` LEFT JOIN `patient_regimens` `pr` ON((`p`.`patient_ID` = `pr`.`patient_id`))) LEFT JOIN `patient_regimens_data` `prd` ON((`pr`.`group_code` = `prd`.`group_code`))) LEFT JOIN `drugs` `m` ON((`prd`.`drug_id` = `m`.`id`))) LEFT JOIN `drug_generics` `mg` ON((`prd`.`drug_generic_id` = `mg`.`id`))) LEFT JOIN `insurance` `i` ON((`p`.`patient_ID` = `i`.`patient_id`))) LEFT JOIN `insurance_schemes` `s` ON((`s`.`id` = `i`.`insurance_scheme`))) WHERE ((`s`.`pay_type` = 'insurance') AND (`prd`.`status` = 'filled'))) AS hmo_spreadsheet WHERE scheme_id = " . $scheme . " AND `when` BETWEEN date('" . $dateStart . "') AND date('" . $dateStop . "') GROUP BY `when`,`pid` ORDER BY `when` ASC, `pid` ASC";
		//        error_log($sql);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row;
			}
		}
		return $report;
	}

	public function getHMOReports_one($from = null, $to = null, $scheme = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		if ($from == null || $from == '') {
			$start = '1970-01-01';
		} else {
			$start = date("Y-m-d", strtotime($from));
		}
		if ($to == null || $to == '') {
			$stop = date("Y-m-d");
		} else {
			$stop = date("Y-m-d", strtotime($to));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($start, $stop), max($start, $stop)];
		}

		$scheme = ($scheme == null) ? '' : $scheme;
		$sql = "SELECT pd.patient_ID, GROUP_CONCAT(DISTINCT d.case) AS `case`, GROUP_CONCAT(DISTINCT pd.diagnosed_by) AS consultant, DATE(pd.date_of_entry) AS `when` FROM patient_diagnoses pd LEFT JOIN diagnoses d ON d.id=pd.diagnosis LEFT JOIN bills b ON b.patient_id=pd.patient_ID LEFT JOIN insurance i ON i.patient_id=b.patient_id WHERE pd._status<>'history' AND DATE(pd.date_of_entry) BETWEEN '2015-06-18' AND '2015-11-27' AND i.insurance_scheme=3 GROUP BY DATE(pd.date_of_entry), pd.patient_ID ORDER BY DATE(pd.date_of_entry) ASC, pd.patient_ID ASC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row;
			}
		}
		return $report;
	}

	public function getHMOReports_two($from = null, $to = null, $scheme = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		if ($from == null || $from == '') {
			$start = '1970-01-01';
		} else {
			$start = date("Y-m-d", strtotime($from));
		}
		if ($to == null || $to == '') {
			$stop = date("Y-m-d");
		} else {
			$stop = date("Y-m-d", strtotime($to));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($start, $stop), max($start, $stop)];
		}

		$scheme = ($scheme == null) ? '' : $scheme;
		$sql = "SELECT v.patient_id, DATE(v.read_date) AS `when`, GROUP_CONCAT(DISTINCT v.value), GROUP_CONCAT(DISTINCT v.read_by) AS consultant FROM vital_sign v LEFT JOIN bills b ON b.patient_id=v.patient_id LEFT JOIN insurance i ON i.patient_id=b.patient_id WHERE v.type='Blood Pressure' AND i.insurance_scheme=3 AND DATE(v.read_date) BETWEEN '2015-06-18' AND '2015-11-27' GROUP BY DATE(v.read_date), v.patient_id ORDER BY DATE(v.read_date) ASC, v.patient_id ASC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row;
			}
		}
		return $report;
	}

	public function getHMOReports_three($from = null, $to = null, $scheme = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		if ($from == null || $from == '') {
			$start = '1970-01-01';
		} else {
			$start = date("Y-m-d", strtotime($from));
		}
		if ($to == null || $to == '') {
			$stop = date("Y-m-d");
		} else {
			$stop = date("Y-m-d", strtotime($to));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($start, $stop), max($start, $stop)];
		}

		$scheme = ($scheme == null) ? '' : $scheme;
		$sql = "SELECT DATE(b.transaction_date) AS `when`, b.patient_id, GROUP_CONCAT(DISTINCT pl.lab_group_id) AS lab_test, GROUP_CONCAT(DISTINCT pr.requested_by) AS consultant, GROUP_CONCAT(DISTINCT pr.group_code) AS drug_name FROM bills b LEFT JOIN patient_labs pl ON pl.patient_id=b.patient_id LEFT JOIN insurance i ON i.patient_id=b.patient_id LEFT JOIN patient_regimens pr ON pr.patient_id=b.patient_id LEFT JOIN patient_regimens_data prd ON pr.group_code=prd.group_code LEFT JOIN drugs m ON prd.drug_id=m.id WHERE pl._status<>'cancelled' AND DATE(b.transaction_date) BETWEEN '2015-06-18' AND '2015-11-27' AND i.insurance_scheme=3 AND prd.status='filled' AND b.cancelled_on IS NULL GROUP BY DATE(b.transaction_date), b.patient_id ORDER BY DATE(b.transaction_date) ASC, b.patient_id ASC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$rst = $stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row;
			}
		}
		return $report;
	}

	public function getHMOReports_four($from = null, $to = null, $scheme = null)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		if ($from == null || $from == '') {
			$start = '1970-01-01';
		} else {
			$start = date("Y-m-d", strtotime($from));
		}
		if ($to == null || $to == '') {
			$stop = date("Y-m-d");
		} else {
			$stop = date("Y-m-d", strtotime($to));
		}

		if (isset($start, $stop)) {
			list($dateStart, $dateStop) = [min($start, $stop), max($start, $stop)];
		}

		$scheme = ($scheme == null) ? '' : $scheme;
		$sql = "SELECT DATE(b.transaction_date) AS `when`, p.patient_ID, p.sex, GROUP_CONCAT(CONCAT_WS('=', bs.name, b.amount)) AS bs_amount, GROUP_CONCAT(DISTINCT b.auth_code) AS auth_code, p.legacy_patient_id, i.enrollee_number FROM bills b LEFT JOIN patient_demograph p ON p.patient_ID=b.patient_id LEFT JOIN insurance i ON i.patient_id=b.patient_id LEFT JOIN bills_source bs ON bs.id=b.bill_source_id WHERE i.insurance_scheme='$scheme' AND DATE(b.transaction_date) BETWEEN '$dateStart' AND '$dateStop' AND b.cancelled_on IS NULL GROUP BY DATE(b.transaction_date), b.patient_id ORDER BY DATE(b.transaction_date) ASC, b.patient_id ASC";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row;
			}
		}
		return $report;
	}

	function getBloodPressure($pid, $when)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT * FROM vital_sign where patient_id=$pid AND type='Blood Pressure' AND DATE(read_date)='$when' ";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report[] = $row['value'];
			}
		}
		return $report;
	}

	function getDiagnosis($pid, $when)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT d.case, pd.diagnosed_by FROM patient_diagnoses pd LEFT JOIN diagnoses d ON d.id=pd.diagnosis WHERE pd._status <> 'history' AND pd.patient_ID='$pid' AND DATE(pd.date_of_entry)='$when'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report['case'][] = $row['case'];
				$report['consultant'][] = $row['diagnosed_by'];
			}
		}
		return $report;
	}

	function getdrugrGiven($pid, $when)
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
		$report = array();
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT m.name AS drug_name, pr.requested_by FROM patient_regimens pr LEFT JOIN patient_regimens_data prd ON pr.group_code=prd.group_code LEFT JOIN drugs m ON prd.drug_id=m.id WHERE prd.status='filled' AND pr.patient_id='$pid' AND DATE(pr.when)='$when'";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		if ($stmt->rowCount() > 0) {
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$report['drugs'][] = $row['drug_name'];
				$report['consultant'][] = $row['requested_by'];
			}
		}
		return $report;
	}
}
