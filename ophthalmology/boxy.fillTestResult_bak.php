<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientLabDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/LabSpecimenDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/PatientLabs.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);


if ($_POST) {
    $pl = (new PatientLabDAO())->getLab($_POST['id']);
    if (empty($_POST['test_value']) || empty($_POST['testnote'])) {
        exit("error:Test Value AND Note is required");
    }
    $pl->setValue($_POST['test_value']);
    $pl->setNotes($_POST['testnote']);
    $pl->setTestDate(date("Y-m-d H:i:s", time()));
    $pl->setPerformedBy($this_user);
    $data = (new PatientLabDAO())->saveResult($pl);
    if ($data !== NULL) {
        exit("ok");
    }
    exit("Failed to save result");
}
$lab = (new LabDAO())->getLab($_GET['testType'], TRUE);
$pl = (new PatientLabDAO())->getLab($_GET['testId']);
?>
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>"
          onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
        <label>
            Preferred Specimens:
            <select disabled="disabled" multiple="multiple">
                <?php
                $epecimens = (new LabSpecimenDAO())->getSpecimens();
                foreach ($epecimens as $s) {
                    echo '<option value="' . $s->getId() . '" ';
                    foreach ($pl->getLabGroup()->getPreferredSpecimens() as $s_) {
                        echo ($s->getId() == $s_->getId() ? ' selected="selected"' : "");
                    }
                    echo '>' . $s->getName() . '</option>';
                }
                ?>
            </select>
        </label>
        <label>
            Taken Specimens:
            <select disabled="disabled" multiple="multiple"><?php
                foreach ($epecimens as $s) {
                    echo '<option value="' . $s->getId() . '" ';
                    foreach ($pl->getSpecimens() as $s_) {
                        echo ($s->getId() == $s_->getId() ? ' selected="selected"' : "");
                    }
                    echo '>' . $s->getName() . '</option>';
                }
                ?></select>
        </label>
        <label>
            <textarea name="test_value" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Enter result for <?php echo $lab->getName() ?> (<?php echo $lab->getTestUnitSymbol(); ?>)"></textarea>
        </label>
        <label>
            <textarea name="testnote" style="max-width: 100%;width:100%;min-width: 100%" placeholder="Notes"></textarea></label>

        <div class="btn-block">
            <input type="hidden" name="id" value="<?= $_GET['testId'] ?>">
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