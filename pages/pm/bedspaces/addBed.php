<?php
$_GET['suppress']=TRUE;
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Bed.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Room.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/RoomDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/BedDAO.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/get_rooms.php';

if(!isset($_SESSION)){@session_start();}

if (isset($_POST['addBed'])) {
    if(!isset($_POST['name']) || strlen(trim($_POST['name']))<1){
        exit("error:Please enter the  bed name/label");
    }
    if(!isset($_POST['room']) || strlen(trim($_POST['room']))<1){
        exit("error:Please select the room");
    }

    $bed = new Bed();
    $bed->setName($_POST['name']);
    $room = new Room();
    $room->setId($_POST['room']);
    $bed->setRoom($room);
    $bed->setDescription($_POST['description']);
    $bed=(new BedDAO())->addBed($bed);
    if($bed===NULL){
        exit("error:Sorry we are unable to add this bed");
    }else{
        exit("success:Bed added successfully");
    }
}
?>


<h5><a id="shBed" href="javascript:void(0)">Show/Hide Existing Bed Space</a></h5>

<div id="beds" style="display:none">
    
</div>


<div>
    <form id="addBedForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart': start_, 'onComplete': done_});">
        <label>Bed Label/Name
            <input type="text" name="name" placeholder="e.g. Bed 123" /></label>

        <label>Room  <span style="float: right"><a href="javascript:void(0)" onclick="Boxy.load('/pages/pm/bedspaces/addRoom.php', {title: 'Add Room', afterHide:function(){reloadRoom()}})">Add Room</a></span>
            <select name="room" id="room">
                <option value="">---select room ---</option>
                <?php foreach($rooms as $r){?>
                    <option value="<?= $r->getId() ?>"><?= $r->getName() ?></option>
                <?php } ?>
            </select></label>
        <label>Description <textarea rows="3" cols="5" name="description"></textarea></label>

        <div class="btn-block">
            <button class="btn" type="submit" name="addBed" value="true">Add</button>
            <button class="btn-link" type="reset">reset</button>
            <div id="mgniu_"></div>
        </div>
    </form>
</div>

<script type="text/javascript">
    $("document").ready(function(){
        loadList();
        $("#shBed").click(function(){
            $("#beds").toggle("fast");
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
        $("#beds").load("/pages/pm/bedspaces/listBeds.php");
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