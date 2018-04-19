<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/23/15
 * Time: 11:36 AM
 */

require $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
require $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$type = (new RoomTypeDAO())->getRoomType($_REQUEST['id'], true);

if ($_POST) {
	//$type = (new RoomTypeDAO())->getRoomType($_POST['id'], TRUE);
	if (!is_blank($_POST['name'])) {
		$type->setName($_POST['name']);
	} else {
		exit("error:Room Type name is required");
	}
	if (!is_blank($_POST['cost'])) {
		$type->setDefaultPrice(parseNumber($_POST['cost']));
	} else {
		exit("error:Default price is required");
	}
	
	$update = (new RoomTypeDAO())->updateRoomType($type);
	
	if ($update !== null) {
		exit("success:Update passed");
	}
	exit("error:Update failed");
	
}

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
	<input type="hidden" name="id" value="<?= $type->getId() ?>">
	<label>New Room Category
		<input type="text" name="name" placeholder="e.g. Executive" value="<?= $type->getName() ?>"/></label>

	<label>Amount Chargeable (per day price)
		<input type="number" step="0.10" min="0" name="cost" value="<?= $type->getDefaultPrice() ?>"></label>


	<div class="btn-block">
		<button class="btn" type="submit" name="addType" value="true">Save</button>
		<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">
			Cancel
		</button>
		<div id="mgniu__"></div>
	</div>
</form>

<script type="text/javascript">
	function start_() {
		$('#mgniu__').html('<img src="/img/loading.gif">').removeClass('warning-bar');
	}
	function done_(data) {
		ret = data.split(":");

		if (ret[0] === "error") {
			$('#mgniu__').html(ret[1]).addClass('warning-bar');
		} else if (ret[0] === "success") {
			Boxy.get($(".close")).hideAndUnload();
			showTabs(1);
		}

	}
</script>