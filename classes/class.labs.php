<?php
class Labs {

	public static $allowLabOrderLinkInModule = TRUE;
	
	public $LIST_RECORDS_PER_PAGE = 10;

    function __construct()
    {
        require_once $_SERVER['DOCUMENT_ROOT'].'/protect.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientLabDAO.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/LabGroupDAO.php';
    }
	
	function getTestClasses(){
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT * FROM labtests_config_category";
		$rst = mysql_query($sql,$dbconnection);
		$str = array();
		while ($row = mysql_fetch_array($rst)){
			$str[] = $row['id']."=".$row['name'];
		}
		return implode("|",$str);
	}
	
	

	function updateLab($labid, $testtype, $testclass, $ref, $testunit, $cost){
		require $_SERVER['DOCUMENT_ROOT'].'/Connections/dbconnection.php';
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }$query = "UPDATE labtests_config SET name = '$testtype',category_id='$testclass',testUnit_Symbol='$testunit',reference='$ref' WHERE id = '$labid'";
		$result = mysql_query($query);
		if ($result) {
			return "ok: updated";
		} else {
			return "error:Error; ".mysql_error();
		}
	}

}
