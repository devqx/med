<!--<h4>Vaccine Summary Page</h4>-->
<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.vaccines.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
$vaccines = new Vaccine_();
$patients = new Manager();
$vaccines_due = json_decode($vaccines->getDueVaccinesList());
$vaccines_sum = 0;
foreach($vaccines_due as $tot){
    $vaccines_sum += $tot->sum_;
}
?>
<?php if(isset($_GET['type']) && $_GET['type']=='p'){
    $patients_due = json_decode($vaccines->getDuePatientsList());
    ?>

<div>
    <h5><i class="icon-th-list"></i> All Patients due for Vaccine (<?=count($patients_due)?>) | <a href="/immunization/summary.php?type=pw">Patients due this week</a></h5>
    <table class="table table-bordered table-hover">
        <thead>
        <tr><th>Patient</th><th>Vaccine</th><th>Patient's Phone</th><th>Patient's Email</th></tr></thead>
        <?php
        foreach($patients_due as $patient){
            $vaccines_due_for_patient = explode(",",$patient->vaccines);
            echo '<tr><td><a href="/immunization/patient_immunization_profile.php?id='.$patient->pid.'">'.$patients->getPatientInfo($patient->pid)['name'].'</a></td><td>';
                foreach($vaccines_due_for_patient as $vaccines_due_for_patient){
                   echo '<span class="tag">'.$vaccines->getVaccineDetails($vaccines_due_for_patient)['label'].'</span>';
                }
            echo '</td> <td>'.$patients->getPatientInfo($patient->pid)['phonenumber'].'</td><td>'.$patients->getPatientInfo($patient->pid)['email'].'</td></tr>';
        }?></table>
        </div>
        <?php }?>
<?php if(isset($_GET['type']) && $_GET['type']=='pw'){
    $patients_due_this = json_decode($vaccines->getDuePatientsListThisWeek());
    ?>

<div>
    <h5><i class="icon-th-list"></i> <a href="/immunization/summary.php?type=p">All Patients due for Vaccine </a> | Patients due this week (<?=count($patients_due_this)?>)</h5>
    <table class="table table-bordered table-hover">
        <thead>
        <tr><th>Patient</th><th>Vaccine</th><th>Patient's Phone</th><th>Patient's Email</th></tr></thead>
        <?php
        foreach($patients_due_this as $patient){
            $vaccines_due_for_patient = explode(",",$patient->vaccines);
            echo '<tr><td><a href="/immunization/patient_immunization_profile.php?id='.$patient->pid.'">'.$patients->getPatientInfo($patient->pid)['name'].'</a></td><td>';
                foreach($vaccines_due_for_patient as $vaccines_due_for_patient){
                   echo '<span class="tag">'.$vaccines->getVaccineDetails($vaccines_due_for_patient)['label'].'</span>';
                }
            echo '</td> <td>'.$patients->getPatientInfo($patient->pid)['phonenumber'].'</td><td>'.$patients->getPatientInfo($patient->pid)['email'].'</td></tr>';
        }?></table>
        </div>
        <?php }?>

<?php if(isset($_GET['type']) && $_GET['type']=='v'){?>
<div>
    <h5><i class="icon-th-list"></i> Vaccines quantities estimated for use this week (<?= $vaccines_sum?>)</h5>
    <table class="table table-bordered table-hover">
        <thead><tr><th>Vaccine</th><th>Quantity <em class="fadedText">(based on patients count)</em></th></tr></thead>
        <?php foreach($vaccines_due as $vaccine){?><tr><td><?=$vaccines->getVaccineDetails($vaccine->vaccine_id)['label']?></td><td><?=$vaccine->sum_?></td></tr><?php }?>
    </table>
</div>
<?php }?>