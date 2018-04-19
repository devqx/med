<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/Connections/MyDBConnector.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/NotificationOptions.php';
$pid = ($_GET['pid']);
$not = new NotificationOptions();
$notices = $not->getSubscribedChannels($pid);
$channels = $not->getAllChannels();

if (!isset($_SESSION)) {
	session_start();
}


?>
<script type="text/javascript">
	var pid = "<?php echo $pid; ?>";
	function start() {
	}

	function getData() {
		var checked = $('input[type="checkbox"]:checked');
		var data = "pid=<?php echo $pid ?>&counter=" + checked.length;
		checked.each(function (index) {
			data += "&ch_" + index + "=" + $(this).val();
		});
		return data;
	}
	function saveChanges() {
		$.ajax({
			url: '/ajax.saveNotifications.php',
			type: 'POST',
			data: getData() + "&checkEmail=" + $('#ch_2').is(':checked'),
			success: function (s) {
				if (s.indexOf("Saved") !== -1) {
					Boxy.info(s);
				} else {//if(s.indexOf("Please update your email"))
					Boxy.confirm("Sorry, you need to update you email in you profile first. <br><center>Will you like do that now?</center>", function () {
						if (location.href.indexOf("boxy.manage_notifications.php") > -1) {
							window.location.replace("/patient/inner/updateEmail.php");
						} else {
							window.location.replace("/edit_patient_profile.php?id=" + pid);
						}
					});
				}
			}
		});
	}
	function closeBoxy(a) {
	}
</script>
<div style="float:left"><strong>Available Notification Channels:</strong></div><br/><br/>
<div id="cnt0" class="info_bar">Subscribe to one or more notification channels:<br></div>
<form action="." method="post">
	<?php for ($i = 0; $i < count($channels); $i++) { ?>
		<div>
			<label><input type="checkbox" <?php if ($not->isSubscribed($notices, $channels[$i]->getId()))
					echo "checked" ?> id="ch_<?php echo($i + 1) ?>" name="ch_<?php echo($i + 1) ?>" value="<?php echo $channels[$i]->getId() ?>"/> <?php echo $channels[$i]->getDescription() . " (" . $channels[$i]->getName() . ")" ?>
			</label></div>
	
	<?php } ?>
	<div class="btn-block"><br>
		<button class="btn" type="button" onclick="saveChanges()">Save &raquo;</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Close &raquo;</button>
	</div>
</form>
