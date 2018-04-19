<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 12:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/ImagingTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ImagingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ScanCategoryDAO.php';

$template = (new ImagingTemplateDAO())->getTemplate($_GET['id']);
$categories = (new ScanCategoryDAO())->getCategories();

if($_POST){
    $imgTpl = new ImagingTemplate();
    if($_POST['title'] == ''){
        exit("error:Title is required");
    }
    if(trim($_POST['imaging_tpl'])==''){
        exit("error:Please design your template");
    }
    if($_POST['category']==''){
        exit("error:Select your category");
    }

    $imgTpl->setCategory($_POST['category']);
    $imgTpl->setTitle($_POST['title']);
    $imgTpl->setBodyPart($_POST['imaging_tpl']);
    $imgTpl->setId($template->getId());

    $newImagingTpl = (new ImagingTemplateDAO())->editTemplate($imgTpl);
    if($newImagingTpl !== NULL){
        exit("success:Imaging Template updated");
    }
    exit("error:Save failed");
}
?>
<div>
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" name="editTplForm" onsubmit="return AIM.submit(this, {onStart:changeTplStart, onComplete: changeTplStop})">
        <label>Title <input type="text" name="title" id="title" placeholder="Enter your imaging template title" value="<?= $template->getTitle()?>"></label>
        <label>Category <select name="category" placeholder="--select imaging category--" id="category" style="width: 100%;"><option></option>
                <?php foreach($categories as $k=>$category){ ?>
                    <option value="<?= $category->getId() ?>"<?= ($category->getId()==$template->getCategory()->getId())? ' selected':'' ?>><?= $category->getName(); ?></option>
                <?php } ?>
            </select></label>
        <label style="margin-top: 18px;"><textarea id="edit_imaging_tpl" placeholder="type template here..." name="imaging_tpl"><?= $template->getBodyPart() ?></textarea></label>
        <div class="btn-block" style="margin-top:10px;">
            <button type="submit" class="btn"><i class="icon-save"></i>Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
        </div>
    </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#edit_imaging_tpl').summernote(SUMMERNOTE_CONFIG);
    $("#category").select2();
});
function changeTplStart(){}
function changeTplStop(s){
    var answer = s.split(":");
    if(answer[0]==="error"){
        Boxy.alert(answer[1])
    } else {
        Boxy.info(answer[1], function(){
            Boxy.get(".close").hideAndUnload();
        });
    }
}
</script>