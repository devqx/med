<?php
require_once $_SERVER['DOCUMENT_ROOT'] ."/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if(!$this_user->hasRole($protect->doctor_role) &&!$this_user->hasRole($protect->nurse) && !$this_user->hasRole($protect->pharmacy))
    exit ($protect->ACCESS_DENIED);
if (isset($_POST['condition'])){
	require $_SERVER ['DOCUMENT_ROOT'] . '/classes/class.patient.php';
	$pManager = new Manager();
	echo $pManager->savePatientPreCondition($_POST['pid'], $_POST['condition'], $_POST['diag_date'],$_POST['severity'], $_POST['therapy'], $_POST['response'], $_POST['therapy_start_date']);
	exit;
} ?>
<div>
<?php
require $_SERVER ['DOCUMENT_ROOT'] . '/Connections/dbconnection.php';
mysql_select_db($database_dbconnection, $dbconnection);
$pid = mysql_real_escape_string($_GET['id']);
?> 
<script type="text/javascript">
    function start() {
        var condition = $('select[name="condition"]').val();//required
        var diag_date = $('input[name="diag_date"]').val();//required
        var severity = $('select[name="severity"]').val();//required
        var therapy = $('select[name="therapy"]').val();//not required
        var therapy_start_date = $('input[name="therapy_start_date"]').val();//not required
        var response = $('select[name="response"]').val();//not required

        var re = /\d{4}-\d{2}-\d{2}/;
        if (condition == "") {
            Boxy.alert("Condition is required");
            return false;
        }
        if (!re.test(diag_date)) {
            Boxy.alert("Invalid Diagnosis Date");
            return false;
        }
        if (severity == "") {
            Boxy.alert("Select Severity");
            return false;
        }
        if (therapy_start_date != "") {
            if (!re.test(therapy_start_date)) {
                Boxy.alert("Invalid Therapy start Date");
                return false;
            }
        }
        return true;
    }
    function done(s) {
        if (s == 'ok') {
            Boxy.info('Saved !');
//and reload this tab, 
            showTabs(5);
//then close this dialog,
            Boxy.get($('.close')).hideAndUnload();
        } else {
            Boxy.alert(s);
        }
    }
    $(document).ready(function () {
        $('#diag_date').datetimepicker({timepicker: false, format: 'Y-m-d'});
        $('#therapy_start_date').datetimepicker({timepicker: false, format: 'Y-m-d'});
        //    $('#therapy_start_date').datetimepicker({timepicker:false, format:'m/d/Y'});
    })
</script>
    <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
          onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
        <label>Condition:<select name="condition" data-placeholder="Condition">
                <option value=""></option>
                <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.config.main.php';
                $MainConfig = new MainConfig();
                foreach (MainConfig::preConditions() as $condition) {
                    echo '<option>' . $condition . '</option>';
                }?>
            </select></label>

        <label>Date of Diagnosis: <input type="text" name="diag_date" id="diag_date" readonly="readonly"
                                         placeholder="Click/touch to select date"></label>
        <label>Severity:<select name="severity">

                <?php foreach ($MainConfig::listSeverity() as $val => $display) {
                    echo '<option value="' . $val . '">' . $display . '</option>';
                }?>
            </select></label>

        <label>Therapy <select name="therapy" class="wide" data-placeholder="Therapy">
                <option value=""></option>
                <?php foreach (MainConfig::listTherapy() as $val => $display) {
                    echo '<option value="' . $val . '">' . $display . '</option>';
                }?>
            </select></label>

        <label>Therapy Start Date<input type="text" name="therapy_start_date" id="therapy_start_date"
                                        readonly="readonly" placeholder="Click/touch to select date"></label>

        <label>Response to Therapy
            <select name="response" class="wide">
                <?php foreach (MainConfig::listResponses() as $val => $display) {
                    echo '<option value="' . $val . '">' . $display . '</option>';
                }?>
            </select></label>

        <div class="btn-block"><input type="hidden" name="pid" value="<?php echo $pid; ?>"/>
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="reset" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</div>
