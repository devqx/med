<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 10/17/14
 * Time: 2:07 PM
 */
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DentistryCategory.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DentistryCategoryDAO.php';
    $newCategory = new DentistryCategory();
    if(!empty($_POST['name'])){
        $newCategory->setName($_POST['name']);
    } else {
        exit("error:Name of category is required");
    }
    $cat = (new DentistryCategoryDAO())->add($newCategory);
    if($cat !== NULL){
        exit("success:Category ".$cat->getName()." added");
    }
    exit("error:Failed to add scan category");
}?>
<section>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart: saving, onComplete: saved})">
        <label>Category Name
            <input type="text" name="name"></label>

        <div class="btn-block">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</section>
<script type="text/javascript">
    function saving(){}
    function saved(s){
        var data = s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1]);
        }else if (data[0]==="success"){
            Boxy.info(data[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }
    }
</script>
