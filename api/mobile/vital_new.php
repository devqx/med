<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 8/3/17
 * Time: 2:10 PM
 */
if(isset($_SERVER["CONTENT_TYPE"]) && strpos($_SERVER["CONTENT_TYPE"], "application/json") !== false) {
    $_POST = array_merge($_POST, (array)json_decode(trim(file_get_contents('php://input')), true));
    header("Access-Control-Allow-Origin:*");
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/VitalSignDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/VitalSign.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientDemograph.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/InPatient.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/StaffDirectory.php';
    if(isset($_POST['staffId']) && isset($_POST['patient_Id']) && isset($_POST['inpatient_id'])){
        $pdo = (new MyDBConnector())->getPDO();
        $pdo->beginTransaction();
        if(isset($_POST['type_id'])){
                $type = (new VitalDAO())->get($_POST['type_id'], $pdo);
                $new = (new VitalSign())->setType($type)->setPatient(new PatientDemograph($_POST['patient_Id']))->setInPatient( new InPatient($_POST['inpatient_id']))
                    ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_POST['staffId']) )
                    ->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($_POST['v_value'])->add($pdo);
                if($new != null){
                    $pdo->commit();
                    exit('success:Saved');
                }
                $pdo->rollBack();
                exit('error:Failed to save reading');
        }else if(isset($_POST['type'])){
            $weight = $_POST['w_value'];
            $height = $_POST['h_value'];
            error_log(json_encode($_POST['type']));
            $value = $_POST['type']=='BMI' ? number_format(($weight / ($height * $height)), 1):
                //else it has to be BSA
                number_format(parseNumber(($weight ^ 0.425 * ($height/100) ^ 0.725) * 0.007184), 2);
            $type = (new VitalDAO())->getByName($_POST['type'], $pdo);
            $new1 = (new VitalSign())->setType((new VitalDAO())->getByName('Weight', $pdo))->setPatient(new PatientDemograph($_POST['patient_Id']))->setInPatient( new InPatient($_POST['inpatient_id']))
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_POST['staffId']) )
                ->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($weight)->add($pdo);

            $new2 = (new VitalSign())->setType((new VitalDAO())->getByName('Height', $pdo))->setPatient(new PatientDemograph($_POST['patient_Id']))->setInPatient( new InPatient($_POST['inpatient_id']))
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_POST['staffId']) )
                ->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($height)->add($pdo);

            $new = (new VitalSign())->setType($type)->setPatient(new PatientDemograph($_POST['patient_Id']))->setInPatient(new InPatient($_POST['inpatient_id']))
                ->setEncounter(NULL)->setHospital(new Clinic(1))->setReadBy( new StaffDirectory($_POST['staffId']) )
                ->setReadDate( date(MainConfig::$mysqlDateTimeFormat) )->setValue($value)->add($pdo);
            if($new != null && $new1 != null && $new2 != null){
                $pdo->commit();
                exit('success:Saved');
            }
        }
    }
}