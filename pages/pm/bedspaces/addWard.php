<?php
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Ward.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BlockDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/CostCenterDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_wards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_blocks.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

if(!isset($_SESSION)){@session_start();}

if (isset($_POST['addWard'])) {
    if(is_blank($_POST['name'])){
        exit("error:Please enter the ward name");
    }
    if(is_blank($_POST['block'])){
        exit("error:Please select block where this ward is located");
    }
    if(is_blank($_POST['cost_centre_id'])){
        exit("error:Please select cost centre for ward");
    }
    
    $ward=(new Ward())
        ->setName($_POST['name'])
        ->setBlock(new Block($_POST['block']))
        ->setBasePrice($_POST['base_price'])
        ->setCostCentre( new CostCenter($_POST['cost_centre_id']) )
    ;

    $ward=(new WardDAO())->addWard($ward);
    if($ward===NULL){
        exit("error:Sorry we are unable to add this ward");
    }else{
        exit("success:Ward added successfully");
    }
}
?>


<h5><a id="shWard" href="javascript:void(0)">Show/Hide Existing Wards</a></h5>

<div id="wards" style="display:none"></div>

<div>
    <form id="addWardForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
        <label>Ward Name
            <input type="text" name="name" placeholder="e.g. Surgery Ward" /></label>
        <label>Base Price <input type="number" name="base_price"> </label>
        <label>Cost Centre <select name="cost_centre_id" required data-placeholder="-- Select Cost Centre --">
                <option value=""></option>
                <?php foreach( (new CostCenterDAO())->all() as $cc ){?><option value="<?= $cc->getId()?>"><?=$cc->getName()?></option><?php }?>
            </select></label>
        <label>Block <span class="pull-right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addBlock.php', {title: 'Add Block', afterHide:function(){reloadBlock();}})">Add Block</a></span>
            <select name="block" id="block">
                <option value="">--- Select Block ---</option>
                <?php foreach($blocks as $key=>$b){ ?>
                    <option value="<?= $b->getId() ?>"><?= $b->getName() ?></option>
                <?php } ?>
            </select></label>

        <div class="btn-ward">
            <button class="btn" type="submit" name="addWard" value="true">Add</button>
            <button class="btn-link" type="reset">Reset</button>
            <div id="mgniu_"></div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $("document").ready(function(){
        loadList();
        $("#shWard").click(function(){
            $("#wards").toggle("fast");
        });
    });
    function start_() {
        $('#mgniu_').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            loadList();
            $('#mgniu_').html('<span class="alert-success">' + status_[1] + '</span>');
            $("button[class='btn-link']").trigger("click");
            showTabs(4);
        } else {
            $('#mgniu_').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    
    function loadList(){
        $("#wards").load("/pages/pm/bedspaces/listWards.php");
    }
    
    function reloadBlock(){
        $.ajax({
            url: '/api/get_blocks.php',
            type: 'get',
            dataType: 'json',
            success: function(d){
                console.log(d)
                html="";
                for(var i=0; i<d.length; i++){
                    html=html+"<option value='"+d[i].id+"' >"+d[i].name+"</option>";
                }
                $("#block").html(html);
            }
        });
    }
</script>