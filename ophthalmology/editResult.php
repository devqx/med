<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyResultDataDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyResultData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientOphthalmology.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyResult.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

if ($_POST) {
    $pl = (new PatientOphthalmologyDAO())->get($_POST['id']);
    if (is_blank($_POST['testnote'])) {
        exit("Enter Ophthalmology result notes");
    }
    $pl->setNotes($_POST['testnote']);
    $pl->setTestDate(date("Y-m-d H:i:s", time()));
    $pl->setPerformedBy($this_user);

    $result = $pl->getOphthalmologyResult();

    $result->setOphthalmologyTemplate($pl->getOphthalmologyResult()->getOphthalmologyTemplate());
    $result->setPatientOphthalmology($pl);
    $data = [];
    foreach ($_POST['lrData'] as $i=>$lrDataValue) {
        list($resultId, $templateDataId) = explode(", ", $i);
        $datum = (new OphthalmologyResultDataDAO())->getResultDatum($resultId, $templateDataId, FALSE);
        $datum->setValue($lrDataValue);

        $data[] = $datum;
    }
    $result->setData($data);
    $pl->setOphthalmologyResult($result);

    $data = (new PatientOphthalmologyDAO())->updateResult($pl);
    if ($data !== NULL) {
        exit("ok");
    }
    exit("Failed to save result");
}

$pl = (new PatientOphthalmologyDAO())->get($_GET['plId']);
$lab = $pl->getOphthalmology();
?>
<div style="min-width: 500px">
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
          onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">

        <?php foreach ($pl->getOphthalmologyResult()->getData() as $lrData) {//$lrData = new LabResultData();?>
            <?php foreach ($lab->getTemplate()->getData() as $temp) {
                if($lrData->getOphthalmologyTemplateData()->getId()== $temp->getId()){?>
                <label> <?= $temp->getLabel() ?>
                    <input type="text" name="lrData[<?= $lrData->getOphthalmologyResult()->getId()?>, <?= $temp->getId()?>]" value="<?=$lrData->getValue() ?>" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $temp ?>" >
                </label>
            <?php } }
        } ?>

<hr>

        <label>
            <textarea name="testnote" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Notes"><?= $pl->getNotes() ?></textarea></label>

        <div class="btn-block">
            <input type="hidden" name="id" value="<?= $_GET['plId'] ?>">
            <button class="btn" type="submit" name="button" id="button">Update Changes</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    function start() {
    }
    function done(s) {
        if (s == 'ok') {
            Boxy.info("Result Updated", function () {
                Boxy.get($('.close')).hideAndUnload();
            });
        } else {
            Boxy.alert(s);
        }
    }
</script>