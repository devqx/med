<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VaccineLevelDAO
 *
 * @author pauldic
 */
class VaccineLevelDAO
{
    private $conn = null;

    function __construct()
    {
        try {
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/Vaccine.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/VaccineLevel.php';
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/classes/DAOs/VaccineDAO.php';
            $this->conn = new MyDBConnector();
        } catch (PDOException $e) {
            exit('ERROR: ' . $e->getMessage());
        }
    }

    function addVaccineLevels($vid, $vls, $pdo = NULL)
    {
        try {
            $vlsss = $vls;
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            foreach ($vls as $key => $vl) {
//                $vl = new VaccineLevel();
                $sql = "INSERT INTO vaccine_levels (vaccine_id, `level`, start_index, end_index, start_age, end_age, duration, start_age_scale, end_age_scale)  VALUES "
                    . "(" . $vid . ", " . $vl->getLevel() . ", " . $vl->getStartIndex() . ", " . ($vl->getEndIndex() + 1) . ", " . $vl->getStartAge() . ", " . ($vl->getEndAge() + 1) . ", " . $vl->getDuration() . ", '" . $vl->getAgeScaleStart() . "', '" . $vl->getAgeScaleStop() . "')";

                //error_log("...".$sql);
                $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
                $stmt->execute();
                $vl->setId($pdo->lastInsertId());
                $vlsss[$key] = $vl;
            }
            $stmt = NULL;
        } catch (PDOException $e) {
            error_log("ERROR: ".$e->getMessage() .":::". $e->getLine()   );
            $vlsss = array();
        }
        return $vlsss;
    }

    function getVaccineLevel($vlid, $getFull = FALSE, $pdo = NULL)
    {
        $vl = new VaccineLevel();
        try {
            $pdo = $pdo == NULL ? $this->conn->getPDO() : $pdo;
            $sql = "SELECT * FROM vaccine_levels WHERE id=" . $vlid;
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vl->setId($row["id"]);
                if ($getFull) {
                    $vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], FALSE, $pdo);
                } else {
                    $vac = new Vaccine($row["vaccine_id"]);
                }
                $vl->setVaccine($vac);
                $vl->setLevel($row['level']);
                $vl->setStartIndex($row['start_index']);
                $vl->setEndIndex($row['end_index']);
                $vl->setStartAge($row['start_age']);
                $vl->setEndAge($row['end_age']);
                $vl->setDuration($row['duration']);

                $vl->setAgeScaleStart($row['start_age_scale']);
                $vl->setAgeScaleStop($row['end_age_scale']);
            } else {
                $vl = NULL;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $vl = NULL;
            errorLog($e);
        }
        return $vl;
    }

    function getVaccineLevels($getFull = FALSE)
    {
        $vls = array();
        try {
            $pdo = $this->conn->getPDO();
            $sql = "SELECT * FROM vaccine_levels";
            $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {
                $vl = new VaccineLevel();
                $vl->setId($row["id"]);
                if ($getFull) {
                    $vac = (new VaccineDAO())->getVaccine($row['vaccine_id'], FALSE, $pdo);
                } else {
                    $vac = new Vaccine();
                    $vac->setId($row["vaccine_id"]);
                }
                $vl->setVaccine($vac);
                $vl->setLevel($row['level']);
                $vl->setStartIndex($row['start_index']);
                $vl->setEndIndex($row['end_index']);
                $vl->setStartAge($row['start_age']);
                $vl->setEndAge($row['end_age']);
                $vl->setDuration($row['duration']);

                $vl->setAgeScaleStart($row['start_age_scale']);
                $vl->setAgeScaleStop($row['end_age_scale']);
                $vls[] = $vl;
            }
            $stmt = null;
        } catch (PDOException $e) {
            $vls = array();
            errorLog($e);
        }
        return $vls;
    }

}
