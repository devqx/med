<?php
require $_SERVER['DOCUMENT_ROOT'].'/classes/class.bedspaces.php';
$bedOBJ=new Bedspaces;

if($_POST){
    exit($bedOBJ->addRoom($_POST['room_name'],$_POST['ward_id']));
}
?>
<div>
<script type="text/javascript">
$(document).ready(function(){
    setTimeout(function(){refreshWards()},100);
});
function refreshWards(){
    obj=null;
    $('#ward_id').html('');
    $.ajax({
        url:'/pages/pm/bedspaces/ajax.list_wards.php',
        type:'GET',
        dataType:'json',
        success:function(s){
            for(var i=0;i< s.length;i++){
                var opt=s[i];
                $('#ward_id').append('<option value="'+opt.id+'">'+opt.ward_name+'</option>');
            }
        },
        error:function(){
            alert('failed to refresh ward list');
        }
    })
}
function start_(){$('#mgniu_').html('<img src="/img/loading.gif">');}
function done_(s){
	status_ = s.split(":");
	if(status_[0] =='success'){
        refreshWards();
        $('#addRoomForm input').each(function(){$(this).val('');});
		$('#mgniu_').html('<span class="alert-success">'+status_[1]+'</span>');
	}else{
		$('#mgniu_').html('<span class="alert-error">'+status_[1]+'</span>');
	}
}
    function loadAddWard(){
        Boxy.load('/pages/pm/bedspaces/add_ward.php',{title:'Add Ward',afterHide:function(){refreshWards();}});
    }
</script>

<form id="addRoomForm" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" onSubmit="return AIM.submit(this, {'onStart' : start_, 'onComplete' : done_});">
<label>Room name
<input type="text" name="room_name" placeholder="e.g. Room A" /></label>

<label>Ward <span class="pull-right"><a href="javascript:void(0)" onclick="loadAddWard()">Add Ward</a></span><select name="ward_id" id="ward_id"></select></label>


<div class="btn-block">
    <button class="btn" type="submit">Add</button>
    <button class="btn-link" type="reset">reset</button>
<div id="mgniu_"></div></div>
</form>
</div>
