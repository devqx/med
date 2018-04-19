<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/23/15
 * Time: 2:40 PM
 */

$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bed.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Room.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_rooms.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$bed = (new BedDAO())->getBed($_REQUEST['id']);

if ($_POST) {
    if(is_blank($_POST['name'])){
        exit("error:Please enter the  bed name/label");
    }
    if(is_blank($_POST['room'])){
        exit("error:Please select the room");
    }

    $bed->setName($_POST['name']);
    $bed->setRoom(new Room($_POST['room']));
    $bed->setDescription($_POST['description']);
    $update=(new BedDAO())->updateBed($bed);
    if($update===NULL){
        exit("error:Sorry we are unable to add this bed");
    }else{
        exit("success:Bed added successfully");
    }
}
?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
    <label>Bed Label/Name
        <input type="text" name="name" placeholder="e.g. Bed 123" value="<?=$bed->getName() ?>" /></label>

    <label>Room  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addRoom.php', {title: 'Add Room', afterHide:function(){reloadRoom()}})">Add Room</a></span>
        <select name="room" id="room">
            <option value="">---select room ---</option>
            <?php foreach($rooms as $r){?>
                <option value="<?= $r->getId() ?>" <?= ($bed->getRoom()->getId()==$r->getId()?"selected":"") ?>><?= $r->getName() ?></option>
            <?php } ?>
        </select></label>
    <label>Description <textarea rows="3" cols="5" name="description"><?= $bed->getDescription() ?></textarea></label>

    <div class="btn-block">
        <button class="btn" type="submit" name="addBed" value="true">Save</button>
        <button class="btn-link" onclick="Boxy.get(this).hideAndUnload()" type="button">Cancel</button>
        <div id="___"></div>
        <input type="hidden" name="id" value="<?= $bed->getId()?>">
    </div>
</form>
<script type="text/javascript">
    function start_() {
        $('___').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            $('#___').html('<span class="alert-success">' + status_[1] + '</span>');
            Boxy.get($(".close")).hideAndUnload(function () {
                showTabs(2);
            });

        } else {
            $('#___').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    function reloadRoom(){
        $.ajax({
            url: '/api/get_rooms.php',
            type: 'get',
            dataType: 'json',
            success: function(d){
                html="";
                for(var i=0; i<d.length; i++){
                    html=html+"<option value='"+d[i].id+"' >"+d[i].name+"</option>";
                }
                $("#room").html(html);
            }
        });
    }
</script>