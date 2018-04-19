<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ExamRoomDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
if ($_POST) {
	if (is_blank($_POST['roomName'])) {
		exit('error:Blank data');
	}
	$room = (new ExamRoomDAO())->getExamRoom($_POST['id'])->setName($_POST['roomName'])->update();
	if ($room !== null) {
		exit('success:Room updated');
	}
	exit('error:Failed to update room');
}
?>
<div>

	<div></div>
	<hr>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" onsubmit="return AIM.submit(this, {'onStart':start, 'onComplete':done});">
		<h6>Please modify the below name and click on update</h6>
		<?php
		$room = (new ExamRoomDAO())->getExamRoom($_GET['id']);
		?>
		<label><input type="hidden" name="id" value="<?= $_GET['id'] ?>">
			<input type="text" value="<?= $room->getName(); ?>" name="roomName"/></label>
		<div class="btn-block">
			<button class="btn" style="display: inline" type="submit">Update &raquo;</button>
			<button type="button" class="cancelBtn btn-link" onclick="Boxy.get(this).hideAndUnload();">Cancel</button>
		</div>
		<div id="wait_"></div>
	</form>
</div>
<script type="text/javascript">
	function start() {
		$('#wait_').html('<em>Please wait</em>');
	}
	function done(s) {
		var data = s.split(':');
		if (data[0] === 'success') {
			$('#wait_').html('<span class="alert alert-info">' + data[1] + '</span>');
			loadCfgXamRoom();
			setTimeout(function () {
				Boxy.get($('.close')).hideAndUnload();
			}, 1000);
		} else if (data[0] === 'error') {
			$('#wait_').html('<span class="alert-error">' + data[1] + '</span>');
		}
	}
</script>