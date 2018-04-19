<?php
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureCategoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ProcedureCategory.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

    $cat = new ProcedureCategory();
    if(!is_blank($_POST['cat_name'])){
        $cat->setName($_POST['cat_name']);
    } else {
        exit('error:Category name is required');
    }

    $newCat = (new ProcedureCategoryDAO())->add($cat);
    if($newCat !== NULL){
        exit("success:Added Category");
    }
    exit('error:Failed to add category');
}?>

<form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete: ended})">
    <p class="well">New Procedure Category</p>
    <label>Name <input type="text" name="cat_name"> </label>
    <div class="btn-block">
        <button class="btn" type="submit">Save</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </div>
</form>
<script type="text/javascript">
    function ended(s){
        console.log(s);
        var data = s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1]);
        } else {
            reloadProCats();
            Boxy.get($(".close")).hideAndUnload();
        }
    }
</script>