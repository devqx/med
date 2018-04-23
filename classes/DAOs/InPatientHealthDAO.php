<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/22/18
 * Time: 11:12 PM
 */

class InPatientHealthDAO
{

    private $conn = null;

    public function __construct()
    {

        try{

            require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/HealthStates.php';
            require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatientHealthState.php';


            $this->conn = new MyDBConnector();
        }
        catch (PDOException $e){

            error_log($e);
        }
    }



    public function updatePatientHealthStatus( $healthState ){


        $pdo = $this->conn->getPDO();

        $sql = " UPDATE in_patient set health_state_id= '".$healthState->getHealthStatusId()."' , risk_to_fall = '".$healthState->getRiskToFall()."' where patient_id='".$healthState->getPatientId()."' ";

        error_log( $sql );
        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL ));

        $stmt->execute();

        if($stmt->rowCount() > 0){

            error_log('saved successfully');
        }
        else{

            error_log('not saved');

        }


    }



    public function getAllHealthStates(){


        $health_states = array();

        $sql = "SELECT * FROM health_state";

        $pdo = $this->conn->getPDO();

        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();

        if( $stmt->rowCount() > 0 ){

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){

                $h_state = new HealthStates();
                $h_state->setId($row['id']);
                $h_state->setState($row['state']);

                $health_states[] = $h_state;

            }

        }

        return $health_states;

    }



    public function getInPatientHealthState($inpid){

        $phealth = new InPatientHealthState();


        $sql = "SELECT hs.state,hs.id, ip.risk_to_fall FROM health_state hs  LEFT JOIN in_patient ip on hs.id = ip.health_state_id where patient_id = '".$inpid."' ";

        $pdo = $this->conn->getPDO();

        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();

        if( $stmt->rowCount() > 0 ){

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){

                $phealth->setHealthStatusId( $row['state']);
                $phealth->setRiskToFall($row['risk_to_fall']);
                $phealth->setId($row['id']);

            }

        }

        return $phealth;


    }



}