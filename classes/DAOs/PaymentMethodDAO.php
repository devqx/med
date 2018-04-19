<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PaymentMethodDAO
 *
 * @author pauldic
 */
class PaymentMethodDAO
{
	private $conn = null;

	function __construct()
	{
		try {
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Clinic.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/PaymentMethod.php';
			require_once $_SERVER ['DOCUMENT_ROOT'] . '/functions/utils.php';
			$this->conn = new MyDBConnector();
		} catch (PDOException $e) {
			exit('ERROR: ' . $e->getMessage());
		}
	}

	function get($lid, $getFull = FALSE, $pdo = null)
	{
		$pm = new PaymentMethod();
		try {
			$pdo = $pdo == null ? $this->conn->getPDO() : $pdo;
			$sql = "SELECT * FROM payment_methods WHERE id=" . $lid;
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();
			if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$ledger = !is_blank($row['ledger_id'])?$row['ledger_id']:'- -';
				$pm->setId($row["id"])->setName($row["name"])->setType($row["type"])->setLedgerId($ledger);
				if ($getFull) {
					$clinic = (new ClinicDAO())->getClinic($row['hospid'], FALSE, $pdo);
				} else {
					$clinic = new Clinic();
					$clinic->setId($row["hospid"]);
				}
				$pm->setClinic($clinic);
			}
			$stmt = null;
		} catch (PDOException $e) {
			$pm = null;
		}
		return $pm;
	}

	function all($getFull = FALSE)
	{
		$pms = array();
		try {
			$pdo = $this->conn->getPDO();
			$sql = "SELECT * FROM payment_methods";
			$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
			$stmt->execute();

			while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
				$pms[] = $this->get($row['id'], $getFull, $pdo);
			}
		} catch (PDOException $e) {
			errorLog($e);
			$pms = array();
		}
		return $pms;
	}
}
