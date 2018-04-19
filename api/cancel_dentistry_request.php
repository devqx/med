<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/2/15
 * Time: 12:21 PM
 */
if (!isset($_SESSION)) {
    @session_start();
}
if (isset($_POST['id'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/DAOs/PatientDentistryDAO.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/StaffDirectory.php";
    $request = (new PatientDentistryDAO())->get($_POST['id']);

    $request->setCanceledBy(new StaffDirectory($_SESSION['staffID']));
    // check if not cancelled already
    if (!boolval($request->getCancelled())) {
        exit(json_encode((new PatientDentistryDAO())->cancel($request)));
    } else {
        exit(json_encode(false));
    }
}

exit(json_encode(false));