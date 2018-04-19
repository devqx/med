<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/13/16
 * Time: 3:53 PM
 */
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';
	require_once $_SERVER['DOCUMENT_ROOT']. '/classes/PackageCategory.php';
	
	if(is_blank($_POST['category_name'])){
		exit('error:Category name is required');
	} else if( (new PackageCategory())->setName($_POST['category_name'])->add() !== null){
		exit('success:Category added');
	}
	exit('error:Failed to add category');
}
?>
<section style="width: ;">
	<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onStart: _28734, onComplete: __9354mnsdf })">
		<label>Category Name <input type="text" name="category_name"></label>
		<p></p>
		<p></p>
		<div class="btn-block">
			<button type="submit" class="btn">Save</button>
			<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
		</div>
	</form>
</section>
<script type="text/javascript">
	var _28734 = function () {
		$(document).trigger('ajaxSend');
	};
	
	var __9354mnsdf = function (s) {
		$(document).trigger('ajaxStop');
		var data = s.split(':');
		if(data[0]=='error'){
			Boxy.warn(data[1]);
		} else if(data[0]=='success'){
			Boxy.get($('.close')).hideAndUnload();
		}
	}
</script>
