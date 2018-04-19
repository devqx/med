<?php
if (isset($_GET['advanced-search'])){ ?>
    <script>
        function start(){
            var optionsS = [];
            $("input[type='checkbox']").each(function(){
                if($(this).attr("checked")){
                    optionsS.push($(this).attr("name"));
                }
            });
            $.ajax({
                    url:"boxy.staff-searchresults.php?id="+$('#searchfield').val()+"&type=advanced&options="+optionsS,
                    beforeSend: function(){
                            $('#cntt').html('Searching... <img src="/img/loading.gif" />');},
                    success:function(s){$('#cntt').html(s);	}
            });
        }
        function finished(s){	
        }
    </script>


    <form method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <label for="searchfield">Find Staff<input type="text" name="searchfield" id="searchfield" /></label>
        <label class="inline"><input type="checkbox" name="name" id="name">Name</label>
        <label class="inline"><input type="checkbox" name="phone" id="phone">Phone #</label>
        <label class="inline"><input type="checkbox" name="email" id="email">Email Address</label>
        <label class="inline"><input type="checkbox" name="profession" id="profession">Profession</label>
        <!-- <label><input type="checkbox" name="office" id="office">Office / Hospital </label>-->

         <a href="staff_find.php">Basic Search</a> &nbsp;&nbsp;&nbsp;
             <button type="submit" class="btn">Search &raquo;</button>&nbsp;&nbsp;
       <div id="cntt"></div>
    </form>
<?php } else {?>
    
    <script>    
        function start(){
            $.ajax({
                url:"boxy.staff-searchresults.php?id="+$('#searchfield').val(),
                beforeSend: function(){$('#cntt').html('Searching... <img src="/img/loading.gif" />');},
                success:function(s){$('#cntt').html(s);	}
            });
        }
        function finished(s){	
        }
    </script>
    <form method="post" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : finished})">
        <label for="searchfield">Find Staff<input type="text" name="searchfield" id="searchfield" /></label>

          <a href="?advanced-search">Advanced Search</a> &nbsp;&nbsp;&nbsp;
          <button type="submit" class="btn">Search &raquo;</button>&nbsp;&nbsp;
    </form>
    <div id="cntt" class="document"></div>
<?php } ?>
