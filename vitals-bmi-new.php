<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/2/15
 * Time: 11:04 AM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$p = new Manager();
$aid = isset($_REQUEST['aid'])? $_REQUEST['aid'] : NULL;
if (isset($_POST['w_value']) && isset($_POST['h_value'])) {
    echo $p->saveBMIVitalSign($_REQUEST['type'], $_REQUEST['pid'], $_REQUEST['w_value'], $_REQUEST['h_value'], $aid );
    exit;
}
?>
<div style="width: 400px">

    <?php
    $what = array();
    $type = $_GET['type'];
    ?>
    <form method="post" name="form1" id="form1" action="/vitals-bmi-new.php?type=<?php echo $type ?>&pid=<?php echo $_GET['id'] ?><?=isset($_GET['aid'])?'&aid='.$_GET['aid']:''?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
        <span id="message">&nbsp;</span>

        <label>Take <span style="text-decoration:underline">Weight</span> reading for <?php echo $p->getPatientName($_GET['id']) ?></label>
        <div class="input-append">
            <input name="w_value" id="p_value" placeholder="Example: 56.9" type="text">
            <button class="btn" type="button">KiloGramme (kg)</button>
        </div>
        <label>Take <span style="text-decoration:underline">Height</span> reading for <?php echo $p->getPatientName($_GET['id']) ?></label>
        <div class="input-append">
            <input name="h_value" id="p_value" placeholder="Example: 2.3" type="text">
            <button class="btn" type="button">Meter (m)</button>
        </div>

        <div class="btn-block">
            <button type="submit" class="btn">Save &raquo;</button>
            <button type="button" onclick="Boxy.get(this).hideAndUnload()" class="btn-link">Cancel</button></div>
    </form>

</div>
<script type="text/javascript">
    function start() {

    }
    function done(s) {
        if (s.split(":")[0] == 'ok') {
            Boxy.info('Saved !');
            //refresh this tab
            <?php if(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='inpatient_profile.php'){?>
            showTabs(11);
            <?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='patient_antenatal_profile.php'){ ?>
            showTabs(7);
            <?php }else {?>
            showTabs(2);
            <?php }?>
            //then close this dialog,
            Boxy.get($('.close')).hideAndUnload();
        } else {
            var msg = s.split(":");
            $('span#message').html(msg[1]).attr('class', 'warning-bar');
        }
    }
</script>