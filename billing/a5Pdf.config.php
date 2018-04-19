<?php
//set a `by-pass' authentication parameters
//used BYPASS as a post, since wkhtmltopdf accepts post data
//some pages redirect to the login page when we send the url to `wkhtmltopdf'.
//this disabled it for this page before passing it to `wkhtmltopdf'

/**
 * Created by PhpStorm.
 * User: robot
 * Date: 12/4/14
 * Time: 11:39 AM
 * @param $url
 * @param string $filename
 **/
@session_start();
//@requires xvfb to be installed: `sudo apt-get install xvfb`
//in the future, pageSize should be set on the fly, for cases of ID-CARD

function print_to_pdf($url, $filename, $orientation='Portrait', $staffId=null, $paperSize='A5', $noMargins=false)
{
	$orientation = ($orientation !== null) ? $orientation : 'Portrait';
	//name of file to use as a temporary holder for the generated pdf
	$tmpFile = "/tmp/pdf.pdf";
	//now generate the pdf, using x-server
	//normal installations of wkhtmltopdf doesn't compile with x-server built-in
	//old size: 1024x768x24 1280x720x24 1360x768x24
	$margins = !$noMargins ? "--margin-bottom 10mm --margin-top 10mm --margin-left 5mm  --margin-right 5mm" : " -d 96 -L 0mm -R 0mm -T 0mm -B 0mm --page-width 148mm --page-height 210mm " ;
	//$margins = !$noMarginsLabel ? "--margin-bottom 10mm --margin-top 10mm --margin-left 5mm  --margin-right 5mm" : " -d 96 -L 0mm -R 0mm -T 0mm -B 0mm --page-width 88mm --page-height 56mm " ;
	$command = "xvfb-run -a --server-args=\"-screen 0, 1024x768x24\" wkhtmltopdf --post BYPASS yes --cookie staffID $staffId --lowquality $margins --disable-javascript --print-media-type --page-size $paperSize --orientation $orientation \"$url\" $tmpFile 2>&1";
	//$command = "xvfb-run -a --server-args=\"-screen 0, 1024x768x24\" wkhtmltopdf --post BYPASS yes --cookie staffID $staffId --disable-javascript --print-media-type --orientation $orientation \"$url\" $tmpFile 2>&1";
	//error_log($command);
	$output = shell_exec($command);
	//read the generated file into buffer
	$pdf = stream_get_contents(fopen($tmpFile, "r"));
	//remove that temp file
	shell_exec("rm $tmpFile");

	//now, send that file out to the browser.
	header('Content-Type: application/pdf');
	header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
	header('Pragma: public');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Any Date in the past
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Length: ' . strlen($pdf));
	header('Content-Disposition: inline; filename="' . $filename . '";');
	echo $pdf;
}

$server = (isset($_SERVER['HTTPS']) ? "https://" : "http://") . $_SERVER['HTTP_HOST'];
ob_clean();

require_once $_SERVER['DOCUMENT_ROOT'] . '/functions/utils.php';
$orientation = !is_blank(@$_GET['orientation']) ? @$_GET['orientation'] : 'landscape';
$paperSize = !is_blank(@$_GET['paperSize']) ? @$_GET['paperSize'] : 'A5';
$noMargins = isset($_GET['noMargins']) ? true : false;

print_to_pdf($server . $_GET['page'], @$_GET['title'] . '_.pdf',$orientation, $_SESSION['staffID'], $paperSize, $noMargins);
unset($_COOKIE['staffID']);
unset($_SESSION['BYPASS']);