<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/11/15
 * Time: 3:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/PatientDentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/StaffDirectory.php';

$request=(new PatientDentistryDAO())->get($_POST['id']);

if(isset($_POST['full']) && $_POST['full']== 'true'){
    $request->setApprovedBy( (new StaffDirectory($_SESSION['staffID'])) );
    $request->setApprovedDate( date("Y-m-d H:i:s") );
    exit(json_encode((new PatientDentistryDAO())->approve($request)));
} else {
    $s=(new PatientDentistryDAO())->approvePartial($request);
    if($s!== null){
        echo "ok";
    }else {
        echo "error";
    }
}

