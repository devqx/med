<?php
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ItemCategoryDAO.php';
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemCategory.php';
	$category = new ItemCategory();
	if(!empty($_POST['item_category'])){
		$category->setName($_POST['item_category']);
	}else {
		exit("error:Invalid Category name");
	}

	$new_category = (new ItemCategoryDAO())->add($category);
	if($new_category!==NULL){
		echo "success:Category Added";
	}else {
		echo "error:Cannot add category";
	}
}?>
<div><span class="error"></span>
	<form id="formItemCategory" action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {onComplete : CategoryAdded})">
		<label for="item_category">Item Category<input type="text" name="item_category" id="item_category"></label>
		<div>
			<button class="btn"  type="submit">Add</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">

	function CategoryAdded(s) {
		var s1=s.split(":");
		if(s1[0]==="success"){
            Boxy.info(s1[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
		}else {
			if(s1[0]==="error"){
				Boxy.alert("Unable to save category");
			}
		}
	}
</script>
