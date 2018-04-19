<?php
/**
 * Created by JetBrains PhpStorm.
 * User: robot
 * Date: 12/10/12
 * Time: 1:22 PM
 * To change this template use File | Settings | File Templates.
 */
if(!isset($_SESSION)){@session_start();}

?><div id="camera" style="display:none">
    <span class="camTop"><span>[x] close</span></span>

    <div id="screen"></div>
    <div id="buttons">
        <div class="buttonPane">
            <a id="shootButton" href="" class="blueButton">Capture!</a>
        </div>
        <div class="buttonPane hidden">
            <a id="cancelButton" href="" class="blueButton">Cancel</a>
            <a id="uploadButton" href="" class="greenButton">Upload!</a>
        </div>
    </div>
    <span class="settings"></span>
</div>
