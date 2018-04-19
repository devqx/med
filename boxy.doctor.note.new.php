<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/protect.php";
$protect = new Protect();
$this_user = (new StaffDirectoryDAO())->getStaff($_SESSION['staffID']);
if (!$this_user->hasRole($protect->doctor_role)) exit ($protect->ACCESS_DENIED);
if (isset($_POST['note'])) {
	require $_SERVER['DOCUMENT_ROOT'] . '/classes/class.patient.php';
	$pat = new Manager();
	echo $pat->savePatientVisitNote($_POST['pid'], $_POST['note'], "doc");
	exit;
}

?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
	<div class="well" id="console"></div>
	<label><textarea placeholder="type here" required="required" name="note" autofocus="autofocus"></textarea></label>
	<input type="hidden" name="pid" value="<?= $_GET['pid'] ?>">
	<div class="btn-block">
		<button type="submit" class="btn" name="save">Save</button>
		<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>

<script>
	function start() {
		$('.btn-block button').attr('disabled', 'disabled');
		$("#console").html('<img src="/img/ajax-loader.gif"> please wait . . .  ')
	}
	function done(s) {
		$('.btn-block button').each(function () {
			$(this).removeAttr("disabled");
		});
		$("#console").removeClass('error');
		var returned = s.split(":");
		if (s === 'ok') {
			showTabs(1);
			Boxy.get($('.close')).hideAndUnload()
		} else {
			$("#console").html(returned[1]).addClass('error');
		}
	}


</script>