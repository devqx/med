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
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AntenatalPackagesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/AntenatalPackages.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$package = (new AntenatalPackagesDAO())->get($_GET['id']);

if ($_POST) {
	require_once $_SERVER ['DOCUMENT_ROOT'] . "/functions/utils.php";
	$package_ = (new AntenatalPackagesDAO())->get($_POST['id']);
	
	if (!is_blank($_POST['packagename'])) {
		$package_->setName($_POST['packagename']);
	} else {
		exit("error:Package name is required");
	}
	if (!is_blank(parseNumber($_POST['amount']))) {
		$package_->setAmount(parseNumber($_POST['amount']));
	} else {
		exit("error:Amount is required");
	}
	
	$updated = (new AntenatalPackagesDAO())->update($package_);
	if ($updated !== null) {
		exit("success: Antenatal package updated");
	}
	exit("error:Failed to update package");
}
?>

<div id="addprofile">
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>"
	      onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
		<label><span id="disp"></span></label>
		<label>Package
			Name:<input name="packagename" type="text" value="<?= $package->getName() ?>">
			<input name="id" type="hidden" value="<?= $package->getId() ?>"></label>
		<label>Amount:<input name="amount" type="number" value="<?= $package->getAmount() ?>"></label>

		<div class="btn-block">
			<button type="submit" class="btn" name="update">Update
				Package &raquo;</button>
			<button type="button" class="btn-link cancelBtn" onclick="Boxy.get(this).hideAndUnload()">
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
			$('#disp').html('<span class="alert alert-info">' + status_[1] + '</span>');
			showTabs(1);
			setTimeout(function () {
				Boxy.get($('.cancelBtn')).hideAndUnload();
			}, 500);
		}
		else {
			$('#disp').html('<span class="alert alert-error">' + status_[1] + '</span>');
		}
	}
</script>