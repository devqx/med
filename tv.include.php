<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 7/9/15
 * Time: 11:51 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientQueueDAO.php';
$dao=new PatientQueueDAO();
//$ts=getTypeOptions('type', 'patient_queue');
//$sub_ts = (new PatientQueueDAO())->getSubTypes();
$queues = (new PatientQueueDAO())->getPatientQueueFiltered("2014-12-15", "2014-12-15", [], ['Active', 'Blocked'], TRUE);
?>
<style type="text/css">
    body {zoom: 1;}
    .list1 {
        /*display: table-cell;*/
        display: block;
        margin-left: -10px;
        overflow: hidden;
        height: 40px;
        margin-bottom: 10px;

    }
    .no-border td {
        border: none !important;
        margin: 10px 0 !important;
    }
</style>
<table class="table no-border">
    <thead><tr><th>TIME IN</th><th>PATIENT</th><th>TO SEE:</th></tr></thead>

    <?php foreach ($queues as $i => $q) {
    if ($q->getPatient()) { ?>
    <tr>
        <td><span class="btn list1"><?= date("h:iA", strtotime($q->getEntryTime()))?></span></td>
        <td><span class="btn list1"><?= $q->getPatient()->getShortname() ?></span></td>
        <td><span class="btn list1"><?= ($q->getSpecialization() != NULL ? $q->getSpecialization()->getName() : '') ?>
            <br>
            <?= ucwords($q->getType()) ?></span></td></tr>
<!--        <tr><td colspan="3">&nbsp;</td></tr>-->
    <?php }
}?>
</table>