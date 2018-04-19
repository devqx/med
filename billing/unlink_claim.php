<?php
/**
 * Created by PhpStorm.
 * User: nnamdi
 * Date: 11/29/16
 * Time: 1:23 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'].'/classes/Claim.php';

$bill_id = $_REQUEST['bill_id'];

$encounter_id = $_REQUEST['encounter_id'];

$pid = $_REQUEST['patient_id'];

$unlinkEncounter = (new Claim())->unlinkEncounter($pid,$encounter_id);
$unlinkBill_Lines = (new Claim())->unlinkLines($pid,$bill_id);

   if($unlinkEncounter && $unlinkBill_Lines){
       echo 'operation successful!';
   }
   else
   {
       exit('failed to unlink!');
   }