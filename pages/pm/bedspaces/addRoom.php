<?php
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/RoomType.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomTypeDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_wards.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_rooms.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_room_types.php';

if(!isset($_SESSION)){@session_start();}

if (isset($_POST['addRoom'])) {
    if(!isset($_POST['name']) || strlen(trim($_POST['name']))<1){
        exit("error:Please enter the ward name");
    }
    if(!isset($_POST['type']) || strlen(trim($_POST['type']))<1){
        exit("error:Please select category");
    }
    if(!isset($_POST['ward']) || strlen(trim($_POST['ward']))<1){
        exit("error:Please select ward where this room is located");
    }
    
    $room=new Room();
    $room->setName($_POST['name']);
        $ward=new Ward();
            $ward->setId($_POST['ward']);
    $room->setWard($ward);
        $type=new RoomType();
            $type->setId($_POST['type']);
    $room->setRoomType($type);
    $room=(new RoomDAO())->addRoom($room);
    if($room===NULL){
        exit("error:Sorry we are unable to add this ward");
    }else{
        exit("success:Room added successfully");
    }
}
?>


<h5><a id="shRoom" href="javascript:void(0)">Show/Hide Existing Rooms</a></h5>

<div id="rooms" style="display:none"></div>

<div>
    <form id="addRoomForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
        <label>Room Name
            <input type="text" name="name" placeholder="e.g. Surgery Room" /></label>
        <label>Room Category  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addRoomType.php', {title: 'Add Room Category', afterHide:function(){reloadCat()}})">Add Room Category</a></span>
            <select name="type" id="type">
                <option value="">---select room category ---</option>
                <?php foreach($roomTypes as $t){?>
                    <option value="<?= $t->getId() ?>"><?= $t->getName() ?></option>
                <?php } ?>
            </select>
        </label>

        <label>Ward  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addWard.php', {title: 'Add Ward', afterHide:function(){reloadWard();}})">Add Ward</a></span>
            <select name="ward" id="ward">
                <option value="">--- select ward ---</option>
                <?php foreach($wards as $key=>$b){ ?>
                    <option value="<?= $b->getId() ?>"><?= $b->getName() ?></option>
                <?php } ?>
            </select></label>

        <div class="btn-ward">
            <button class="btn" type="submit" name="addRoom" value="true">Add</button>
            <button class="btn-link" type="reset">reset</button>
            <div id="mgniu_"></div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $("document").ready(function(){
        loadList();
        $("#shRoom").click(function(){
            $("#rooms").toggle("fast");
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
        } else {
            $('#mgniu_').html('<span class="alert-error">' + status_[1] + '</span>');
        }
    }
    
    function loadList(){
        $("#rooms").load("/pages/pm/bedspaces/listRooms.php");
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