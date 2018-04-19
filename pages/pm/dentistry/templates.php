<?php
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DentistryTemplate.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/classes/DentistryCategory.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/functions/utils.php';

$templates = (new DentistryTemplateDAO())->all();
$categories = (new DentistryCategoryDAO())->all();

if($_POST){
    if(is_blank($_POST['title'])){
        exit("error:Title is required");
    }if(is_blank($_POST['imaging_tpl'])){
        exit("error:Please design your template");
    }
    if(is_blank($_POST['category_id'])){
        exit("error:Select template category");
    }
    $imgTpl = (new DentistryTemplate())
        ->setCategory( new DentistryCategory($_POST['category_id']))
        ->setTitle($_POST['title'])
        ->setBodyPart($_POST['imaging_tpl']);

    $newImagingTpl = (new DentistryTemplateDAO())->add($imgTpl);
    if($newImagingTpl !== NULL){
        exit("success:Dentistry Documenting Template added");
    }
    exit("error:Save failed");
}
?>
<div>
    <h6>Available Dentistry Templates</h6>
    <div class="three-column scans">
        <?php foreach ($templates as $tpl) { ?>
            <div class="column tag"><?=$tpl->getTitle()?> <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="<?=$tpl->getId()?>" class="editDentistryTplLink">Edit</a></span></div>
        <?php }?>
    </div>
    <h6>Add New Imaging Templates</h6>
    <form method="post" name="newDentistryTplForm" action="<?= $_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:addTplStart, onComplete: addTplStop})">
        <div class="row-fluid">
            <label class="span6">Title <input type="text" name="title" id="title" placeholder="Enter your documenting template title"></label>
            <label class="span6">Category <select name="category_id" placeholder="--select template category--" id="category_id" style="width: 100%;"><option></option>
                    <?php foreach($categories as $k=>$category_id){ ?>
                        <option value="<?= $category_id->getId() ?>"><?= $category_id->getName(); ?></option>
                    <?php } ?>
                </select></label>
        </div>
        <label><textarea placeholder="Type / paste template text here..." id="imaging_tpl" name="imaging_tpl"></textarea></label>

        <div class="btn-block" style="margin-top: 10px;">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Close</button>
        </div>
    </form>
</div>
<script>
$(document).ready(function(){
    $("#category_id").select2();
    $('textarea[name="imaging_tpl"]').summernote(SUMMERNOTE_CONFIG);
    $('a.editDentistryTplLink').live('click', function (e) {
        if(!e.handled){
            Boxy.load('/pm/dentistry/edit_dentistry_tpl.php?id='+$(this).data('id'), {title: 'Edit Dentistry Template', afterHide: reloadDentistryTpl});
            e.handled = true;
        }
    })
});
function reloadDentistryTpl(){
    $.ajax({
        url: '/api/get_dentistry_tpls.php',
        dataType: 'json',
        complete:function(s){
            var html = '';
            $.each(s.responseJSON, function (idx, imgTpl) {
                html += '<div class="column tag">'+imgTpl.title+' <span class="pull-right"><i class="icon-edit"></i><a href="javascript:;" data-id="'+imgTpl.id+'" class="editDentistryTplLink">Edit</a></span></div>';
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
        reloadDentistryTpl();
        $('form[method="post"][name="newDentistryTplForm"]').get(0).reset();
        $("#category_id").select2('val', '');
        $("#imaging_tpl").code('').val('');
        Boxy.info(answer[1]);
    }
}
</script>