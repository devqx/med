<?php require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.config.main.php';
require_once $_SERVER['DOCUMENT_ROOT'] ."/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if(!$this_user->hasRole($protect->doctor_role) &&!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy))
    exit ($protect->ACCESS_DENIED);
$MainConfig=new MainConfig();
if (isset($_POST['allergen'])){
	require $_SERVER['DOCUMENT_ROOT'].'/classes/class.patient.php';
	$assessment = new Manager();
//	echo $assessment->savePatientAllergen($_POST['pid'],$_POST['allergen'],$_POST['reaction'], $_POST['severity']);exit;
} ?>
<div style="width: 450px;">

<script type="text/javascript">
    function start() {
    }
    function done(s) {
        if (s == 'ok') {
            Boxy.info('Saved !');
//and reload this tab, 
            showTabs(4);
//then close this dialog,
            Boxy.get($('.close')).hideAndUnload();
        } else {
            Boxy.alert(s);
        }
    }
</script>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
        <label>Allergen<input type="text" name="allergen" id="allergen"></label>
        <label>Reaction <input type="text" name="reaction" id="reaction"></label>
        <label>Level of severity<select name="severity" class="wide">
                <?php foreach ($MainConfig::allergenSeverities() as $val => $sev) {
                    echo '<option value="' . $val . '">' . $sev . '</option>';
                }
                ?>
            </select></label>

        <div class="btn-block"><input type="hidden" name="pid" value="<?php echo $_GET['pid']; ?>"/>
            <button type="submit" class="btn">Save &raquo;</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>


    </form>
</div>
