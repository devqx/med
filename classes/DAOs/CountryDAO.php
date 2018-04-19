<?php

/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 11/1/16
 * Time: 5:45 PM
 */
class CountryDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Nation.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getCountry($cid, $pdo = null)
	{
		$country = new Nation();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM countries WHERE id=".$cid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$country->setId($row['id']);
				$country->setCountryName($row['country_name']);
				$country->setIsoAlpha2Code($row['iso_alpha2_code']);
				$country->setIsoAlpha3Code($row['iso_alpha3_code']);
				$country->setIsoNumberic($row['iso_numeric']);
				$country->setDailingCode($row['dialing_code']);
			}
			$stmt = null;

		} catch (PDOException $e) {
			$country = null;
		}
		return $country;
	}

	function getCountries($pdo = null)
	{
		
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM countries";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			$data = [];
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$country = new Nation();
				$country->setId($row['id']);
				$country->setDailingCode($row['dialing_code']);
				$country->setIsoAlpha3Code($row['iso_alpha3_code']);
				$country->setIsoAlpha2Code($row['iso_alpha2_code']);
				$country->setIsoNumberic($row['iso_numeric']);
				$data[] = $country;
			}
	 return $data;
		} catch (PDOException $e) {
			return [];
		}

	}
}