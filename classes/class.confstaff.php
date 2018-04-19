<?php

class StaffConfig
{

	function updateStaffFee($staff_cat, $staff_fee)
	{
		require $_SERVER['DOCUMENT_ROOT'] . "/Connections/dbconnection.php";
		$sql = $chk2 = $upd_billable_query = array();
		mysql_select_db($database_dbconnection, $dbconnection);

		for ($i = 0; $i < count($staff_cat) - 1; $i++) {
			//exclude the submit button
			if (trim($staff_fee[$i]) != "" || $staff_cat != "updatebtn") {
				//update the cost in the billable_items for use insurance manager
				$upd_billable_query[] = "UPDATE insurance_items_cost SET selling_price = " . $staff_fee[$i] . " WHERE item_code = '" . $staff_cat[$i] . "'";
			} else {
				return 'error:Enter staff fee';
			}
		}
//        return 'error:'.implode("; ",$upd_billable_query);
		for ($j = 0; $j < count($upd_billable_query); $j++) {
			$chk2[] = mysql_query($upd_billable_query[$j], $dbconnection);
		}
		if (in_array(false, $chk2)) {
			return 'error:Could not save changes to database';
		} else {
			return 'success!';
		}
	}
}