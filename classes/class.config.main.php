<?php


class MainConfig {

	public static $dbHost = "localhost";
	public static $dbName = "medicplus";
	public static $dbUser = "homestead";
	public static $dbPass = "secret";

	public static $numUsers = 6000;
	public static $sipEnabled = true;
	public static $erpEnabled = false;
	public static $defaultSchemeCreditLimit = 50000000;
	public static $approvedQueueDailyOnly = true;
	public static $amountShow = true; // display or hide amounts on claims validation sheet
	public static $showHx = true; // option to display Hx on the patient Soap
	
	
	public static $welcomeMessage = "Thank you %s for choosing %s. Your EHR ID is %s";
	//public static $welcomeMessage = "Welcome aboard and thank you for registering with The Limi Hospital. Your Reg. ID is %s. For any inquiries, Please contact us on 09094369526, 09094369527 or visit www.limihospital.org";

	public static $dateTimeFormat = "d/m/Y g:iA";
	public static $dateFormat = "d/m/Y";
	public static $shortDateFormat = "Y/m/d";
	public static $mysqlDateTimeFormat = "Y-m-d H:i:s";
	
	private $_drug_presentations = array(
		'transdermal patches',
		'rectal solution',
		'rectal dosage',
		'granules',
		'suppository',
		'dental cartridge',
		'topical',
		'injection',
		'infusion',
		'oily injection',
		'oil',
		'cream',
		'syrup',
		'tablet',
		'capsule',
		'caplet',
		'inhaler',
		'IV',
		'IM',
		'suspension',
		'solid oral dosage',
		'drop',
		'powder',
		'oral liquid',
		'solution',
		'lotion',
		'ointment',
		'spray',
	);

	private $_drug_stock_uom = array(
		'bottle','packet','carton', 'sachet','tablet','caplet','tube','vial','capsule', 'ampoule','tin',
		'bag',
		'cartridge',
		'Injection',
		'Jar',
		'Medical Device',
		'Patch',
		'Pen',
		'Plaster',
		'Prefilled Syringe',
		'Pressurized Container / Aerosol',
		'Suppositories',
		'Pump Spray',
		'Device'
	);
	public static $drug_stock_uom = [];
	public static $drug_presentations = [];// = sort($_drug_presentations);

	public static $drugFrequencies = array(
		'daily',
		'hourly',
		'weekly',
    'Monthly',
    'Quarterly',
		'At Night',
		'Immediately',
		'As Needed',
	  'Stat'
	);

	public static $smsGatewayUrl = "";
	public static $smsSenderName = "";
	
	
	function __construct()
	{
		sort($this->_drug_presentations, SORT_NATURAL | SORT_FLAG_CASE);
		sort($this->_drug_stock_uom, SORT_NATURAL | SORT_FLAG_CASE);
		MainConfig::$drug_presentations = $this->_drug_presentations;
		MainConfig::$drug_stock_uom = $this->_drug_stock_uom;
		MainConfig::$smsGatewayUrl = $this->getSmsGateway();
		MainConfig::$smsSenderName = $this->getSmsSender();
	}

	function getSmsGateway(){
		$CONFIG = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/classes/sms.config.php");
		$username = $CONFIG['email'];
		$password = $CONFIG['password'];
		return "http://sms.bbnplace.com/bulksms/bulksms.php?username=$username&password=$password";
	}

	function getSmsSender(){
		$CONFIG = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/classes/sms.config.php");
		return ($CONFIG['sender'] != "") ? $CONFIG['sender'] : "MEDICPLUS";
	}

	function getHIVSymptoms($filter = NULL){
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
		if (isset($database_dbconnection,$dbconnection)) {
			mysql_select_db($database_dbconnection, $dbconnection);
		}
		$sql = "SELECT id, symptom FROM hiv_symptoms";
		$chk=mysql_query($sql);
		$data = array();
		while ($row_data = mysql_fetch_assoc($chk)) {
			$data[] =  $row_data;
		}
		return $data;
	}
	static function allergenSeverities(){
		return array(
			'mild'=>'MILD',
			'moderate'=>'MODERATE',
			'severe'=>'SEVERE',
			'intolerable' => 'INTOLERABLE'
		);
	}

	static function preConditions(){
		return ['HIV/AIDS', 'Cancer', 'Tuberculosis', 'Malaria'];
	}

	static function listSeverity(){
		//TODO: optimize this so that it comes from the db
		return array(
			1=>'Mild',
			2=>'Moderate',
			3=>'Severe'
		);
	}

	static function listTherapy(){
		//TODO: let these values come from the db
		return array(
			'act'=>'ACT',
			'art'=>'ART',
			'dots'=>'DOTS',
			'none'=>'None',
		);
		//'<option value="others" disabled="disabled">Others</option>';
	}

	static function listResponses(){
		return array(
			5=>'Excellent',
			4=>'Very Good',
			3=>'Good',
			2=>'Bad',
			1=>'Resistant',
			0=>'Unknown'
		);
	}

	static function listSocioEconomicTypes(){
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
		$patient=new Manager();
		return $patient->getAllSocioEco('json');
	}
	static function listLifeStyles(){
		require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
		$patient=new Manager();
		return $patient->getAllLifeStyle('json');
	}

	static function listFamilyPlanningMethods(){
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM fp_methods";
		$data = array();
		$rst = mysql_query($sql, $dbconnection);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}
	static function listlmpPmtctLinkOptions(){
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM lmp_pmtct_links";
		$data = array();
		$rst = mysql_query($sql, $dbconnection);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}

	static function listTBStatuses(){
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM tb_statuses";
		$data = array();
		$rst = mysql_query($sql, $dbconnection);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}
	static function listFunctionalStatuses(){
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM functional_statuses";
		$data = array();
		$rst = mysql_query($sql);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}

	function listImagingTypes(){
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM patient_scan_types";
		$data = array();
		$rst = mysql_query($sql);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}

	public function listServiceEntries()
	{
		require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/dbconnection.php';
		if (isset($database_dbconnection, $dbconnection)) {
			mysql_select_db ( $database_dbconnection, $dbconnection );
		}
		$sql = "SELECT * FROM hiv_service_entries";
		$data = array();
		$rst = mysql_query($sql);
		while ($row = mysql_fetch_assoc($rst)){
			$data[] = $row;
		}
		return $data;
	}

}
