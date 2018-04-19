<?php

class InsuranceManager {
	function __construct(){
			if (! isset ( $_SESSION )) {@session_start ();}
	}
	
	static function getStaffHospitalID($staffid) {
		require_once 'class.staff.php';
        $staff=new StaffManager();
        return $staff->getStaffHospitalID($staffid);

	}
	function getInsuranceProfileName($insuranceprofileID) {
		$hospid = self::getStaffHospitalID ( $_SESSION ['staffID'] );
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		if (isset($database_dbconnection, $dbconnection)) {             mysql_select_db ( $database_dbconnection, $dbconnection );         }
		$rst = mysql_query ( "SELECT * FROM insurance_owners WHERE id='".$insuranceprofileID. "' AND hospid = ".$hospid);
		$row_data = mysql_fetch_assoc ( $rst );
		return $row_data ['company_name'];
	}
	static function getInsuranceSchemeName($insuranceSchemeID) {
		$hospid = self::getStaffHospitalID ( $_SESSION ['staffID'] );
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		if (isset($database_dbconnection, $dbconnection)) {             mysql_select_db ( $database_dbconnection, $dbconnection );         }
		$rst = mysql_query ( "SELECT * FROM insurance_schemes WHERE id='".$insuranceSchemeID. "' AND hospid = ".$hospid);
		$row_data = mysql_fetch_assoc ( $rst );
		return $row_data ['scheme_name'];
	}
	
	function getInsuranceSchemeOwnerName($insuranceSchemeID){
		$hospid = self::getStaffHospitalID ( $_SESSION ['staffID'] );
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		if (isset($database_dbconnection, $dbconnection)) {             mysql_select_db ( $database_dbconnection, $dbconnection );         }
		$sql = "SELECT a.company_name FROM insurance_owners a, insurance_schemes b WHERE b.id='".$insuranceSchemeID. "' AND b.scheme_owner_id = a.id /*AND hospid = ".$hospid."*/";
		$rst = mysql_query ( $sql);
		$row_data = mysql_fetch_assoc ( $rst );
		return $row_data ['company_name'];
	}

	function addBillableItem($itemcode, $description,$category, $insurance_scheme_id, $selling_price) {
		if (trim($description) == "") {
			return "error: Name of item cannot be empty";
		}
        #this session breaks if the person is no longer logged in.
        #TODO: how do you mitigate against that?
		$hospid = $this->getStaffHospitalID ( $_SESSION ['staffID'] );

        if(trim($hospid) == ""){
            $hospid = 1;
        }

		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
        if (isset($database_dbconnection, $dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "INSERT INTO insurance_billable_items(item_code,item_description,item_group_category,hospid) VALUES('" .$itemcode."','".$description."','".$category."',".$hospid. ")";
//		 return "error:".$sql;
		$chk = mysql_query ( $sql);
		if ($chk) {
			//passed first stage execution
			$sql = "INSERT INTO insurance_items_cost (item_code, selling_price, insurance_scheme_id, hospid) VALUES ('" .$itemcode."', ".$selling_price.", ".$insurance_scheme_id.", ".$hospid.")";
			$chk = mysql_query($sql, $dbconnection);
			if($chk){
				//passed second stage
				$ret = 'success: Billable Item added';
			}else{
				//failed second stage
				$ret = "error: Database error occured;2";
			}			
		} else {
			//failed first stage
			$ret = "error: Database error occured;1". mysql_error($dbconnection);
		}
		
		return $ret;
	}
	
	function addBillableItemsCSV($file){
		//TODO: upload this file, read the contents as csv then produce
        $status = false;
		if ($status) {
			return 'success:TODO;;ok';
		} else {
			return 'error:can\'t parse file';
		}
	}
}
