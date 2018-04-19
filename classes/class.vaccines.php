<?php

class Vaccine_
{

	function __construct()
	{
		if (!isset ($_SESSION)) {
			session_start();
		}
	}

	function getAllVaccines()
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();

		$sql = "SELECT * FROM vaccines";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
		$str = '<ul class="list_blocks">';
		if ($stmt->rowCount() > 0) {
			do {
				$str .= '<li class="tag">' . $row ['description'] . ' (' . $row ['label'] . ') <a href="#"><i class="icon-edit"></i></a></li>';
			} while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT));
		} else {
			$str .= '<li>No Vaccine available</li>';
		}
		return $str . '</ul>';
	}

	function getVaccineDetails($v_id)
	{
		//TODO: check if the patient is insured for the vaccine and return the proper price
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		//TODO:returns only the price for SELF SCHEME need to check if patient is under insurance and return appropriatle
		$sql = "SELECT v.id, v.billing_code,v.label,v.description,i.selling_price AS price FROM vaccines v LEFT JOIN insurance_items_cost i ON v.billing_code=i.item_code WHERE v.id =" . $v_id . " AND i.insurance_scheme_id=1";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return $data[0];
	}

	function isBooster($vID)
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT IF(COUNT(*) > 0, TRUE, FALSE) FROM vaccines_booster WHERE vaccine_id = " . escape($vID);
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		return $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
	}


	function getDueVaccinesList()
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT vaccine_id, count(*) AS sum_ FROM patient_vaccine WHERE (date(now()) BETWEEN due_date AND expiration_date) AND entry_date IS NULL GROUP BY vaccine_id";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return json_encode($data);
	}

	function getDuePatientsList()
	{
		require_once $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT DISTINCT patient_id AS pid, GROUP_CONCAT(vaccine_id) AS vaccines FROM patient_vaccine WHERE (DATE(NOW()) BETWEEN due_date AND expiration_date) AND entry_date IS NULL GROUP BY patient_id";
		//$sql= "SELECT pid, sms_channel_address, email_channel_address FROM message_dispatch WHERE export_status = 1";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return json_encode($data);
	}

	function getDuePatientsListThisWeek()
	{
		require $_SERVER ['DOCUMENT_ROOT'] . "/Connections/MyDBConnector.php";
		$pdo = (new MyDBConnector())->getPDO();
		$sql = "SELECT patient_id AS pid, GROUP_CONCAT(vaccine_id) AS vaccines FROM patient_vaccine WHERE due_date BETWEEN getFirstDayOfWeekDate(TRUE) AND getFirstDayOfWeekDate(FALSE) GROUP BY patient_id";
//        $sql= "SELECT pid, sms_channel_address, email_channel_address FROM message_dispatch WHERE export_status = 1";
		$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
		$stmt->execute();
		$data = array();
		while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
			$data[] = $row;
		}
		return json_encode($data);
	}
}
