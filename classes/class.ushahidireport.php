<?php

class UshahidiReports
{
	
	function createNewReport($date, $title, $description, $hour, $minute, $ampm, $categoryID, $lat, $long, $locationName)
	{
		//		error_log( "PARAMS: ".$date."#".$title."#".$description."#".$hour."#".$minute."#".$ampm."#".$categoryID."#".$lat."#".$long."#".$locationName);
		require_once 'class.config.main.php';
		$dir = MainConfig::$ushahidi_dir;
		$_usha_clean_url_enabled = (MainConfig::$_usha_clean_url_enabled) ? '' : '/index.php';
		$posturl = ((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] === 'off') ? 'http' : 'https') . '://' . $_SERVER['HTTP_HOST'] . '/' . $dir . $_usha_clean_url_enabled . '/api';
		$url = "task=report&incident_title=" . $title . "&incident_description=" . $description . "&incident_date=" . $date . "&incident_hour=" . $hour . "&incident_minute=" . $minute . "&incident_ampm=" . $ampm . "&incident_category=" . $categoryID . "&latitude=" . $lat . "&longitude=" . $long . "&location_name=" . $locationName . "&incident_active=1&incident_verified=1";
		
		$Curl_Session = curl_init($posturl);
		@curl_setopt($Curl_Session, CURLOPT_POST, 1);
		@curl_setopt($Curl_Session, CURLOPT_POSTFIELDS, $url);
		@curl_setopt($Curl_Session, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($Curl_Session, CURLOPT_FOLLOWLOCATION, 1);
		$response = @curl_exec($Curl_Session);
		//        error_log("curl result: ".$response);
		@curl_close($Curl_Session);
	}
	
	function getCategoryID($str)
	{
		$str = strtolower($str);
		require $_SERVER['DOCUMENT_ROOT'] . '/Connections/ushahidiconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql = "SELECT * FROM `category` WHERE LOWER(`category_title`) ='$str'";
		//$sql = "SELECT * FROM `diagnoses` WHERE LOWER(`case`) = ''";
		$result = mysql_query($sql, $dbconnection);
		$row = mysql_fetch_assoc($result);
		
		return $row['id'];
	}
	
	function getClinicName($clinicID)
	{
		require "Connections/dbconnection.php";
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql = "SELECT * FROM clinic WHERE clinicId  = '" . $clinicID . "'";
		//        error_log("MY LOCATION IS $clinicID");
		$chk = mysql_query($sql, $dbconnection);
		$retVal = "";
		$row_data = mysql_fetch_assoc($chk);
		if (mysql_num_rows($chk) >= 1) {
			do {
				$retVal .= $row_data['FullName'];
			} while ($row_data = mysql_fetch_assoc($chk));
			return $retVal;
		} else if (mysql_num_rows($chk) < 1) {
			return 'Unknown Location';//always default to a real location rather than showing "no data found"
		}
	}
	
	function getClinicLocation($clinicID, $type)
	{
		require "Connections/dbconnection.php";
		mysql_select_db($database_dbconnection, $dbconnection);
		$sql = "SELECT * FROM clinic WHERE clinicId  = '" . $clinicID . "'";
		$chk = mysql_query($sql, $dbconnection);
		$retVal = "";
		$row_data = mysql_fetch_assoc($chk);
		if (mysql_num_rows($chk) >= 1) {
			do {
				if ($type == "lat") {
					$retVal = $row_data['location_lat'];
				} else if ($type == "long") {
					$retVal = $row_data['location_long'];
				} else {
					$retVal = 0;
				}
			} while ($row_data = mysql_fetch_assoc($chk));
			return $retVal;
		} else if (mysql_num_rows($chk) < 1) {
			return 0;
		}
	}
	
	function addIncidentCategory($category)
	{
		require $_SERVER['DOCUMENT_ROOT'] . '/Connections/ushahidiconnection.php';
		mysql_select_db($database_dbconnection, $dbconnection);
		$query = "INSERT INTO `category` (`category_title`, `category_description`, `category_color`) VALUES ('$category', '$category', '" . $this->generateCategoryColor() . "');";
		$result = mysql_query($query, $dbconnection);
	}
	
	function generateCategoryColor()
	{
		$color = '';
		$chars = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
		$str = '';
		
		for ($i = 0; $i < 6; $i++) {
			$str .= $chars[rand(0, count($chars) - 1)];
		}
		return $str;
	}
	// 	$catID;
	// 	$incidentID;
	// 	$locationID;
	// //ushahidi tables to modify: category,incident,incident_category,level,location
	// function updateCategory($parentid,$locale,$catType,$catPosition,$categoryTitle,$categoryDescription,$categorycolor,$catvisible,$cattrusted){
	// 	require "Connections/ushahidiconnection.php";
	// 	mysql_select_db($database_dbconnection, $dbconnection);
	// 	$sql = "INSERT INTO category( parent_id,category_type,category_position,category_title,category_description,category_color,category_visible,category_trusted ) VALUES ('".ucwords($fname)."','".ucwords($lname)."','".$phone."','".$hospital."','".$specialty."','".strtolower($email)."','".md5($rpassword)."','".$profession."','".$username."')";
	// 	$chk = mysql_query($sql);
	// 	if($chk){
	// 	$catID=mysql_insert_id($dbconnection);
	// 	}else{
	// 	$catID= "Error: ".mysql_error();
	// }
	// return $catID;
	// }
	// function updateIncident($locationID,$formID,$locale,$userID,$incidentTitle,$incidentDescription,$incidentDate,$incidentMode,$incidentActive,$incidentVerified,$incidentRating,$incidentAlertStatus){
	// require "Connections/ushahidiconnection.php";
	// 	mysql_select_db($database_dbconnection, $dbconnection);
	// 	$sql = "INSERT INTO incident( location_id,form_id,locale,user_id,incident_title,incident_description,incident_date,incident_mode,incident_active,incident_verified,incident_rating,incident_alert_status ) VALUES ('".ucwords($fname)."','".ucwords($lname)."','".$phone."','".$hospital."','".$specialty."','".strtolower($email)."','".md5($rpassword)."','".$profession."','".$username."')";
	// 	$chk = mysql_query($sql);
	// 	if($chk){
	// 	$incidentID=mysql_insert_id($dbconnection);
	// 	}else{
	// 	$incidentID= "Error: ".mysql_error();
	// }
	// return $incidentID;
	// }
	
	// function updateIncidentCategory($incidentID,$catID){
	// 	require "Connections/ushahidiconnection.php";
	// 	mysql_select_db($database_dbconnection, $dbconnection);
	// 	$sql = "INSERT INTO incident_category( incident_id,category_id ) VALUES (".$incidentID.",".$catID.")";
	// 	$chk = mysql_query($sql);
	// 	if($chk){
	// 	$incatID=mysql_insert_id($dbconnection);
	// 	}else{
	// 	$incatID= "Error: ".mysql_error();
	// }
	// return $incatID;
	// }
	// function updateLevel($levelTitle,$levelDescription,$levelWeight){
	// require "Connections/ushahidiconnection.php";
	// 	mysql_select_db($database_dbconnection, $dbconnection);
	// 	$sql = "INSERT INTO level( incident_id,category_id ) VALUES (".$incidentID.",".$catID.")";
	// 	$chk = mysql_query($sql);
	// 	if($chk){
	// 	$levelID=mysql_insert_id($dbconnection);
	// 	}else{
	// 	$levelID= "Error: ".mysql_error();
	// }
	// return $levelID;
	// }
	// function updateLocation($locationName,$countryID,$latitude,$longitude,$locationDate){
	// require "Connections/ushahidiconnection.php";
	// 	mysql_select_db($database_dbconnection, $dbconnection);
	// 	$sql = "INSERT INTO location( location_name,country_id,latitude,longitude,location_visible,location_date ) VALUES ('".$locationName."',".$countryID.",".$latitude.",".$longitude.",1,'".$locationDate."')";
	// 	$chk = mysql_query($sql);
	// 	if($chk){
	// 	$locationID=mysql_insert_id($dbconnection);
	// 	}else{
	// 	$locationID= "Error: ".mysql_error();
	// }
	// return $locationID;
	// }
}
