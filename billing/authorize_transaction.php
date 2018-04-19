<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 1/5/16
 * Time: 4:34 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BillDAO.php';
$DAO = new BillDAO();
$transaction = $DAO->getBill($_GET['tid'], TRUE);

if ($_POST) {
	$trans = $DAO->getBill($_POST['transaction_id'], FALSE);
	$trans->setAuthCode($_POST['auth_code']);

	if ($DAO->authorize($trans)) {
		exit("success:Authorized transaction successfully");
	}
	exit("error:Failed to authorize transaction");
} ?>

<form method="post" onsubmit="return AIM.submit(this, {'onStart' : startSaving, 'onComplete' : saveComplete})"
      action="<?= $_SERVER['REQUEST_URI'] ?>">
	<div class="loader"></div>
	<div class="well">
		<table class="table">
			<tr>
				<td>TRANSACTION DESCRIPTION:</td>
				<td><?= $transaction->getDescription() ?></td>
			</tr>
			<tr>
				<td>SERVICE RECIPIENT:</td>
				<td><?= $transaction->getPatient()->getFullname() ?></td>
			</tr>
			<tr>
				<td>SERVICE RECIPIENT EMR #:</td>
				<td><?= $transaction->getPatient()->getId() ?></td>
			</tr>
			<tr>
				<td>SERVICE AMOUNT:</td>
				<td><?= $transaction->getAmount() ?></td>
			</tr>
			<tr>
				<td>REVIEW ACTION SET BY:</td>
				<td><?= $transaction->getReceiver()->getFullname() ?></td>
			</tr>
		</table>
	</div>
	<label>Authorization Code: <input autocomplete="off" type="text" name="auth_code" required></label>
	<div class="btn-block">
		<button type="submit" class="btn">Authorize</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
	</div>
	<input type="hidden" name="transaction_id" value="<?= $transaction->getId() ?>">
</form>

<script>
	function startSaving() {
		$('.loader').html('<img src="/img/loading.gif"/> please wait...');
	}
	function saveComplete(s) {
		var dat = s.split(":");
		if (dat[0] === "error") {
			$('.loader').html('<span class="warning-bar">' + dat[1] + '</span>');
		} else if (dat[0] === "success") {
			Boxy.get($('.close')).hideAndUnload();
		}
	}
</script>
