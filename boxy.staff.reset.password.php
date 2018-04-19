<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 6/23/14
 * Time: 12:14 PM
 */
if(!isset($_SESSION)){session_start();}
if($_POST){
    $ret = (object)null;
    require_once $_SERVER['DOCUMENT_ROOT'].'/classes/DAOs/StaffDirectoryDAO.php';
    if($_POST['pswd']==$_POST['pswd1'] && !empty($_POST['pswd'])){
        $callback = (new StaffDirectoryDAO())->resetPassword( $_POST['staff_id'], $_POST['pswd']);
        $ret->message = $callback;
        $ret->status = "success";
        exit( json_encode($ret) );
    }else {
        $ret->message = "Passwords are not the same or empty";
        $ret->status = "error";
        exit(json_encode($ret));
    }

}
?>
<div class="well"></div>
<form method="post" action="<?=$_SERVER['REQUEST_URI']?>" onsubmit="return AIM.submit(this, {onStart:__09,onComplete:__08})">
    <label>New Password:<input type="password" name="pswd"></label>
    <label>Confirm Password:<input type="password" name="pswd1"></label>
    <div class="btn-block">
        <button class="btn">Reset</button>
        <input type="hidden" name="staff_id" value="<?= $_GET['id']?>">
        <button class="btn-link" type="button" onclick="Boxy.get($('.close')).hideAndUnload()">Cancel</button>
    </div>
</form>
<script type="text/javascript">
    function __09(){
        $('.boxy-content.well').html('<img src="/img/loading.gif"> Please wait... ');
    }
    function __08(s){
        console.log(s);
        var data = JSON.parse(s);
        if(data.status=="success"){
            Boxy.info("password reset successfully");
            Boxy.get($(".close")).hideAndUnload();
        }else {
            $('.boxy-content.well').html('<span class="alert">'+data.message+'</span>').removeClass("alert-error").addClass("alert-error");
        }
    }
</script>