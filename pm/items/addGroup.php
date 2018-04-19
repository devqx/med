<?php
/**
 * Created by PhpStorm.
 * User: issoftie
 * Date: 3/20/17
 * Time: 9:18 AM
 */

if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ItemGroup.php';

    if(empty($_POST['name'])){
        exit("error: name required");
    }

    $store = (new ItemGroup())->setName($_POST['name'])->setDescription($_POST['description'])->add();
    if($store !== NULL){
        echo "success: Item Group Added";
    }else {
        echo "error:Cannot add Item Group";
    }
}?>
<div>
    <form id="formItem" action="<?= $_SERVER['REQUEST_URI']?>" method="post" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : success})">
        <label for="item_group">Group Name<input type="text" name="name" id="item_group"></label>

            <label>Description <textarea name="description"></textarea> </label>

            <div>
                <button class="btn" name="itemCatBtn" type="submit">Add</button>
                <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()" >Cancel</button>
            </div>
    </form>
</div>
<script type="text/javascript">

    function success(s) {
        var s1=s.split(":");
        if(s1[0]==="success"){
            Boxy.info(s1[1], function () {
                Boxy.get($(".close")).hideAndUnload();
            });
        }else {
            if(s1[0]==="error"){
                Boxy.alert("Unable to Save Item Group");

            }
        }
    }

</script>
