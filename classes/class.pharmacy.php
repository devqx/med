<?php
if (! isset ( $_SESSION )) {
	session_start ();
}
class Pharmacy {

	//true for paragon, danferd and limi
	//false for garki, nisa and fmc
	public static $canPrescribeBrand = false;


	
	function purgeExpiredDrug($drugID,$hospital=NULL){
		if(!isset($_SESSION))session_start();
		require '../Connections/dbconnection.php';
		if (isset($database_dbconnection,$dbconnection)) {mysql_select_db($database_dbconnection, $dbconnection);         }
		
		$sql="SELECT * FROM drugs WHERE id =".$drugID;
		$rst = mysql_query($sql,$dbconnection);
		$row = mysql_fetch_assoc($rst);
		if(mysql_num_rows($rst)>0){
			$total=0;
			$total=(float)($row['price']*$row['quantity']);
			$sql="UPDATE drugs SET quantity = 0, date_last_modified = DATE(NOW()) WHERE drug_id = ".$drugID;
			$sql .= "";
			$chk = mysql_query($sql,$dbconnection);
			if($chk){
				$sql1="INSERT INTO purged_drugs(drug_id,quantity,amountlost,purgedby,purge_date) VALUES(".$drugID.",".$row['quantity'].",".$total.",'".$_SESSION['staffID']."',DATE(NOW()));";
				@mysql_query($sql1,$dbconnection);
				return 'success';
			}else{
				return 'error';
			}
		}
	
	}

}
