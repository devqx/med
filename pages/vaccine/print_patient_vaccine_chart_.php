<html>
<head><title>Patient Vaccine</title></head>

<style type="text/css">@import "/style/vaccine.css";
	@import "/style/fixed_table_rc.css";</style>
'
<script type="text/javascript" src="/js/fixed_table_rc.js"></script>


<link rel="stylesheet" href="/style/patient.profile.css">
<link rel="stylesheet" href="/style/style.css">

<body style="background-color: #ffffff">

<?php
if (!isset($_SESSION)) {
	session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientDemographDAO.php';
$pid = "0775327148";
//    $pid=$_GET['pid'];
$dao = new PatientDemographDAO();
$pat = $dao->getPatient();
?>


<div style="margin: 30px 0 0 0">
	<img class="passport" src="<?= $pat->getPassportPath() ?>" title='<?php echo $pat->getLname() . ", " . $pat->getFname() . " " . $pat->getMname(); ?>' height="100" width="100" style="float: left;margin-right: 10px;">
	<div style="float:left; margin-top:-7px; width:200px">
		<h4 class="uppercase" style="font-size: 20px;"><?php echo $pat->getLname() . ", " . $pat->getFname() . " " . $pat->getMname(); ?></h4>
		<div style="margin-top: -5px;"><?php echo $pat->getId() . " " . (!empty($pat->getLegacyId()) ? '(' . $pat->getLegacyId() . ')' : ''); ?></div>
		<div class="item-block uppercase" style="margin-top: -5px;"><span>Sex: </span><span><?= $pat->getSex() ?></span>
		</div>
		<div class="item-block uppercase" style="margin-top: -5px;">
			<span>DOB: </span><span><?= $pat->getDateOfBirth() ?></span></div>
	</div>
	<div class="uppercase" style="width: 60%; text-align: center; float:left"><h4>Patient Vaccine Record </h4>
		<span><?= $pat->getBaseClinic()->getFullName() ?></span></div>

	<!--<iframe style="display:block; width:1040px; height:100%; border:none;" src="/immunization/patient_immunization_profile.php?id=0775327148&view=immu-map&action=print"></iframe>-->

</div>

<?php


?>
</body>
</html>