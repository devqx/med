<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 7/1/15
 * Time: 2:40 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalEnrollmentDAO.php';
$pid = (new AntenatalEnrollmentDAO())->get($_GET['aid'], false)->getPatient()->getId();
?>
<form method="post" action="/antenatal/ajax.close-enrollment.php?aid=<?= $_GET['aid'] ?>" onsubmit="return AIM.submit(this, {'onStart': start, 'onComplete': done});">
	<span id="_output"></span>
	<label>
		Please add a close note here
		<textarea name="reason" required="required"></textarea>
	</label>
	<label><span class="clearfix"></span></label>
	<div class="btn-block">
		<input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
		<button class="btn" type="submit">Save</button>
		<button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
</form>
<script type="text/javascript">
	function start() {
		$('#_output').html('<img src="/img/loading.gif"> Please wait...').attr('class', '')
	}

	function done(str) {
		var s = str.split(":");
		if (s[0] === "error") {
			$('#_output').html(s[1]).attr('class', 'warning-bar');
		} else if (s[0] === "ok") {
			Boxy.info(s[1], function () {
				Boxy.get($(".close")).hideAndUnload();
				location.href = "/patient_profile.php?id=<?= $pid ?>";
			});
		}
	}
</script>