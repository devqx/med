<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/6/18
 * Time: 3:39 PM
 */

class InPatientHealthState implements JsonSerializable
{

    private $health_status;
    private $patient_id;
    private $risk_to_fall;
    private $conn = null;


    /**
     * InPatientHealth constructor.
     */
    public function __construct()
    {

        try{

            require_once $_SERVER['DOCUMENT_ROOT'] .'/Connections/MyDBConnector.php';

            $this->conn = new MyDBConnector();
        }
        catch (PDOException $e){

            error_log($e);
        }
    }

    /**
     * @return mixed
     */
    public function getHealthStatusId()
    {
        return $this->health_status;
    }

    /**
     * @param mixed $health_status
     */
    public function setHealthStatusId($health_status)
    {
        $this->health_status = $health_status;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patient_id;
    }

    /**
     * @param mixed $patient_id
     * @return InPatientHealth
     */
    public function setPatientId($patient_id)
    {
        $this->patient_id = $patient_id;
        return $this;
    }



    public function updatePatientHealthStatus(){

        $pdo = $this->conn->getPDO();

        $sql = " UPDATE in_patient set health_state_id= '".$this->getHealthStatusId()."' , risk_to_fall = '".$this->getRiskToFall()."' where patient_id='".$this->getPatientId()."' ";

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

    /**
     * @return mixed
     */
    public function getRiskToFall()
    {
        return $this->risk_to_fall;
    }

    /**
     * @param mixed $risk_to_fall
     * @return InPatientHealthState
     */
    public function setRiskToFall($risk_to_fall)
    {
        $this->risk_to_fall = $risk_to_fall;
        return $this;
    }




    public function getAllHealthStates(){

        $states = [];

        $sql = "SELECT * FROM health_state";

        $pdo = $this->conn->getPDO();

        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();

        if( $stmt->rowCount() > 0 ){

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){

                $states[] = $row;
            }

        }

        return $states;

    }

    /**
     * @return MyDBConnector|null
     */
    public function getInPatientHealthState($inpid){

        $health_state = [];

        $sql = "SELECT hs.state,hs.id, ip.risk_to_fall FROM health_state hs  LEFT JOIN in_patient ip on hs.id = ip.health_state_id where patient_id = '".$inpid."' ";

        $pdo = $this->conn->getPDO();

        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();

        if( $stmt->rowCount() > 0 ){

            while($row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)){

                $health_state['state'] = $row['state'];
                $health_state['risk_to_fall'] = $row['risk_to_fall'];
                $health_state['id'] = $row['id'];
            }

        }

        return $health_state;


    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
        return (object)get_object_vars($this);
    }

}