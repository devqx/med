<!--
<script>
    function start(){
        var optionsS = new Array();
        $("input[type='checkbox']").each(function(){
            if($(this).attr("checked")){
                optionsS.push($(this).attr("name"));
            }
        });
        $.ajax({
                url:"boxy.staff-searchresults.php?id="+$('#searchfield').val()+"&type=advanced&options="+optionsS,

                beforeSend: function(){
                        $('#cntt').html('Searching... <img src="img/loading.gif" />');},
                success:function(s){$('#cntt').html(s);	}
        });//$('#cntt').load("boxy.staff-searchresults.php?id="+$('#searchfield').val());
    }
    function finished(s){	
    }
</script>


<form method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
    <label for="searchfield">Find Staff<input type="text" name="searchfield" id="searchfield" /></label>
    <label><input type="checkbox" name="name" id="name">Name</label>
    <label><input type="checkbox" name="phone" id="phone">Phone #</label>
    <label><input type="checkbox" name="email" id="email">Email Address</label>
    <label><input type="checkbox" name="profession" id="profession">Profession</label> 
     <label><input type="checkbox" name="office" id="office">Office / Hospital </label>

     <a href="staff_find.php">Basic Search</a> &nbsp;&nbsp;&nbsp;
         <button type="submit">Search &raquo;</button>&nbsp;&nbsp;
   <div id="cntt"></div>
</form>-->