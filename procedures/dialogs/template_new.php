<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureTemplateCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/ProcedureTemplateDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/functions/utils.php';

$templateCategories = (new ProcedureTemplateCategoryDAO())->all();

if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ProcedureTemplate.php';
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/ProcedureTemplateCategory.php';
    $new = new ProcedureTemplate();

    if(!is_blank($_POST['category_id'])){
        $new->setCategory(new ProcedureTemplateCategory($_POST['category_id']));
    } else {
        exit("error:Category is required");
    }

    if(!is_blank($_POST['content'])){
        $new->setContent($_POST['content']);
    } else {
        exit("error:Template Content Text is required");
    }

    $added = (new ProcedureTemplateDAO())->add($new);
    if($added !== NULL){
        exit("success:Template saved");
    }
    exit("error:Save failed for new template");
}
?>
<section style="width: 850px">
    <p>Save a new Procedure template
<!--        <a href="javascript:;" data-href="/consulting/template_help.php" class="pull-right" id="procedure_template_link_child"><i class="icon-question-sign"></i> help</a> -->
    </p>
    <form method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {onComplete:done})">
        <label>Category
            <span class="pull-right"><a href="javascript:;" class="newCategoryLink">add</a> </span>
            <select name="category_id" id="category_id" placeholder="Template Category/Specialty">
                <option></option>
                <?php foreach($templateCategories as $t){//$t=new ProcedureTemplateCategory();?>
                <option value="<?=$t->getId()?>"><?=$t->getName()?></option>
<?php } ?>
        </select></label>
        <label>Content <textarea name="content"></textarea></label>
        <div class="btn-block">
            <button class="btn" type="submit">Save</button>
            <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        </div>
    </form>
</section>
<script>
    $(document).ready(function(){
        $('textarea[name="content"]' ).summernote(SUMMERNOTE_CONFIG);
    });
    $('#procedure_template_link_child').click(function(){
        Boxy.load("/consulting/"+$(this).data("href"));
    });

    $('.newCategoryLink').click(function () {
        Boxy.load('/procedures/dialogs/template_category_new.php');
    });

    function done(s){
        var data = s.split(":");
        if(data[0]==="error"){
            Boxy.alert(data[1]);
        } else {
            refreshProcedureTemplates();
            Boxy.get($(".close")).hideAndUnload();
        }
    }

    function refreshCats(){
        $.ajax({
            url:"/api/get_procedure_templates_categories.php",
            dataType:'json',
            complete: function(s){
                var data = s.responseJSON;
                var str = '<option></option>';
                for(var i=0;i< data.length;i++){
                    str += '<option value="'+data[i].id+'" >'+data[i].name+'</option>';
                }
                $('#category_id').html(str);
            }
        });
    }
</script>