<?php
if($_POST){
    sleep(5);
    exit("error:Test Note");
}

?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" onsubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done});">
    <div class="well" id="console"></div>
    <label><textarea placeholder="type here" autofocus="autofocus"></textarea></label>
    <div class="btn-block">
        <button type="submit" class="btn">Save</button>
        <button type="reset" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
    </div>
</form>

<script>
    function start(){
        $('.btn-block button').attr('disabled','disabled');
        $("#console").html('<img src="/img/ajax-loader.gif"> please wait . . .  ')
    }
    function done(s){
        alert(s);
        $("#console").html('<img src="/img/ajax-loader.gif"> done . . .  ')
    }


</script>