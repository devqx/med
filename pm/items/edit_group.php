<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/23/17
 * Time: 4:15 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGroupDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$g = (new ItemGroupDAO())->getItemGroup($_GET['id']);

if ($_POST) {
	require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/ItemGroup.php';
	if(!empty($_POST['description'])){
	    $des_ = $_POST['description'];
    }

	if (empty($_POST['name'])) {
		exit("error:Name is required");
	}
	$newGrp= $g->setName($_POST['name'])->setDescription($des_)->update();

	if ($newGrp !== null) {
		exit("ok:Group " . $newGrp->getName() . " updated successfully!");
	} else {
		exit("error:Failed to Update group");
	}
}
?>
<section>
	<div>
		<form  name="editGroupForm" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
			<label>Name
				<input type="text" name="name" value="<?= $g->getName() ?>">
			</label>
            <label>Description <textarea name="description" ><?= $g->getDescription() ?></textarea> </label>

            <div class="btn-block" style="margin-top: 10px;">
				<input type="hidden" name="id" value="<?= $_GET['id'] ?>">
				<button type="submit" class="btn" ><i class="icon-edit"></i>
					update
				</button>
				<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">
					Close
				</button>
			</div>
		</form>
	</div>
</section>
<script type="text/javascript">

	function start() {
		$(document).trigger('ajaxSend');
	}
	function success(s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');

		if (data[0] === "ok") {
			Boxy.info(data[1], function(){
				Boxy.get($(".close")).hideAndUnload();
			});
		} else {
			Boxy.warn(data[1]);
		}
	}
</script>