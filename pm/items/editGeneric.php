<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/30/17
 * Time: 3:06 PM
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ItemGenericDAO.php';
$gen = (new ItemGenericDAO())->get($_GET['id']);
$cat = (new ItemCategoryDAO())->getCategories();
if($_POST){
	require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemGeneric.php';
	if(empty($_POST['name'])){
		exit("error: name required");
	}
	$store = (new ItemGeneric())->setId($gen->getId())->setName($_POST['name'])->setCategory((new ItemCategoryDAO())->get($_POST['category_id']))->setDescription($_POST['description'])->update();
	if($store !== NULL){
		echo "success:Generic Edited successfully";
	}else {
		echo "error:Cannot Edit Generic";
	}
}

?>
<div><span class="error"></span>
	<form id="formItem" action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
		<label for="item_generic">Name<input type="text" name="name" id="item_generic" value="<?= $gen->getName() ?>"></label>
        <label>Category
            <span class="pull-right"><a href="javascript:;" id="addCatLink">add Category</a></span>
            <select class="category_id" name="category_id" placeholder="select category">
                <option></option>
                <?php foreach ($cat as $c){ ?>
                    <option value="<?= $c ? $c->getId() : '' ?>"<?= $gen->getCategory() && $c->getId()  == $gen->getCategory()->getId() ? 'selected="selected"' : '' ?>><?= $c->getName() ?></option>
              <?php  }
                ?>
            </select>
        </label>
        <label>Description <textarea name="description"><?= $gen->getDescription() !== "NULL" ? $gen->getDescription() : ""  ?></textarea> </label>
		<div>
			<button class="btn" name="itemCatBtn" type="submit">Update</button>
			<button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">

    function success(s) {
		var s1=s.split(":");
		if(s1[0]==="success"){
			$('span.error').html('<span class="alert alert-info">'+s1[1]+'</span>');
			$("#formItem").get(0).reset();

		}else {
			if(s1[0]==="error"){
				$('span.error').html('<span class="alert alert-error">'+s1[1]+'</span>');
			}
		}
	}


</script>