<?php
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/ImagingTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ImagingTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/ScanCategoryDAO.php';

$scanTemplates = (new ImagingTemplateDAO())->getTemplates();
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
    $newImagingTpl = (new ImagingTemplateDAO())->addTemplate($imgTpl);
    if($newImagingTpl !== NULL){
        exit("success:Imaging Template added");
    }
    exit("error:Save failed");
}
?>
<div>
    <h6>Available Imaging Templates</h6>
    <div class="three-column scans">
        <?php foreach ($scanTemplates as $tpl) { ?>
            <div class="column tag"><?=$tpl->getTitle()?> <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?=$tpl->getId()?>" class="editScanTplLink">edit</a></span></div>
        <?php }?>
    </div>
    <h6>Add New Imaging Templates</h6>
    <form method="post" name="newImagingTplForm" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:addTplStart, onComplete: addTplStop})">
        <div class="row-fluid">
            <label class="span6">Title <input type="text" name="title" id="title" placeholder="Enter your imaging template title"></label>
            <label class="span6">Category <select name="category" placeholder="--select imaging category--" id="category" style="width: 100%;"><option></option>
                    <?php foreach($categories as $k=>$category){ ?>
                        <option value="<?= $category->getId() ?>"><?= $category->getName(); ?></option>
                    <?php } ?>
                </select></label>
        </div>
        <label><textarea placeholder="type template here..." id="imaging_tpl" name="imaging_tpl"></textarea></label>

        <div class="btn-block" style="margin-top: 10px;">
            <button type="submit" class="btn"><i class="icon-save"></i>Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    $("#category").select2();
    $('textarea[name="imaging_tpl"]').summernote(SUMMERNOTE_CONFIG);
    $('a.editScanTplLink').live('click', function (e) {
        if(!e.handled){
            Boxy.load('/pm/imaging/edit_imaging_tpl.php?id='+$(this).data('id'), {title: 'Edit Imaging Template', afterHide: reloadImgTpl});
            e.handled = true;
        }
    })
});
function reloadImgTpl(){
    $.ajax({
        url: '/api/get_imaging_tpls.php',
        dataType: 'json',
        complete:function(s){
            var html = '';
            $.each(s.responseJSON, function (idx, imgTpl) {
                html += '<div class="column tag">'+imgTpl.title+' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="'+imgTpl.id+'" class="editScanTplLink">edit</a></span></div>';
            });
            $('div.three-column.scans').html(html);
        },
        error: function () {

        }
    });
}

function addTplStart(){}
function addTplStop(s){
    var answer = s.split(":");
    if(answer[0]==="error"){
        Boxy.alert(answer[1])
    } else {
        reloadImgTpl();
        $('form[method="post"][name="newImagingTplForm"]').get(0).reset();
        $("#category").select2('val', '');
        $("#imaging_tpl").val('');
        Boxy.info(answer[1]);
    }
}
</script>