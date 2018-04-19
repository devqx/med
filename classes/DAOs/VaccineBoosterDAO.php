<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VaccineBoosterDAO
 *
 * @author pauldic
 */
class VaccineBoosterDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineBooster.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PatientVaccineBooster.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/PatientVaccineBoosterDAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function addVaccineBooster($vb, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$pdo->beginTransaction();
			$sql = "INSERT INTO vaccines_booster (vaccine_id, start_age, start_age_scale, interval_, interval_scale)  VALUES " . "(" . $vb->getVaccine()->getId() . ", " . $vb->getStartAge() . ", '" . $vb->getStartAgeScale() . "', " . $vb->getInterval() . ", '" . $vb->getIntervalScale() . "')";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($stmt->rowCount() > 0) {
				$vb->setId($pdo->lastInsertId());
				$pdo->commit();
				return $vb;
			} else {
				$pdo->rollBack();
				$vb = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$stmt = null;
			$vb = null;
		}
		return $vb;
	}

	function updateVaccineBooster($vb, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "UPDATE vaccines_booster SET start_age=" . $vb->getStartAge() . ", start_age_scale='" . $vb->getStartAgeScale() . "', interval_=" . $vb->getInterval() . ", interval_scale='" . $vb->getIntervalScale() . "' WHERE id=" . $vb->getId();
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$stmt = null;
		} catch (PDOException $e) {
			$vb = null;
		}
		return $vb;
	}

	function getVaccineBooster($vbid, $getFull = FALSE, $pdo = null)
	{
		$vb = new VaccineBooster();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM vaccines_booster WHERE id=" . $vbid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vb->setId($row["id"]);
				if ($getFull) {
					$vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], $pdo);
				} else {
					$vac = new Vaccine($row["vaccine_id"]);
				}
				$vb->setVaccine($vac);
				$vb->setInterval($row['interval_']);
				$vb->setIntervalScale($row['interval_scale']);
				$vb->setStartAge($row['start_age']);
				$vb->setStartAgeScale($row['start_age_scale']);
			} else {
				$vb = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			errorLog($e);
			$vb = null;
		}
		return $vb;
	}

	function getVaccineBoosterByVaccine($vid, $getFull = FALSE, $pdo = null)
	{
		$vb = new VaccineBooster();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM vaccines_booster WHERE vaccine_id=" . $vid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vb->setId($row["id"]);
				if ($getFull) {
					$vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], FALSE, $pdo);
				} else {
					$vac = new Vaccine();
					$vac->setId($row["vaccine_id"]);
				}
				$vb->setVaccine($vac);
				$vb->setInterval($row['interval_']);
				$vb->setIntervalScale($row['interval_scale']);
				$vb->setStartAge($row['start_age']);
				$vb->setStartAgeScale($row['start_age_scale']);
			} else {
				$vb = null;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$vb = null;
		}
		return $vb;
	}

	function getVaccineBoosters($getFull = FALSE, $pdo = null)
	{
		$vbs = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM vaccines_booster";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$vb = new VaccineBooster();
				$vb->setId($row["id"]);
				if ($getFull) {
					$vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], FALSE, $pdo);
				} else {
					$vac = new Vaccine();
					$vac->setId($row["vaccine_id"]);
				}
				$vb->setVaccine($vac);
				$vb->setInterval($row['interval_']);
				$vb->setIntervalScale($row['interval_scale']);
				$vb->setStartAge($row['start_age']);
				$vb->setStartAgeScale($row['start_age_scale']);
				$vbs[] = $vb;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$vbs = array();
		}
		return $vbs;
	}

}
