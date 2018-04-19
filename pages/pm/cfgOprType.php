<?php
if($_POST){
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/StaffSpecialization.php';
    require_once $_SERVER['DOCUMENT_ROOT'] .'/classes/DAOs/StaffSpecializationDAO.php';
    $spe = new StaffSpecialization();
    $spe->setName($_POST['stafftype']);
    if(!empty($_POST['stafftype'])){
        $spe = (new StaffSpecializationDAO())->addSpecialization($spe);
        exit(json_encode($spe));
    }else {
        exit(json_encode(null));
    }
}
?>

<h4>Add Consultancy Types</h4>
<div id="confstaff">

    <span class="error1 loader" style="display:block;"></span>

    <form action="<?=$_SERVER['REQUEST_URI']?>" method="post"
          onSubmit="return AIM.submit(this, {onStart: start, onComplete: finishedConfig})">
        <label for="stafftype">Staff Category
            <input name="stafftype" type="text" id="stafftype" value=""></label>

        <div class="btn-block">
            <button class="btn" name="addstafftype" id="addstafftype" type="submit"><i class="icon-plus-sign"></i>Add</button>
            <button class="btn" onclick="loadStaffCharges()" type="button"><i class="icon-edit"></i>Edit Consultancy Charges</button>
        </div>
    </form>

     <a href="javascript:void(0)"></a>
</div>
<script type="text/javascript">
    function finishedConfig(s){
        s = $.parseJSON(s);
        if(s!=null){
            $('.loader').html('<span class="alert-info">Successfully added '+ s.name+'</span>');
            $('#stafftype').val('');
        }else {
            $('.loader').html('<span class="alert-error">Cannot save</span>');
        }
    }
</script>