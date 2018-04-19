<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 12/10/15
 * Time: 4:48 PM
 */

if (!isset($_SESSION)) {
	session_start();
}
if ($_POST) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
	require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/AntenatalPackages.php";
	require_once $_SERVER ['DOCUMENT_ROOT'] . "/classes/DAOs/AntenatalPackagesDAO.php";
	
	$package = new AntenatalPackages();
	if (!is_blank($_POST['packagename'])) {
		$package->setName($_POST['packagename']);
	} else {
		exit("error:Package name is required");
	}
	if (!is_blank($_POST['amount'])) {
		$package->setAmount(parseNumber($_POST['amount']));
	} else {
		exit("error:Amount is required");
	}
	$newCoy = (new AntenatalPackagesDAO())->add($package);
	
	if ($newCoy !== null) {
		exit("success: Antenatal package added");
	}
	exit("error:Failed to create package");
}

?>

<div id="addpackage">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
		<label>
			<span id="msg"></span>
		</label>
		<label>
			Package Name: <span class="required-text">*</span>
			<input name="packagename" type="text">
		</label>
		<label>
			Amount: <span class="required-text">*</span>
			<input name="amount" type="number" min="0">
		</label>

		<div class="btn-block">
			<button class="btn" type="submit" name="update">Create
				Package &raquo;</button>
			<button class="btn-link cancelBtn" type="button" onclick="Boxy.get(this).hide()">
				Cancel
			</button>
		</div>
	</form>
</div>
<script type="text/javascript">
	function start() {
		$('#msg').html('<img src="/img/loading.gif"/> <em>please wait ...</em>');
	}
	function done(s) {
		var status_ = s.split(":");
		if (status_[0] === 'success') {
			$('#msg').html('<span class="alert alert-info">' + status_[1] + '</span>');
			showTabs(1);
			setTimeout(function () {
				Boxy.get($('.cancelBtn')).hideAndUnload();
			}, 500);
		} else {
			$('#msg').html('<span class="alert alert-error">' + status_[1] + '</span>');
		}
	}
</script>