<?php

class Protect
{
	public $mgt;
	public $doctor_role;
	public $nurse;
	public $lab;
	public $lab_super;
	public $pharmacy;
	public $pharmacy_super;
	public $lab_super_user;
	public $records;
	public $accounts;
	public $cashier;
	public $radiology;
	public $radiologyApproval;
	public $hmo_officer;
	public $referral_mgt;
	public $voucher;
	public $reporter;
	public $creditLimit;
	public $dentistry;
	public $ophthalmology;
	public $physiotherapy;
	public $consultationAuditor;
	public $procedures;
	public $user_management;
	public $bill_auditor;
	public $ivf_doctor;
	public $ivf_nurse;
	public $consumables;
	public $medical_report_approver;
	

	public $ACCESS_DENIED;
	public $SESSION_EXPIRED;

	public function __construct()
	{
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffRolesDAO.php';
		require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';

		$AllRoles = new StaffRolesDAO();
		$this->mgt = $AllRoles->getRole(6);
		$this->doctor_role = $AllRoles->getRole(2);
		$this->nurse = $AllRoles->getRole(1);
		$this->lab = $AllRoles->getRole(3);
		$this->lab_super = $AllRoles->getRole(4);
		$this->pharmacy = $AllRoles->getRole(5);
		$this->records = $AllRoles->getRole(7);
		$this->accounts = $AllRoles->getRole(8);
		$this->radiology = $AllRoles->getRole(9);
		$this->hmo_officer = $AllRoles->getRole(10);
		$this->cashier = $AllRoles->getRole(11);
		$this->referral_mgt = $AllRoles->getRole(12);
		$this->voucher = $AllRoles->getRole(13);
		$this->reporter = $AllRoles->getRole(14);
		$this->creditLimit = $AllRoles->getRole(15);
		$this->dentistry = $AllRoles->getRole(16);
		$this->pharmacy_super = $AllRoles->getRole(17);
		$this->lab_super_user = $AllRoles->getRole(18);
		$this->ophthalmology = $AllRoles->getRole(19);
		$this->consultationAuditor = $AllRoles->getRole(20);
		$this->procedures = $AllRoles->getRole(21);
		$this->user_management = $AllRoles->getRole(22);
		$this->physiotherapy = $AllRoles->getRole(23);
		$this->ivf_doctor = $AllRoles->getRole(24);
		$this->ivf_nurse = $AllRoles->getRole(25);
		$this->bill_auditor = $AllRoles->getRole(26);
		$this->radiologyApproval = $AllRoles->getRole(27);
		$this->consumables = $AllRoles->getRole(28);
		$this->medical_report_approver = $AllRoles->getRole(29);

		$this->ACCESS_DENIED = '<div class="alert-box notice">No access</div>';
		$this->SESSION_EXPIRED = '<div class="alert-box warning">Your session has expired. Please <a href="/login.php" onclick="location.reload(); return false">login</a> again</div>';
	}
}

/*include_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Session.php';
$session = new Session();*/
if (!isset($_SESSION)) {
	session_start();
}

if (!isset($_SESSION['staffID'])) {
	$_SESSION['location'] = urlencode($_SERVER['REQUEST_URI']);
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/login.php")) {
		if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			exit((new Protect())->SESSION_EXPIRED);
		} else {
			if (!isset($_POST['BYPASS']) || $_POST['BYPASS'] != "yes") {
				if (true) {
					// failover for wkhtlmtopdf `shell cmd`
					header('Location: /login.php');
				}
			}
		}
	}
}
