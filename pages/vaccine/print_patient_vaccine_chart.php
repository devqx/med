<html>
<head><title>Patient Vaccine</title>
    <script type="text/javascript" src="/js/jquery-2.0.3.min.js"></script>
    <script type="text/javascript" src="/js/fixed_table_rc.js"></script>
    <link rel="stylesheet" href="/style/vaccine.css" media="all">
    <link rel="stylesheet" href="/style/fixed_table_rc.css" media="all">
    <link rel="stylesheet" href="/style/patient.profile.css" media="all">
    <link rel="stylesheet" href="/style/bootstrap.css" media="all">
    <style type="text/css" media="all">
        @import url('/style/google-font.css');
        @import url('/style/printer.margins.css');
        body {
            margin: 0;
            font-family: "Open Sans", Calibri, Candara, Arial, sans-serif;
            font-size: 14px;
            line-height: 20px;
            color: #555;
            background-color: #FFF;
        }
        .clearfix {
            *zoom: 1;
        }

        .clearfix:before, .clearfix:after {
            display: table;
            content: "";
            line-height: 0;
        }
        .legend {
            padding: 1px 15px;
            font-size: 90%;
        }
        .desc {
            padding-left: 10px;
            font-size: 90%;
        }
    </style>
</head>
<body>

<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$pid = $_GET['id'];
$dao = new PatientDemographDAO();
$pat = $dao->getPatient($pid, FALSE);
$patient = new Manager();
$data = $patient->getPatientVaccineMap($pid);
?>

<div style="margin: 30px 0 0 0">
    <img class="passport" src="<?= $pat->getPassportPath() ?>"
         title='<?= $pat->getLname() . ", " . $pat->getFname() . " " . $pat->getMname(); ?>' height="100"
         width="100" style="float: left;margin-right: 10px;">

    <div style="float:left; margin-top:-7px; width:220px">
        <h4 class="uppercase"
            style="font-size: 20px;"><?= $pat->getFullname() ?></h4>

        <div><?= $pat->getId() . " " . (!empty($pat->getLegacyId() ? '(' . $pat->getLegacyId() . ')' : '')); ?></div>
        <div class="item-block uppercase"><span>Sex: </span><span><?= $pat->getSex() ?></span></div>
        <div class="item-block uppercase"><span>DOB: </span><span><?= $pat->getDateOfBirth() ?></span></div>
    </div>
    <div class="uppercase" style="width: 60%; text-align: center; float:left"><h4
            style="margin: 1px auto; font-size: 20px">Patient Vaccine Record </h4>
        <div><?= $pat->getBaseClinic()->getName() ?></div>
        <div><?= $pat->getBaseClinic()->getAddress() ?></div>
        <div><?= $pat->getBaseClinic()->getPhoneNo() ?></div>
    </div>
</div>
<div class="pull-right"><span>Generated On: <?= date("Y-m-d") ?></span></div>
<br>
<div style="display: block; margin-top: 130px;/*float: left;position: absolute;*/font-size: 95%"><?= $data ?></div>
<div><table class="table table-bordered table-striped pull-right" style="width:500px !important;">
    <thead><tr><th colspan="2"><em class="desc">LEGEND</em></th></tr></thead>
    <tr><td><div class="legend vaccine-block green done internal">&nbsp;</div> </td><td class="desc">shot taken in this hospital</td></tr>
    <tr><td><div class="legend vaccine-block green done">&nbsp;</div></td><td class="desc">shot taken elsewhere</td></tr>
    <tr><td><div class="legend vaccine-block yellow">&nbsp;</div></td><td class="desc">vaccine due</td></tr>
    <tr><td><div class="legend vaccine-block red">&nbsp;</div></td><td class="desc">vaccine overdue</td></tr>
    <tr><td><div class="legend vaccine-block gray">&nbsp;</div></td><td class="desc">vaccine not yet due</td></tr>
</table></div>
</body>
</html>
