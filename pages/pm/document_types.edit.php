<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/17
 * Time: 4:32 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AttachmentCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffRolesDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$data = (new AttachmentCategoryDAO())->get($_GET['id']);
$roles = (new StaffRolesDAO())->getRoles();
if ($_POST) {
	if(is_blank($_POST['name'])){exit('error:Name of the role?');}
	$roles = @array_filter(@$_POST['roles']);
	
	$data = (new AttachmentCategoryDAO())->get($_POST['id'])->setName($_POST['name'])->setRoles($roles)->update();
	if($data !== null){
		exit('success:Updated!');
	}
	exit('error:Failed to update record');
}
?>
<section style="width: 600px;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, formProc)">
		<label>Name <input type="text" name="name" pattern="[a-zA-Z ]+" required value="<?= $data->getName() ?>"> </label>
		<label>Roles <select multiple name="roles[]" data-placeholder="-- --">
				<option></option>
				<?php foreach ($roles as $role) { ?>
					<option value="<?= $role->getId() ?>" <?= in_array($role->getId(), $data->getRoles()) ? 'selected' : '' ?> ><?= $role->getCode() ?></option>
				<?php } ?>
			</select> </label>
		<p style="margin-top:50px"></p>
		<input type="hidden" name="id" value="<?= $data->getId() ?>">
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var formProc = {
		onStart: function () {
			$(document).trigger('ajaxSend');
		}, onComplete: function (s) {
			$(document).trigger('ajaxStop');
			var data = s.split(':');
			if (data[0]==='error'){
				Boxy.warn(data[1]);
			} else if(data[0]==='success'){
				Boxy.get($('.close')).hideAndUnload();
				loadDocTypes();
			}
		}
	};
</script>
