<?php
/**
 * Created by PhpStorm.
 * User: oluwaseunpaul
 * Date: 4/3/18
 * Time: 3:17 PM
 */

class EarlyWarningScore implements JsonSerializable
{

    private $blood_pressure;
    private $temperature;
    private $oxygen_saturations;
    private $respiration_rate;
    private $heart_rate;
    private $loc;
    private $supplemental_oxygen;
    private $score;
    private $patient_id;
    private $admission_id;
    private $taken_by_id;
    private $type_id;
    private $conn = null;
    private $age_range;
    private $color_code;
    private $score_average;
    /**
     * EarlyWarningScore constructor.
     */
    public function __construct()
    {
        try{
            require_once $_SERVER ['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
            $this->conn = new MyDBConnector();
        }
        catch (PDOException $e){
                error_log($e);
        }



    }

    /**
     * @return mixed
     */
    public function getBloodPressure()
    {
        return $this->blood_pressure;
    }

    /**
     * @param mixed $blood_pressure
     * @return EarlyWarningScore
     */
    public function setBloodPressure($blood_pressure)
    {
        $blood_pressure = explode('/', $blood_pressure )[0];

        switch ($blood_pressure){

            case ( $blood_pressure <= 90) :
                $this->blood_pressure = 3;
                break;

            case ( in_array($blood_pressure, range(91, 100)) ) :
                $this->blood_pressure = 2;
                break;

            case (in_array($blood_pressure, range(101, 110))) :
                $this->blood_pressure = 1;
                break;


            case (in_array($blood_pressure, range(111, 219))) :
                $this->blood_pressure = 0;
                break;

            case ($blood_pressure >= 220) :
                $this->blood_pressure = 3;
                break;

        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemperature()
    {
        return $this->temperature;
    }

    /**
     * @param mixed $temperature
     * @return EarlyWarningScore
     */
    public function setTemperature($temperature)
    {


        switch ($temperature){

            case ( $temperature <= 35.0) :
                $this->temperature = 3;
                break;

            case ( in_array($temperature, range(35.1, 36.0)) ):
                $this->temperature = 1;
                break;

            case (in_array($temperature, range(36.1, 38.0))):
                $this->temperature = 0;
                break;


            case ( in_array($temperature, range(38.1, 39.0))):
                $this->temperature = 1;
                break;

            case ($temperature >= 39.1) :
                $this->temperature = 2;
                break;

        }


        return $this;
    }

    /**
     * @return mixed
     */
    public function getOxygenSaturations()
    {
        return $this->oxygen_saturations;
    }

    /**
     * @param mixed $oxygen_saturations
     * @return EarlyWarningScore
     */
    public function setOxygenSaturations($oxygen_saturations)
    {

        switch ($oxygen_saturations){

            case ( $oxygen_saturations <= 91) :
                $this->oxygen_saturations = 3;
                break;

            case ( in_array($oxygen_saturations, range(92, 93)) ) :
                $this->oxygen_saturations = 2;
                break;

            case (in_array($oxygen_saturations, range(94, 95))) :
                $this->oxygen_saturations = 1;
                break;


            case ($oxygen_saturations >= 96) :
                $this->oxygen_saturations = 0;
                break;

        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRespirationRate()
    {
        return $this->respiration_rate;
    }

    /**
     * @param mixed $respiration_rate
     * @return EarlyWarningScore
     */
    public function setRespirationRate($respiration_rate)
    {

        switch ($respiration_rate){

            case ( $respiration_rate <= 8) :
                $this->respiration_rate = 3;
                break;

            case ( in_array($respiration_rate, range(9, 11)) ) :
                $this->respiration_rate = 1;
                break;

            case (in_array($respiration_rate, range(12, 20))) :
                $this->respiration_rate = 0;
                break;

            case (in_array($respiration_rate, range(21, 24))) :
                $this->respiration_rate = 0;
                break;

            case ($respiration_rate >= 25) :
                $this->respiration_rate = 3;
                break;

        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeartRate()
    {
        return $this->heart_rate;
    }

    /**
     * @param mixed $heart_rate
     * @return EarlyWarningScore
     */
    public function setHeartRate($heart_rate)
    {

        switch ($heart_rate){

            case ( $heart_rate <= 40) :
                $this->heart_rate = 3;
                break;

            case ( in_array($heart_rate, range(41, 50)) ) :
                $this->heart_rate = 1;
                break;

            case (in_array($heart_rate, range(51, 90))) :
                $this->heart_rate = 2;
                break;

            case (in_array($heart_rate, range(91, 110))) :
                $this->heart_rate = 1;
                break;

            case (in_array($heart_rate, range(111, 130))) :
                $this->heart_rate = 2;
                break;

            case ($heart_rate >= 131 ) :
                $this->heart_rate = 3;
                break;

        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * @param mixed $loc
     * @return EarlyWarningScore
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSupplementalOxygen()
    {
        return $this->supplemental_oxygen;
    }

    /**
     * @param mixed $supplemental_oxygen
     * @return EarlyWarningScore
     */
    public function setSupplementalOxygen($supplemental_oxygen)
    {
        $this->supplemental_oxygen = $supplemental_oxygen;
        return $this;
    }

    public function setScore($score){

        $this->score =  $this->blood_pressure + $this->heart_rate + $this->respiration_rate + $this->temperature + $this->oxygen_saturations+ $this->loc + $this->supplemental_oxygen;
        return $this;

    }


    public function getScore(){
        return abs( $this->score );
    }

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.

        return (object)get_object_vars($this);
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
     * @return EarlyWarningScore
     */
    public function setPatientId($patient_id)
    {
        $this->patient_id = $patient_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdmissionId()
    {
        return $this->admission_id;
    }

    /**
     * @param mixed $admission_id
     * @return EarlyWarningScore
     */
    public function setAdmissionId($admission_id)
    {
        $this->admission_id = $admission_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTakenById()
    {
        return $this->taken_by_id;
    }

    /**
     * @param mixed $taken_by_id
     * @return EarlyWarningScore
     */
    public function setTakenById($taken_by_id)
    {
        $this->taken_by_id = $taken_by_id;
        return $this;
    }




    public function save(){

        $pdo = $this->conn->getPDO();

        error_log("Color code " .$this->getColorCode());

        $sql = "INSERT INTO early_warning_sign ( respiration_rate , oxygen_saturations, heart_rate, loc, supplemental_oxygen, temperature, systolic_bp, score, patient_id, admission_id, taken_by ) VALUES ( '".$this->getRespirationRate()."' , '".$this->getOxygenSaturations()."' , '".$this->getHeartRate()."' , '".$this->getLoc()."' , '".$this->getSupplementalOxygen()."', '".$this->getTemperature()."' , '".$this->getBloodPressure()."' , '".$this->getScore()."', '".$this->getPatientId()."', '".$this->getAdmissionId()."', '".$this->getTakenById()."')";

        //echo $sql;

        $stmt = $pdo->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );

        $stmt->execute();

        if($stmt->rowCount() > 0){

            echo "Early Warning Sign saved successfully";
        }
        else{

            return null;
        }

        return $this;

    }

    public function getAll($patient_id, $admission_id){

        $result = [];

        $pdo = $this->conn->getPDO();

        $sql = "SELECT * FROM early_warning_sign ews left join staff_directory sd on ews.taken_by = sd.staffId where patient_id = $patient_id  and admission_id = $admission_id  order by ews.take_time DESC";

        //echo $sql;

        $stmt = $pdo->prepare( $sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL) );

        $stmt->execute();

        if($stmt->rowCount() > 0){

            while( $row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT)) {

                $result[] = $row;
            }

            return $result;

        }
        else{

            return null;
        }

    }


    public function saveToVitalSign($request, $misc, $value){

        $pdo = $this->conn->getPDO();
        $sql = "INSERT INTO vital_sign ( patient_id, read_date, in_patient_id, type_id, value, hospital_id, read_by, encounter_id) VALUES ('".$misc->patient_id."', NOW(), '".$misc->patient_id."', '".$this->getTypeId()."' , '".$value."', '".$misc->hospital_id."', '".$misc->read_by."' , '".$misc->encounter_id."' )";

        $stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));

        $stmt->execute();

        return $this;

        }/**
     * @return mixed
     */


    public function getTypeId()
    {
        return $this->type_id;
    }/**
     * @param mixed $type_id
     * @return EarlyWarningScore
     */
    public function setTypeId($type_id)
    {

        switch ($type_id){
            case ($type_id == "temperature"):
                $this->type_id = 20;
                break;

            case ($type_id=="systolic_bp"):
                $this->type_id = 3;
                break;

            case ($type_id=="oxygen_saturations"):
                $this->type_id = 18;
                break;

            case ($type_id=="respiration_rate"):
                $this->type_id = 17;
                break;
        }

        return $this->type_id;
    }

    /**
     * @return mixed
     */
    public function getColorCode()
    {
        return $this->color_code;
    }

    /**
     * @param mixed $color_code
     * @return EarlyWarningScore
     */
    public function setColorCode($color_code)
    {

        switch ($this->score_average){

            case  '':
                break;

        }

        error_log("The color code is ". $this->getColorCode() );

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScoreAverage()
    {
        return $this->score_average;
    }

    /**
     * @param mixed $score_average
     * @return EarlyWarningScore
     */
    public function setScoreAverage($score_average)
    {
        $this->score_average =  ( $this->score / floatval(7) );

        //error_log("average score: ". $this->score_average);

        return $this;
    }











}