<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/OphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientOphthalmologyDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyResultData.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientOphthalmology.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/OphthalmologyResult.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);

$lab = (new OphthalmologyDAO())->get($_REQUEST['testType'], TRUE);
if ($_POST) {
    $pl = (new PatientOphthalmologyDAO())->get($_POST['id']);
    if (empty($_POST['test_value']) || empty($_POST['testnote'])) {
//        exit("error:Test Value AND Note is required");
    }
    $pl->setNotes($_POST['testnote']);
    $pl->setTestDate(date("Y-m-d H:i:s", time()));
    $pl->setPerformedBy($this_user);
    
    $result = new OphthalmologyResult();
    $result->setOphthalmologyTemplate($lab->getTemplate());
    $result->setPatientOphthalmology(new PatientOphthalmology($_POST['id']));
    $data = [];
    foreach ($lab->getTemplate()->getData() as $idx=>$temp) {
        $datum = new OphthalmologyResultData();
        $datum->setValue($_POST['temp'][$temp->getId()]);
        $datum->setOphthalmologyTemplateData(new OphthalmologyTemplateData($temp->getId()));
        $data[] = $datum;
    }
    $result->setData($data);
    $pl->setOphthalmologyResult($result);

    $data = (new PatientOphthalmologyDAO())->saveResult($pl);
    if ($data !== NULL) {
        exit("ok");
    }
    exit("Failed to save result");
}
$pl = (new PatientOphthalmologyDAO())->get($_GET['testId']);
?>
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
          onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
        <!--<label>
            Preferred Specimens:
            <select disabled="disabled" multiple="multiple">
                <?php
                /*$epecimens = (new OphthalmologySpecimenDAO())->getSpecimens();
                foreach ($epecimens as $s) {
                    echo '<option value="' . $s->getId() . '" ';
                    foreach ($pl->getOphthalmologyGroup()->getPreferredSpecimens() as $s_) {
                        echo ($s->getId() == $s_->getId() ? ' selected="selected"' : "");
                    }
                    echo '>' . $s->getName() . '</option>';
                }*/
                ?>
            </select>
        </label>
        <label>
            Received Specimens:
            <select disabled="disabled" multiple="multiple"><?php
                /*foreach ($epecimens as $s) {
                    echo '<option value="' . $s->getId() . '" ';
                    foreach ($pl->getSpecimens() as $s_) {
                        echo ($s->getId() == $s_->getId() ? ' selected="selected"' : "");
                    }
                    echo '>' . $s->getName() . '</option>';
                }*/
                ?></select>
        </label>-->
        <?php foreach ($lab->getTemplate()->getData() as $temp) { //$temp = new LabTemplateData();?>
            <label> <?= $temp->getLabel() ?>
                <?php if($temp->getReference() != ""){?><span class="pull-right fadedText">Reference (<?=$temp->getReference()?>)</span><?php }?>
                <!--<input type="text" name="<?= strtr ($temp->getLabel(), array (' ' => '_')) ?>" value="" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $temp ?>" >-->
                <input type="text" name="temp[<?= $temp->getId()?>]" value="" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?= $temp ?>" >
            </label>
        <?php } ?>
        <label>
            <textarea name="testnote" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Notes"></textarea></label>

        <div class="btn-block">
            <input type="hidden" name="id" value="<?= $_GET['testId'] ?>">
            <input type="hidden" name="testType" value="<?= $_GET['testType'] ?>">
            <button class="btn" type="submit" name="button" id="button">Save &raquo;</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel &raquo;</button>
        </div>
    </form>
</div>
<script type="text/javascript">
    function start() {
    }
    function done(s) {
        if (s == 'ok') {
            Boxy.info("Result Saved", function () {
                Boxy.get($('.close')).hideAndUnload();
            });
        } else {
            Boxy.alert(s);
        }
    }
</script>