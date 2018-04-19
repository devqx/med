<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 2/23/15
 * Time: 3:04 PM
 */
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/RoomType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_wards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_rooms.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_room_types.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';

$room=(new RoomDAO())->getRoom($_REQUEST['id'], TRUE);
if ($_POST) {
    if(is_blank($_POST['name'])){
        exit("error:Please enter the ward name");
    }
    if(is_blank($_POST['type'])){
        exit("error:Please select category");
    }
    if(is_blank($_POST['ward'])){
        exit("error:Please select ward where this room is located");
    }


    $room->setName($_POST['name']);
    $room->setWard(new Ward($_POST['ward']));
    $room->setRoomType(new RoomType($_POST['type']));
    $update=(new RoomDAO())->updateRoom($room);
    if($update===NULL){
        exit("error:Sorry we are unable to add this ward");
    }else{
        exit("success:Room added successfully");
    }
}
?>
<form id="addRoomForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
    <label>Room Name
        <input type="text" name="name" placeholder="e.g. Surgery Room" value="<?= $room->getName()?>" /></label>
    <label>Room Category  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addRoomType.php', {title: 'Add Room Category', afterHide:function(){reloadCat()}})">Add Room Category</a></span>
        <select name="type" id="type">
            <option value="">---select room category ---</option>
            <?php foreach($roomTypes as $t){?>
                <option value="<?= $t->getId() ?>" <?=($t->getId()==$room->getRoomType()->getId()?"selected":"")?>><?= $t->getName() ?></option>
            <?php } ?>
        </select>
    </label>

    <label>Ward  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addWard.php', {title: 'Add Ward', afterHide:function(){reloadWard();}})">Add Ward</a></span>
        <select name="ward" id="ward">
            <option value="">--- select ward ---</option>
            <?php foreach($wards as $key=>$b){ ?>

                <option value="<?= $b->getId() ?>" <?=($b->getId()==$room->getWard()->getId()?"selected":"")?>><?= $b->getName() ?></option>
            <?php } ?>
        </select></label>
    <input type="hidden" name="id" value="<?= $room->getId()?>">

    <div class="btn-ward">
        <button class="btn" type="submit" name="addRoom" value="true">Save</button>
        <button class="btn-link" type="button" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
        <div id="result"></div>
    </div>
</form>

<script type="text/javascript">
    function start_() {
        $('#result').html('<img src="/img/loading.gif">');
    }
    function done_(s) {
        var status_ = s.split(":");
        if (status_[0] == 'success') {
            loadList();
            $('#result').html('<span class="alert-success">' + status_[1] + '</span>');
            Boxy.get($(".close")).hideAndUnload(function(){
                showTabs(3);
            });
        } else {
            $('#result').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    function reloadWard(){
        $.ajax({
            url: '/api/get_wards.php',
            type: 'get',
            dataType: 'json',
            success: function(d){
                console.log(d)
                html="";
                for(var i=0; i<d.length; i++){
                    html=html+"<option value='"+d[i].id+"' >"+d[i].name+"</option>";
                }
                $("#ward").html(html);
            }
        });
    }
    function reloadCat(){
        $.ajax({
            url: '/api/get_room_types.php',
            type: 'get',
            dataType: 'json',
            success: function(d){
                html="";
                for(var i=0; i<d.length; i++){
                    html=html+"<option value='"+d[i].id+"'>"+d[i].name+"</option>";
                }
                $("#type").html(html);
            }
        });
    }
</script>