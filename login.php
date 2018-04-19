<?php
header("Access-Control-Allow-Origin:*");
if(!isset($_SESSION)){
  session_start();
}

if(isset($_POST['userid'])){
  require_once $_SERVER['DOCUMENT_ROOT'].'/classes/class.staff.php';
  $staff = new StaffManager;
  $next = (isset($_POST['next'])?$_POST['next']:NULL);
  if(isset($_POST['remember'])){
    //TRUE is the last param makes it only possible via https
    setcookie("medicplus_username", $_POST['userid'], time()+ (60 * 60 * 24 * 7), '/', '', NULL);//7 days
    setcookie("medicplus_passcode", $_POST['passwd'], time()+ (60 * 60 * 24 * 7), '/', '', NULL);//7 days
  }
  $ret = $staff->doLogin($_POST['userid'],$_POST['passwd'], $next);
  exit($ret);
}

if(isset($_SESSION['staffID'])){header('Location: home.php');}

$script_block = <<<EOF
  \$(document).ready(function(){
    //\$('#username').focus();
  });
  function start(){
    \$('#output, .output').html('<img src="/img/loading.gif" /> Please wait...');
  }
  function finished(s){
    s1=s.split(":");
    //if force change password
    if(s1[0]==="change"){
      Boxy.load("/login_change_password.php?1="+s1[2]+"&2="+s1[3], {
        afterHide: function(){
          location.href='/';
        }
      });
    } else if(s1[0]==="session"){
      \$('#output, .output').html(null);
        Boxy.ask("<div style='margin-bottom:30px'>You are logged in already.<br>How would you like to continue?<br>You can <span class=\"fadedText\">terminate</span> previous session and continue here <br>OR <span class=\"fadedText\">Abort</span> this Login attempt</div>", ["Terminate and Continue","Abort this Login attempt"], function(choice){
          if(choice==="Terminate and Continue"){
            \$.post("/re-login.php", {u:s1[2], id:s1[3]}, function(data){
              form1 = \$('.form form.form-horizontal[method="post"][target][onsubmit]');
              form2 = \$('.loginForm[method="post"][target][onsubmit]');
              if(form1.length > 0){form1.submit();}
              if(form2.length > 0){form2.submit();}
            });
          }else { // Abort this Login attempt
            location.reload();
          }
        });
    } else
    //if successful
    if(s1[0] === "success"){
      //if the user came from a protected page
      if(s1[1].toLowerCase()==="doctor"){//only doctors can subscribe to examination rooms
        \$('#output, .output').html('<img src="/img/loading.gif"> Subscribing to room...');
        Boxy.load('/login_subscribe_to_room.php',{title:'Select Examination Room', afterHide:function(){
          if(typeof s1[2] !== "undefined"  && s1[0]==="success" && !/^\d+$/.test(s1[2])){console.log("xxx:a: "+s1[2]);location.href=decodeURIComponent(s1[2]);}else {location.href='/home.php';}
        }});
      }else{
        if(typeof s1[2] !== "undefined" && s1[0]==="success"){console.log("xxx:b: "+s1[2]);location.href=decodeURIComponent(s1[2]);}else {location.href='/home.php';}
      }
    } else {
      if(s1[0]=="error"){\$('#output, .output').html('<div class="warning-bar">'+s1[1]+'</div>');}
    }
  }	

EOF;

$page = "pages/login.php";
include "template.inc.out.php";
