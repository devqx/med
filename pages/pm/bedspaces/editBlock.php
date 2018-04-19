<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/23/15
 * Time: 3:29 PM
 */
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Block.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Clinic.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/ClinicDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_blocks.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_staff.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$block = (new BlockDAO())->getBlock($_REQUEST['id'], TRUE);
if ($_POST) {
    if(is_blank($_POST['name'])){
        exit("error:Please enter the block name");
    }
    $block->setName($_POST['name']);
    $block->setDescription($_POST['description']);
    $block->setHospital($staff->getClinic());
    $update=(new BlockDAO())->updateBlock($block);

    if($update===NULL){
        exit("error:Sorry we are unable to add this block");
    }else{
        exit("success:Block added successfully");
    }
}
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
    <label>Block Name
        <input type="text" name="name" placeholder="e.g. Surgery Block" value="<?= $block->getName()?>"/></label>
    <label>Description
        <textarea cols="5" rows="3" name="description" ><?= $block->getDescription()?></textarea></label>

    <div class="btn-block">
        <button class="btn" type="submit" value="true">Save</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        <div id="returner"></div>
        <input type="hidden" name="id" value="<?= $block->getId()?>">
    </div>
</form>

<script type="text/javascript" >
    function start_() {
        $('#returner').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            $('#returner').html('<span class="alert-success">' + status_[1] + '</span>');
            Boxy.get($(".close")).hideAndUnload(function () {
                showTabs(5);
            })
        } else {
            $('#returner').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
</script>