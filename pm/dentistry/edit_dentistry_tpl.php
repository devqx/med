<?php
/**
 * Created by PhpStorm.
 * User: emnity
 * Date: 5/12/15
 * Time: 12:37 PM
 */
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DentistryTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DentistryCategory.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';

$template = (new DentistryTemplateDAO())->get($_GET['id']);
$categories = (new DentistryCategoryDAO())->all();

if($_POST){
    if(is_blank($_POST['title'])){
        exit("error:Title is required");
    }
    if(is_blank($_POST['imaging_tpl'])){
        exit("error:Please design your template");
    }
    if(is_blank($_POST['category_id'])){
        exit("error:Select your category_id");
    }
    $template->setCategory(new DentistryCategory($_POST['category_id']))
        ->setTitle($_POST['title'])
        ->setBodyPart($_POST['imaging_tpl']);

    $newImagingTpl = (new DentistryTemplateDAO())->update($template);
    if($newImagingTpl !== NULL){
        exit("success:Template updated");
    }
    exit("error:Save failed");
}
?>
<div style="width: 1000px;">
    <form method="post" action="<?= $_SERVER['REQUEST_URI']?>" name="editTplForm" onsubmit="return AIM.submit(this, {onStart:changeTplStart, onComplete: changeTplStop})">
        <label>Title <input type="text" name="title" id="title" placeholder="Enter your template title" value="<?= $template->getTitle()?>"></label>
        <label>Category <select name="category_id" placeholder="--select category--" id="category_id" style="width: 100%;"><option></option>
                <?php foreach($categories as $k=>$category){ ?>
                    <option value="<?= $category->getId() ?>"<?= ($category->getId()==$template->getCategory()->getId())? ' selected':'' ?>><?= $category->getName(); ?></option>
                <?php } ?>
            </select></label>
        <label style="margin-top: 18px;"><textarea id="edit_imaging_tpl" placeholder="Type / paste template text here..." name="imaging_tpl"><?= $template->getBodyPart() ?></textarea></label>
        <div class="btn-block" style="margin-top:10px;">
            <button type="submit" class="btn"><i class="icon-save"></i>Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
        </div>
    </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('#edit_imaging_tpl').summernote(SUMMERNOTE_CONFIG);
    $("#category_id").select2();
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