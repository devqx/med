<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/DentistryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/InsuranceItemsCostDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$service = (new DentistryDAO())->get($_REQUEST['id']);

if($_POST){
    if(is_blank($_POST['serviceName'])){
        exit("error:Dentistry Service Name is required");
    }
    if(is_blank($_POST['cost'])){
        exit("error:Dentistry Service Base Price is required");
    }
    if(is_blank($_POST['category_id'])){
        exit("error:Dentistry Service Category is required");
    }
    $service->setName($_POST['serviceName']);
    $service->setCategory( (new DentistryCategoryDAO())->get($_POST['category_id']) );

    $update = (new DentistryDAO())->update($service, $_POST['cost']);
    exit(json_encode($update));
}?>
<div style="width: 500px">
    <div class="loading_place"></div>
    <form method="post" action="<?=$_SERVER['PHP_SELF']?>" onsubmit="return AIM.submit(this, {onStart: updating, onComplete: updated})">
        <input type="hidden" name="id" value="<?=$service->getId()?>">
        <label>Service Name
            <input type="text" name="serviceName" id="serviceName" value="<?=$service->getName()?>"/></label>
        <label>Base Price
            <input type="number" step="0.10" min="0" name="cost" id="cost" value="<?=(new InsuranceItemsCostDAO())->getItemDefaultPriceByCode($service->getCode()) ?>"></label>
        <label>Category
            <span class="pull-right">
                <a href="javascript:void(0)" data-url="/dentistry/boxy.addTestCategory.php" onclick="Boxy.load($(this).attr('data-url'),{title:'Add Dentistry Service Category', afterHide: function(){refreshCategories_();}})">Add
                    Category</a></span>
            <input type="hidden" id="category_id2" name="category_id" value="<?=$service->getCategory()->getId()?>" />
        </label>
        
        <div class="button-block">
            <button class="btn">Save Changes</button>
        </div>
    </form>

</div>

<script type="text/javascript">
    function updating(){
        $('.loading_place').html('Please wait...');
    }
    function updated(s){
        try {
            var data = JSON.parse(s);
            if(data !== null){
                refreshServicesList();
                Boxy.info("Changes have been saved", function(){
                    Boxy.get($(".close")).hideAndUnload();
                });
            } else {
                Boxy.alert("Nothing was changed", function(){
                    Boxy.get($(".close")).hideAndUnload();
                });
            }
        } catch (exception){
            var data = s.split(":");
            if(data[0]=="error"){
                Boxy.alert(data[1]);
            }
        }
        refreshServicesList();

    }
    function refreshServicesList(){
        $.ajax({
            url: '/api/get_dentistry_services.php',
            success:function(a,b,c){
                var data = JSON.parse(a);
                var str='<ul class="list-blocks">';
                for(var i=0;i<data.length;i++){
                    str += '<li class="tag">' +data[i].name+ '<span><a class="editLab" href="javascript:;" data-href="/pages/pm/dentedit.php?id='+data[i].id+'">Edit</a></span></li>';
                }
                str += '</ul>';
                $('#existingLabs').html(str);
            }
        });
    }

    $(document).ready(function () {
        $.ajax({
            url: '/api/dent_category.php',
            dataType: 'json',
            success: function (a) {
                categoryData = a;
                $('#category_id2').select2({
                    placeholder: "Select a Category",
                    width: '100%',
                    formatResult: function (data) {
                        return data.name;
                    },
                    formatSelection: function (data) {
                        return data.name;
                    },
                    data: function () {
                        return {results: categoryData, text: 'name'};
                    },
                    initSelection: function(element, callback) {
                        var id = $(element).val();
                        if (id !== "") {
                            $.ajax("/api/dent_category.php?id=" + id, {
                                dataType: "json"
                            }).done(function(data) { callback(data); });
                        }
                    }
                });
            }
        });
    })
</script>