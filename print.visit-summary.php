<?php
error_reporting(0);
$_GET['id'] = $_GET['pid'];
$_GET['view'] = "visit_summary";
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/PatientDemographDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/AppointmentDAO.php';
$mgr = (new PatientDemographDAO())->getPatient($_GET['id'],FALSE);

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$patient = new Manager();
$start_date = (isset($_GET['from']))? $_GET['from'] : NULL;
$end_date = (isset($_GET['to']))? $_GET['to'] : NULL;
$id = $_GET['pid'];
$data = $patient->_getVisitSummary($id, $start_date, $end_date);
?>
<html>
<head>
    <link rel="stylesheet" href="/style/def.css">
    <link rel="stylesheet" href="/style/bootstrap.css">
    <link rel="stylesheet" href="/style/style.css">
    <link rel="stylesheet" href="/style/printer.margins.css" media="all">
    <script type="text/javascript" src="/js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/assets/moment/moment.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#age").html(moment("<?php echo $mgr->getDateOfBirth()?>").fromNow(true));
        })
    </script>
</head>
<body class="print content">
    <div class="head">
        <img src="img/logo/logo.jpg?r=<?=rand()?>" style="height: 80px; float:left; margin-right:10px; padding-right: 10px; border-right:1px solid #ddd">
        <div><h1><?= $mgr->getBaseClinic()->getName() ?></h1></div>
        <div><h4><?= $mgr->getBaseClinic()->getAddress() ?></h4></div>
        <div><h6><?= $mgr->getBaseClinic()->getPhoneNo() ?></h6></div>
    </div>
    <div id="printSummaryDemograph">
        <table class="table">
            <tr><td><span class="fadedText">Patient:</span> <?= $mgr->getFullname() ?></td><td><span class="fadedText">Age/Sex:</span> <em id="age"></em></td></tr>
            <tr><td><span class="fadedText">Insurance:</span> <?= $mgr->getScheme()->getName()?></td><td><span class="fadedText">Current Admission Status:</span></td></tr>
        </table>
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
            <tr><td><strong>Date</strong></td><td><strong>Diagnosis</strong></td><td><strong>Type</strong></td><td><strong>By</strong></td></tr>
            <?php foreach(array_reverse($data['diagnoses']) as $diagnosis){//$diagnosis=new PatientDiagnosis();?>
                <tr><td><?= date($DATE_FORMAT, strtotime($diagnosis->getDate())) ?></td><td><?=$diagnosis->getDiagnosis()?></td><td><?= $diagnosis->getType()?></td><td><?= $diagnosis->getDiagnosedBy()->getFullname()?></td></tr>
            <?php }?>
        </table>
        <table class="table table-bordered">
            <tr><td colspan="3"><strong>Other Notes</strong></td></tr>
            <tr><td nowrap><strong>Date</strong></td><td><strong>Note</strong></td><td><strong>By</strong></td></tr>
            <?php foreach(array_reverse($data['notes']) as $note){//$note=new VisitNotes()?>
                <tr><td nowrap><?= date($DATE_FORMAT, strtotime($note->getDateOfEntry())) ?></td><td><?= $note->getDescription()?></td><td nowrap><?= $note->getNotedBy()->getFullname()?></td></tr>
            <?php }?>
        </table>
    </div>
    <div style="margin: 50px 20px 20px;">Next Appointment: <h4><?php echo date("l M j, Y H:i A",strtotime((new AppointmentDAO())->getPatientNextAppointment($id)->getStartTime()))?></h4></div>
</body>
</html>