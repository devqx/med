
<?php 
function so_5645412_url_params($url) {
    $url_comps = parse_url($url);
    $query = $url_comps['query'];

    $args = array();
    parse_str($query, $args);

    return $args;
}

$get = so_5645412_url_params($_GET['url']); ?>
<section>
<form method="get" action="<?=urldecode($_GET['url']) ?>">
<label>Paper Size <select name="paperSize"><option value="A4">A4</option><option value="A5">A5</option></select>
<label>Orientation <select name="orientation"><option value="Portrait">Portrait</option><option value="Landscape">Landscape</option></select></label>
<?php foreach ($get as $key => $value) {?>
	<input type='hidden' name='<?=$key?>' value='<?=urldecode($value)?>'/>
<?php }?>
<p style="margin-bottom:20px"></p>
<button type="submit" class="btn" onclick="generatePDF()">Continue</button>
<button type="button" class="btn-link" onclick="Boxy.get(this).hideAndUnload()">Cancel</button>
</form>
</section>
<script type="text/javascript">
var generatePDF=function(){
	$.blockUI({
			message: '<div class="ball"></div><br><h6 class="fadedText" style="font-size:200%">Generating PDF. Please wait...</h6>',
			css: {
				borderWidth: '0',
				backgroundColor:'transparent'
			}
		});
};</script>
