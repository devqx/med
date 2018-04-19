<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 11/27/15
 * Time: 3:14 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$patient = new Manager();
$start_date = (isset($_GET['from']))? $_GET['from'] : NULL;
$end_date = (isset($_GET['to']))? $_GET['to'] : NULL;
$id = $_GET['pid'];
$data = $patient->_getVisitSummary($id, $start_date, $end_date);

?>
<div style="margin-top:6px" class="no-print">
    <div class="row-fluid">
        <div class="span3 input-prepend" style="margin-left: 0;">
            <span class="add-on">From</span>
            <input class="span10" type="text" placeholder="Start Date" name="from" value="<?=$start_date?>" id="from">
        </div>
        <div class="span3 input-prepend">
            <span class="add-on">To</span>
            <input class="span10" type="text" placeholder="End Date" name="to" value="<?= $end_date ?>" id="to" disabled="disabled">
        </div>
    </div>
</div>
<div style="overflow: hidden" id="lastVisitSummary">
    <table class="table table-bordered">
        <tr><td colspan="5"><strong>Last Vitals</strong></td></tr>
    <?php
    $DATE_FORMAT = "Y M, d";
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/VitalSignDAO.php';
    $vitals = (new VitalSignDAO())->getPatientLastVitalSigns($id, NULL, FALSE, ["Temperature","Blood Pressure","Respiration","Pulse","Weight"]);
    ?>
        <tr><?php foreach ($vitals as $v){/*$v=new VitalSign();*/?><td><strong><?= ucwords($v->getType()) ?></strong></td> <?php }?></tr>
        <tr><?php foreach ($vitals as $v){/*$v=new VitalSign();*/?><td><?=$v->getValue()?></td> <?php }?></tr>
        <tr><?php foreach ($vitals as $v){/*$v=new VitalSign();*/?><td><?=date($DATE_FORMAT, strtotime($v->getReadDate()))?></td> <?php }?></tr>
    </table>
    <table class="table table-bordered">
        <tr><td colspan="4"><strong>Diagnoses</strong></td></tr>
        <?php if(count($data['diagnoses']) > 0){?><tr><td><strong>Date</strong></td><td><strong>Diagnosis</strong></td><td><strong>Type</strong></td><td><strong>By</strong></td></tr><?php }else {?>
            <tr><td colspan="4"><em>No Diagnoses within the period</em></td></tr>
        <?php }?>
        <?php foreach(array_reverse($data['diagnoses']) as $diagnosis){ //$diagnosis=new PatientDiagnosis();?>
        <tr><td><?= date($DATE_FORMAT, strtotime($diagnosis->getDate())) ?></td><td><?=$diagnosis->getDiagnosis()?></td><td><?= $diagnosis->getType()?></td><td><?= $diagnosis->getDiagnosedBy()->getFullname()?></td></tr>
        <?php }?>
    </table>
    <table class="table table-bordered">
        <tr><td colspan="3"><strong>Other Notes</strong></td></tr>
        <?php if(count($data['notes']) > 0){?><tr><td nowrap><strong>Date</strong></td><td><strong>Note</strong></td><td><strong>By</strong></td></tr>
        <?php } else {?>
            <tr><td colspan="3"><em>No Other Notes</em></td></tr>
        <?php }?>
        <?php foreach(array_reverse($data['notes']) as $note){//$note=new VisitNotes()?>
        <tr><td nowrap><?= date($DATE_FORMAT, strtotime($note->date_of_entry)) ?></td><td>
                <?php if($note->note_type=='o'){?>
                <?php }else if($note->note_type=='d'){?>
                    <span class="doc_note">Doc Note:</span>
                <?php }else if($note->note_type=='p'){?>
                    <span class="plan_note">Plan:</span>
                <?php }else if($note->note_type=='a'){?>
                    <span class="diag_note">Diagnosis:</span>
                <?php }else if($note->note_type=='i'){?>
                    <span class="inv_note">Investigation:</span>
                <?php }else if($note->note_type=='g'){?>
                    <span class="diag_note">Diagnosis Note:</span>
                <?php }else if($note->note_type=='e'){?>
                    <span class="inv_note">Examination:</span>
                <?php }else if($note->note_type=='r'){?>
                    <span class="ref_note">Referral:</span>
                <?php }else if($note->note_type=='v'){?>
                    <span class="review_note">Systems Review:</span>
                <?php }else if($note->note_type=='x'){?>
                    <span class="review_note">Physical Exam:</span>
                <?php }else {?>
                    <span class="com_note">Complaint:</span>
                <?php } ?>

                <?= $note->description?></td><td nowrap><?= $note->username?></td></tr>
        <?php }?>
    </table>
</div>
