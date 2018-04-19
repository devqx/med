<?php
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Block.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_blocks.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';

if(!isset($_SESSION)){@session_start();}

if (isset($_POST['addBlock'])) {
    if(!isset($_POST['name']) || strlen(trim($_POST['name']))<1){
        exit("error:Please enter the block name");
    }
    
    $block=new Block();
        $block->setName($_POST['name']);
        $block->setDescription($_POST['description']);
        $block->setHospital($staff->getClinic());
    $block=(new BlockDAO())->addBlock($block);
    if($block===NULL){
        exit("error:Sorry we are unable to add this block");
    }else{
        exit("success:Block added successfully");
    }
}
?>


<h5><a id="shBlock" href="javascript:void(0)">Show/Hide Existing Blocks</a></h5>

<div id="blocks" style="display:none">
    
</div>


<div>
    <form id="addBlockForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
        <label>Block Name
            <input type="text" name="name" placeholder="e.g. Surgery Block" /></label>
        <label>Description
            <textarea cols="5" rows="3" name="description" ></textarea></label>

        <div class="btn-block">
            <button class="btn" type="submit" name="addBlock" value="true">Add</button>
            <button class="btn-link" type="reset">reset</button>
            <div id="mgniu_"></div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $("document").ready(function(){
        loadList();
        $("#shBlock").click(function(){
            $("#blocks").toggle("fast");
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
            $("button[class='btn-link']").trigger("click")
        } else {
            $('#mgniu_').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    
    function loadList(){
        $("#blocks").load("/pages/pm/bedspaces/listBlocks.php");
    }
</script>