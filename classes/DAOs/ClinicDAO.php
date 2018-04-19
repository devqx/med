<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClinicDAO
 *
 * @author pauldic
 */
class ClinicDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/LGA.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/LGADAO.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function getClinic($cid, $getFull = FALSE, $pdo = null)
	{
		$cli = new Clinic();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinic WHERE clinicID=" . $cid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cli->setId($row['clinicID']);
				$cli->setName($row['name']);
				$cli->setAddress($row['address']);
				if ($getFull) {
					$dao = new LGADAO();
					$lga = $dao->getLGA($row['lga_id'], TRUE, $pdo);
				} else {
					$lga = new LGA();
					$lga->setId($row['lga_id']);
				}
				$cli->setLga($lga);
				$cli->setCode($row['hosp_code']);
				$cli->setFolioPrefix($row['folio_prefix']);
				$cli->setLocationLat($row['location_lat']);
				$cli->setLocationLong($row['location_long']);
				$cli->setKlass($row['class']);
				$cli->setPhoneNo($row['phone_no']);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$cli = null;
		}
		return $cli;
	}

	function getClinics()
	{
		$clinics = array();
		try {
			$pdo = $this->conn->getPDO();
			$sql = "SELECT * FROM clinic";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cli = new Clinic();
				$cli->setId($row['clinicID']);
				$cli->setName($row['name']);
				$cli->setAddress($row['address']);
				$lga = new LGA();
				$lga->setId($row['lga_id']);
				$cli->setLga($lga);
				$cli->setCode($row['hosp_code']);
				$cli->setFolioPrefix($row['folio_prefix']);
				$cli->setLocationLat($row['location_lat']);
				$cli->setLocationLong($row['location_long']);
				$cli->setKlass($row['class']);
				$cli->setPhoneNo($row['phone_no']);
				$clinics[] = $cli;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$clinics = array();
		}
		return $clinics;
	}

	function getClinicByLGA($lid, $getFull = FALSE, $pdo = null)
	{
		$clinics = array();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM clinic WHERE lga_id=" . $lid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$cli = new Clinic();

				$cli->setId($row['clinicID']);
				$cli->setName($row['name']);
				$cli->setAddress($row['address']);
				if ($getFull) {
					$dao = new LGADAO();
					$lga = $dao->getLGA($row['lag_id'], FALSE, $pdo);
				} else {
					$lga = new LGA();
					$lga->setId($row['lga_id']);
				}
				$cli->setLga($lga);
				$cli->setCode($row['hosp_code']);
				$cli->setFolioPrefix($row['folio_prefix']);
				$cli->setLocationLat($row['location_lat']);
				$cli->setLocationLong($row['location_long']);
				$cli->setKlass($row['class']);
				$cli->setPhoneNo($row['phone_no']);

				$clinics[] = $cli;
			}
			$stmt = null;
		} catch (PDOException $e) {
			$clinics = array();
		}
		return $clinics;
	}

	function updateClinic($c, $pdo = null)
	{
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$lga = (new LGADAO())->getLGA($c->getLga(), TRUE, $pdo);
			$folio_prefix = !is_blank($c->getFolioPrefix()) ? quote_esc_str($c->getFolioPrefix()) : 'null';
			$sql = "UPDATE clinic SET `name`='" . $c->getName() . "', hosp_code='" . $c->getCode() . "', address='" . $c->getAddress() . "', state_id=" . $lga->getState()->getId() . ", lga_id='" . $lga->getId() . "', location_lat=" . $c->getLocationLat() . ", location_long=" . $c->getLocationLong() . ", class='" . $c->getKlass() . "', phone_no='" . $c->getPhoneNo() . "', folio_prefix=$folio_prefix WHERE clinicID=" . $c->getId();
			//error_log($sql);
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			$stmt = null;
			return $c;
		} catch (PDOException $e) {
			return null;
		}
	}
}
