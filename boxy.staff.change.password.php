<?php
if (!isset($_SESSION)) {
	session_start();
}
if ($_POST) {
	//	sleep(2);
	//check if this is the user
	require_once "Connections/MyDBConnector.php";
	$pdo = (new MyDBConnector())->getPDO();
	$sql = "SELECT * FROM staff_directory WHERE staffId = '" . $_SESSION['staffID'] . "'";
	$stmt = $pdo->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$chk = $stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_NAMED, PDO::FETCH_ORI_NEXT);
	
	if ($_GET['id'] == $_SESSION['staffID'] && $stmt->rowCount() == 1 && password_verify($_POST['pswd'], $row['pswd'])) { // only i can change my password
		//continue to validate
		if (trim($_POST['pswd1']) != "" && trim($_POST['pswd2']) != "") {
			if (trim($_POST['pswd1']) == trim($_POST['pswd2'])) {
				//change password
				require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/class.staff.php';
				$staff = new StaffManager();
				if ($staff->changePassword($_SESSION['staffID'], trim($_POST['pswd1'])) == "success") {
					echo "ok:done";
				} else {
					echo "Error: Can not change password; contact administrator";
				}
			} else {
				echo "Error: Passwords do not match";
			}
		} else {
			echo "Enter new password and confirm it";
		}
	} else {
		echo "Access Denied!";
	}
	exit;
}
?>
<div>
	<script type="text/javascript">
		function start() {
			$('#msg').html('<img src="/img/loading.gif"/> <em>please wait ...</em>');
		}
		function done(s) {
			var status_ = s.split(":");
			if (status_[0] === 'ok') {
				$('#msg').html('<span class="alert alert-info">Password Changed</span>');
				setTimeout(function () {
					//we have to log you out and come back to this page
					$.get('/logout.php', function () {
						location.reload()
					});
					Boxy.get($('.close')).hideAndUnload();
				}, 1500);
			}
			else {
				$('#msg').html('<span class="alert-error">' + s + '</span>');
			}
		}
	</script>
	<span id="msg"></span>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">

		<label>Enter Old Password:<input name="pswd" type="password" autofocus="true"></label>
		<label>Enter New Password:<input name="pswd1" type="password"></label>
		<label>Confirm the Password:<input name="pswd2" type="password"></label>
		<div class="btn-block">
			<button type="submit" class="btn">Change Password &raquo;</button>
			<button type="button" class="btn-link" onclick="$('.close').click()">Cancel</button>
		</div>
	</form>

</div>
