<?php
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DrugCategoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DrugCategory.php';
    $category = new DrugCategory();
    if(!empty($_POST['drugcategory'])){
        $category->setName($_POST['drugcategory']);
    }else {
        exit("error:Invalid Category name");
    }

    $new_category = (new DrugCategoryDAO())->addCategory($category);
    if($new_category!==NULL){
        echo "success:Category Added";
    }else {
        echo "error:Cannot add category";
    }
}?>
<div><span class="error"></span>
	<form id="formDrugCategory" action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : CategoryAdded})">
        <label for="drugcategory">Drug Category<input type="text" name="drugcategory" id="drugcategory"></label>
        <div>
			<button class="btn" name="drugcatbtn" type="submit">Add</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
		</div>
	</form>
</div>
<script type="text/javascript">
    function CategoryAdded(s) {
        var s1=s.split(":");
        if(s1[0]==="success"){
            $('span.error').html('<span class="alert alert-info">'+s1[1]+'</span>');
            $("#formDrugCategory").get(0).reset();
//            Boxy.get(this).hideAndUnload() //showuld i?

        }else {
            if(s1[0]==="error"){
                $('span.error').html('<span class="alert alert-error">'+s1[1]+'</span>');
            }
        }
    }
</script>
