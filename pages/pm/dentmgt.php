<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/protect.php';
@session_start();

if (isset($_POST['serviceName'])) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Dentistry.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryCategoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/StaffDirectoryDAO.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

    if (is_blank($_POST['serviceName'])) {
        exit("error:Service Name is required");
    }
    if (is_blank($_POST['cost'])) {
        exit("error:Service Base Price is required");
    }
    if (is_blank($_POST['category_id'])) {
        exit("error:Service Category is required");
    }
    $service = (new Dentistry())->setName($_POST['serviceName'])->setCategory((new DentistryCategoryDAO())->get($_POST['category_id']));

    $newService = (new DentistryDAO())->add($service, $_POST['cost']);

    if ($newService !== NULL) {
        exit("ok");
    }
    exit("error:Error adding Dentistry object");
}
?>
<script type="text/javascript">
    function start() {
        $(".boxy-content").animate({scrollTop: 0}, "slow");
        $('#output').html('<img src="/img/loading.gif"> Please wait');
    }
    function done(s) {
        if (s.indexOf('ok') != -1) {
            $('#output').html('<span class="alert alert-info">Dentistry updated !</span>');
            refreshServicesList();
            $('#fwdij').get(0).reset();
            /*setTimeout(function () {
                Boxy.get($('.close')).hideAndUnload()
            }, 1500);*/
        } else {
            var dat = s.split(":"); //dat[0] will always be error then
            $('#output').html('<span class="alert alert-error">' + dat[1] + '</span>');
        }
    }
    function collapseAll() {
        $(".hide").hide();
    }
</script>
<section>
    <div id="existingLabCenters"></div>

    <div><h4>Available Dentistry Services</h4></div>
    <div id="existingLabs">
        <?php
        require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/DentistryDAO.php';
        $labs = (new DentistryDAO())->getServices();?>
        <ul class="list-blocks"><?php foreach ($labs as $service) {?>
            <li class="tag"><?=$service->getName() ?><span><a class="editLab" href="javascript:;" data-href="/pages/pm/dentedit.php?id=<?= $service->getId() ?>">Edit</a></span></li>
        <?php }?></ul>
    </div>
    <hr/>
    <div><h4>New Dentistry Service</h4></div>
    <span id="output"></span><span id="msg"></span>
    <form action="/pages/pm/dentmgt.php" method="post" id="fwdij"
          onSubmit="return AIM.submit(this, {onStart: start, onComplete: done})">
        <div class="row-fluid">
            <label class="span6">Service Name
                <input type="text" name="serviceName" id="serviceName"/></label>
            <label class="span6">Base Price
                <input type="number" step="0.10" min="0" name="cost" id="cost"></label>
        </div>
        <div class="row-fluid">
            <label class="span12">Category
            <span class="pull-right">
                <a href="javascript:void(0)" data-url="/dentistry/boxy.addTestCategory.php" onclick="Boxy.load($(this).attr('data-url'), {title: 'Add Dentistry Test Category', afterHide: function () {
                                refreshCategories_();
                            }})">Add Test Category</a></span>
                <input type="hidden" id="category_id" name="category_id"  />
            </label>
        </div>

        <div class="btn-block">
            <button type="submit" class="btn pull-right">Add</button>
        </div>

    </form>

</section>
<script>
    var newlyAdded = "";
    var categoryData = [];
    $(document).ready(function () {
        $('#existingLabCenters').load("/pages/pm/dent-centers.php", function () {
           $('table.table').dataTable();
        });

        $('a.editLab').live('click', function (e) {
            if(e.handled != true){
                Boxy.load($(this).data("href"), {title: 'Edit Dentistry'});
                e.handled = true;
            }
        });

        $.ajax({
            url: '/api/dent_category.php',
            dataType: 'json',
            success: function (a) {
                categoryData = a;
                $('#category_id').select2({
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
                    }
                });
            }
        });

    });

    function refreshCategories_() {
        $.ajax({
            url: '/api/dent_category.php',
            dataType: 'json',
            success: function (a) {
                categoryData = a;
                var selectedVal = "";
                for (var i = 0; i < a.length; i++) {
                    if (a[i].name === newlyAdded) {
                        selectedVal = a[i].id;
                        break;
                    }
                }
                var Str = '';
                for (var j = 0; j < a.length; j++) {
                    Str.concat('<option value="'+a[j].id+'">'+a[j].name+'</option>');
                }
                $('#category_id').select2("val", selectedVal, true);
                $('select[name="category_id"]').html(Str).select2("val", selectedVal, true);
            }
        });
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
</script>