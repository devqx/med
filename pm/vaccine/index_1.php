<?php //
//if (isset($_POST['vacname'])){
//	require 'class.vaccines.php';
//	$vac=new Vaccine();
//        $counter=$_POST['counter'];
//        if($counter < 1){
//            echo "Counter: ".$counter;
//        }else{
//            echo ".................Ok";
//        }
//        $levels=array();
//        for($i=0; i<$counter; $i++){
//            $level=array($_POST["level_"+($i + 1)], $_POST["startAge_"+($i + 1)], $_POST["endAge_"+($i + 1)]);
//            $levels[$i]=$level;
//        }
//
//	echo $vac->addVaccine($_POST['vacname'],$_POST['vacdes'], $_POST['vacprice'], $levels );
//	exit;
//
//} ?><!--<script type="text/javascript" src="/scripts/webtoolkit.aim.js"></script>-->
<!--<script type="text/javascript">-->
<!--    function start(){-->
<!--        $('#output').html('<img src="/images/loading.gif"> Please wait');-->
<!--            }-->
<!--    function done(s){	-->
<!--        $('#output').html('<span style="color:#C00">' + s +  '</span>');-->
<!--    }-->
<!--</script>-->
<!--<script src="/scripts/jquery-1.8.3.min.js" type="text/javascript"></script>-->
<!--<script src="/scripts/boxy/javascripts/jquery.boxy.js" type="text/javascript" ></script>-->
<!--<link href="/scripts/boxy/stylesheets/boxy.css" rel="stylesheet" type="text/css" media="screen" />-->
<!--<script type="text/javascript">-->
<!--    $(document).ready(function() {-->
<!--        $('.boxy').boxy();-->
<!--        $('#add').click(function(){-->
<!--            if(!isOk()){-->
<!--                return;-->
<!--            }-->
<!--            addLevel($("#levels tr").length-1);-->
<!--            $("#level").val("");-->
<!--            $("#startAge").val("");-->
<!--            $("#endAge").val("");-->
<!--            alert($("#levels tr").length-2+"\n"+$("#counter").val())-->
<!--            $("input[name='']").val(4);-->
<!--            alert($("#levels tr").length-2+"\n"+$("#counter").val())-->
<!--        });-->
<!--        $('a[data*="e"]').live({-->
<!--            click: function(){-->
<!--                var i=$(this).attr("data").replace("e", ""); -->
<!--                $("#level").val($("#level_"+i).val());-->
<!--                $("#startAge").val($("#startAge_"+i).val());-->
<!--                $("#endAge").val($("#endAge_"+i).val());-->
<!--                $("#tr"+i).remove();-->
<!--                -->
<!--            }-->
<!--        });-->
<!--        $('a[data*="d"]').live({-->
<!--            click: function(){-->
<!--                $("#tr"+$(this).attr("data").replace("d", "")).remove();-->
<!--            }-->
<!--        });-->
<!--    });-->
<!--    -->
<!--    function isOk(){-->
<!--        var status=true;-->
<!--        if($("#level").val()==="" || parseInt($("#level").val())<1){-->
<!--            status=false;-->
<!--            $("#level").css("border", "1px solid #ca2c2c");-->
<!--        }else{            -->
<!--            $("#level").removeAttr('style');-->
<!--        }-->
<!--        -->
<!--        if($("#startAge").val()===""){-->
<!--            status=false;-->
<!--            $("#startAge").css("border", "1px solid #ca2c2c");-->
<!--        }else{            -->
<!--            $("#startAge").removeAttr('style');-->
<!--        }-->
<!--        -->
<!--        if($("#endAge").val()==="" || parseInt($("#endAge").val())< parseInt($("#startAge").val())){-->
<!--            status=false;-->
<!--            $("#endAge").css("border", "1px solid #ca2c2c");-->
<!--        }else{            -->
<!--            $("#endAge").removeAttr('style');-->
<!--        }-->
<!--        $("#levels tr").each(function(index){-->
<!--            if("tr"+$("#level").val()===$(this).attr("id")){-->
<!--                status=false;-->
<!--                $("#level").css("border", "1px solid #ca2c2c");                -->
<!--            }-->
<!--        });-->
<!--        return status;-->
<!--    }-->
<!--    function addLevel(x){-->
<!--        $("#levels").append('<tr id="tr'+x+'" valign="middle" style="border-bottom: 1px solid #959595; height: 20px;">'-->
<!--            +'<td align="center"><strong>'+$("#level").val()+'</strong><input type="hidden" id="level_'+x+'" name="level_'+x+'" value="'+$("#level").val()+'"></td>'-->
<!--            +'<td align="center"><strong>'+$("#startAge").val()+'</strong><input type="hidden" id="startAge_'+x+'" name="startAge_'+x+'" value="'+$("#startAge").val()+'"></td>'-->
<!--            +'<td align="center"><strong>'+$("#endAge").val()+'</strong><input type="hidden" id="endAge_'+x+'" name="endAge_'+x+'" value="'+$("#endAge").val()+'"></td>'-->
<!--            +'<td width="7%" align="center"><a href="javascript:void(0)" data="e'+x+'">Edit</a></td>'-->
<!--            +'<td width="7%" align="center"><a href="javascript:void(0)" data="d'+x+'">Delete</a></td>'-->
<!--            +'</tr>');-->
<!--    }-->
<!--</script>-->
<!--<h2>Add Vaccine</h2>-->
<!--<fieldset><legend>Existing Vaccines</legend>-->
<!--<div>-->
<?php //
//    require 'class.vaccines.php';
//    $vac=new Vaccine();
//    echo $vac->getAllVaccines();
//?>
<!--</div>-->
<!--</fieldset>-->
<!---->
<!--<fieldset><legend>New Vaccine Details</legend>-->
<!--<span id="output"></span>-->
<!--<form action=" --><?php //echo $_SERVER['REQUEST_URI']?><!--" method="post" id="fwdij" onSubmit="return AIM.submit(this, {'onStart' : start, 'onComplete' : done})">-->
<!--<label>Vaccine Name</label>-->
<!--<input type="text" name="vacname" id="vacname"/>-->
<!--<label>Vaccine Description</label>-->
<!--<input type="text" name="vacdes" id="vacdes"/>-->
<!--<label>Vaccine Price</label>-->
<!--<input type="text" name="vacprice" id="vacprice"/>-->
<!--<label>Administration Level</label>-->
<!--<input type="number" name="level" id="level" placeholder="eg: 1 " min="1" value="">-->
<!--<label>Administration Start Age</label>-->
<!--<input type="number" name="startAge" id="startAge" placeholder="eg: 0" min="0" value="">-->
<!--<label>Administration End Age</label>-->
<!--<input type="number" name="endAge" id="endAge" placeholder="eg: 13" min="0" value=""> -->
<!--<a href="javascript:void(0)" title="Add Level" id="add">Add</a>-->
<!--<table border="1" width="70%" id="levels">-->
<!--    <tr align="left" valign="middle" style="border-bottom: 1px solid #959595; height: 30px; background-color: #efefef">-->
<!--        <td colspan="5" align="center"><strong>Vaccine Administration Levels</strong></td>-->
<!--    </tr>-->
<!--    <tr valign="middle" style="border-bottom: 1px solid #959595; height: 20px; background-color: #efefef">-->
<!--        <th align="center">Level</th>-->
<!--        <td align="center"><strong>Start Age</strong></td>-->
<!--        <td align="center"><strong>End Age</strong></td>-->
<!--        <td colspan="2" width="7%" align="center">Action</td>-->
<!--    </tr>-->
<!--</table>-->
<!--<div id="vacmgt" align="right">-->
<!--    <input type="text" id="counter" name="counter">-->
<!--    <button name="btn1" type="submit" style="width: 20%">Submit &raquo;</button></div>-->
<!--</form></fieldset>-->
<!---->
<!---->
<!--<br>-->