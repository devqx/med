<?php
require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
$p = new Manager();
$aid_ = isset($_REQUEST['aid'])?$_REQUEST['aid']:NULL;
if (isset($_POST['p_value'])) {
    echo $p->saveVitalSign($_REQUEST['type'], $_REQUEST['pid'], $_REQUEST['p_value'], $aid_);
    exit;
}
?>
<div style="width: 500px">

    <?php
    $what = $unit = $placeholder = "";
    $type = $_GET['type'];
    if ($type == "weight") {
        $what = "Weight";
        $unit = "KiloGramme (kg)";
        $placeholder = "Example: 56.9";
    } elseif ($type == "height") {
        $what = "Height";
        $unit = "Meter (m)";
        $placeholder = "Example: 2.3";
    } elseif ($type == "pulse") {
        $what = "Pulse";
        $unit = "beats per minute";
        $placeholder = "Example: 56";
    } else if ($type == "rp") {
        $what = "Respiratory Rate";
        $unit = "breath per minute";
        $placeholder = "Example: 70";
    } else if ($type == "bp") {
        $what = "Blood Pressure";
        $unit = "mmHg";
        $placeholder = "Systolic/Diastolic (Example: 120/80)";
    } else if ($type == "temp") {
        $what = "Temperature";
        $unit = "&deg;C";
        $placeholder = "Example: 34.6";
    } else if ($type == "fundus_height") {
        $what = "Fundus Height";
        $unit = "cm";
        $placeholder = "Example: 20.6";
    } else if ($type == "glucose") {
        $what = "Glucose";
        $unit = "mg/dL";
        $placeholder = "Example: 95";
    } else if ($type == "protein") {
        $what = "Protein";
        $unit = "";
        $placeholder = "Example: 300";
    }else if ($type == "fhr") {
        $what = "Fetal Heart Rate";
        $unit = " bpm";
        $placeholder = "Example: 150";
    }else if ($type == "dilation") {
        $what = "Dilation";
        $unit = " cm";
        $placeholder = "Example: 7";
    }else if ($type == "mid-arm-circumference") {
        $what = "Mid Arm Circumference";
        $unit = " cm";
        $placeholder = "Example: 12";
    }else if ($type == "head-circumference") {
        $what = "Head Circumference";
        $unit = " cm";
        $placeholder = "Example: 40";
    }else if ($type == "length-of-arm") {
        $what = "Length of Arm";
        $unit = " cm";
        $placeholder = "Example: 10";
    }
    ?>
    <form method="post" name="form1" id="form1" action="/vitals-all-new.php?type=<?= $type ?>&pid=<?= $_GET['id'] ?><?=isset($_GET['aid'])?'&aid='.$_GET['aid']:''?>" onSubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
        <span id="message">&nbsp;</span>
        <label>Take <span style="text-decoration:underline"><?= $what; ?></span> reading for <?= $p->getPatientName($_GET['id']) ?></label>

        <div class="row-fluid">
            <label class="span8"><input type="text" name="p_value" id="p_value" placeholder="<?= $placeholder; ?>"/> </label>

            <span class="btn span4"><?= $unit ?></span>
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
        if (s == 'ok') {
            Boxy.info('Saved !');
            //refresh this tab
            <?php if(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='inpatient_profile.php'){?>
            showTabs(11);
            <?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='patient_antenatal_profile.php'){ ?>
            showTabs(7);
            <?php } elseif(basename(strstr($_SERVER['HTTP_REFERER'],"?",true))=='patient_labour_profile.php'){ ?>
            showTabs(1);
            <?php }else {?>
            showTabs(2);
            <?php }?>
            //then close this dialog,
            Boxy.get($('.close')).hideAndUnload();
        } else {
            msg = s.split(":");
            $('span#message').html(msg[1]).attr('class', 'warning-bar');
        }
    }
</script>