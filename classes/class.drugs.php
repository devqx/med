<?php
class Drugs {
	function getDrugName($drug_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT drugs.drug_name, drug_category.name FROM drugs, drug_category WHERE drugs.cater_ID = drug_category.id AND drugs.drug_id ='".$drug_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['drug_name'];
	}
	function getGenericName($generic_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT name FROM drug_generics  WHERE id ='".$generic_id."'";
//		error_log($sql);
                $chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['name'];
	}
	function getDrugNameOnly($drug_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT drugs.drug_name, drug_category.name FROM drugs, drug_category WHERE drugs.cater_ID = drug_category.id AND drugs.drug_id ='".$drug_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['drug_name'];
	}
	function getDrugType($drug_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "select g.form from drug_generics g left join drugs d on g.id=d.drug_generic_id WHERE d.id =".$drug_id;
        $chk=mysql_query($sql);
		$row_data = mysql_fetch_assoc($chk);
		return ucwords($row_data['form']);
	}
	
	static function getDrugPrice($drug_id ,$patientID = NULL){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
		$insStatus = false;
		require_once "class.insurance.php";
		$ins = new Insurance();
		if(!class_exists('Insurance')){
			$insStatus = $ins->isPatientInsured($patientID);
		}else{
			$insStatus = $ins->isPatientInsured($patientID);
		}
		//FIXME: URGENT The insurance class throws a fatal error		
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
//		if($patientID != NULL && $insStatus){
			//require_once $_SERVER['DOCUMENT_ROOT'] .'/class.patient.php';
            $sql = "SELECT ic.selling_price AS price FROM insurance_items_cost ic left join drugs dr on dr.billing_code = ic.item_code where dr.drug_id = $drug_id and ic.insurance_scheme_id = (select insurance_scheme from insurance where patient_id = '$patientID')";
//            error_log($sql);
            $chk=mysql_query($sql,$dbconnection);
			if(mysql_num_rows($chk)==0){
				$sql = "SELECT ic.selling_price AS price FROM insurance_items_cost ic left join drugs dr on dr.billing_code = ic.item_code where dr.drug_id = $drug_id and ic.insurance_scheme_id = 1";;
			}		
//		}else {
//			$sql = "SELECT * FROM drugs WHERE drug_id ='".$drug_id."'";
//		}
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return ucwords($row_data['price']);
	}
	
	function getDrugQuantityFromRegimenGroupID($drugID,$R_Group_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT quantity FROM regimens_full_data WHERE reg_id ='".$R_Group_id."' AND drugName = '".$drugID."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['quantity'];//TODO: Save quantity instead of returning quantity
	}
	function getDrugQuantityRemaining($drug_id){
		require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
		$sql = "SELECT quantity FROM drugs WHERE drug_id ='".$drug_id."'";
		$chk=mysql_query($sql,$dbconnection);
		$row_data = mysql_fetch_assoc($chk);
		return $row_data['quantity'];
	}
	function dispenseDrug($drug_id, $quantity){
		/**
		* reduce the number of this drug available by the number removed/dispensed
		*/
		if($this->getDrugQuantityRemaining($drug_id)>=$quantity){
			require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
            if (isset($database_dbconnection,$dbconnection)) {
                mysql_select_db($database_dbconnection, $dbconnection);
            }$sql = "UPDATE `drugs` SET `quantity` = (quantity - ".$quantity.") WHERE `drug_id` = ".$drug_id.";";
			@mysql_query($sql,$dbconnection);
            //TODO: move this quantity to the dispensed drugs table in the db
			return "ok";
		}else{
			return "not enough quantity";
		}
	}

    function listDrugs($retunType){
        require $_SERVER['DOCUMENT_ROOT']."/Connections/dbconnection.php";
        if (isset($database_dbconnection,$dbconnection)) {
            mysql_select_db($database_dbconnection, $dbconnection);
        }
        $sql = "SELECT drug_id AS id, drug_name, weight, type FROM drugs";
        $rst = mysql_query($sql);
        $data = array();
        while($row=mysql_fetch_assoc($rst)){
            $data[] = $row;
        }
        if($retunType == 'json')
            return json_encode($data);
        return $data;
    }
}