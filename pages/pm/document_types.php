<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/24/17
 * Time: 2:16 PM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/AttachmentCategoryDAO.php';
$data = (new AttachmentCategoryDAO())->all();
?>
<section>
	<div class="pull-right">
		<a href="javascript:" name="addNewBtn" class="type_add">New</a>
	</div>
	<table class="table table-striped">
		<thead><tr><th>Category</th><th>Roles</th><th>*</th></tr></thead>
		<?php foreach($data as $datum){ /*$datum = new AttachmentCategory();*/ ?><tr><td><?= $datum->getName()?></td><td><?=count($datum->getRolesFull())>0 ? arrayIterator($datum->getRolesFull(), 'getCode') : '- -' ?></td><td><a data-id="<?=$datum->getId()?>" href="javascript:" class="editType">Edit</a></td></tr><?php }?>
	</table>
</section>
<script type="text/javascript" >
	$(document).on('click', '.editType', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/document_types.edit.php?id='+$(e.target).data('id'));
			e.handled = true;
		}
	}).on('click', '.type_add', function (e) {
		if(!e.handled){
			Boxy.load('/pages/pm/document_types.add.php');
			e.handled = true;
		}
	})
</script>

