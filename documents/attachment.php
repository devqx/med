<?php
/**
 * Created by PhpStorm.
 * User: robot
 * Date: 4/28/15
 * Time: 11:26 AM
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/DAOs/PatientAttachmentDAO.php';
$attachment = (new PatientAttachmentDAO())->get($_GET['id']);
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?= $attachment->getNote() ?> (<?= $attachment->getPatient()->getFullname() ?>)</title>
	<!--    <link href="/libs/pdfjs/build/generic/web/viewer.css" rel="stylesheet">-->
</head>
<body style="/*margin: 5px auto; width: 850px*/">

<div style="margin: 0 auto; text-align: center;box-shadow: 0 1px 2px rgba(34, 25, 25, 0.4);">
	<button id="prev" class="btn-link"><i class="icon-chevron-left"></i></button>
	&nbsp; &nbsp;
	<span>Page: <span id="page_num"></span> of <span id="page_count"></span></span>
	&nbsp; &nbsp;
	<button id="next" class="btn-link"><i class="icon-chevron-right"></i></button>
</div>

<div class="pdfViewer">
	<canvas class="page" id="the-canvas"></canvas>
</div>

<!-- for legacy browsers add compatibility.js -->
<!--<script src="../compatibility.js"></script>-->

<script src="/libs/pdfjs/build/pdf.js"></script>

<script id="script">
	//
	// If absolute URL from the remote server is provided, configure the CORS
	// header on that server.
	var url = '<?= $attachment->getUrl()?>';
	//
	// Disable workers to avoid yet another cross-origin issue (workers need
	// the URL of the script to be loaded, and dynamically loading a cross-origin
	// script does not work).
	//
	// PDFJS.disableWorker = true;

	//
	// In cases when the pdf.worker.js is located at the different folder than the
	// pdf.js's one, or the pdf.js is executed via eval(), the workerSrc property
	// shall be specified.
	//
	PDFJS.workerSrc = '/libs/pdfjs/build/pdf.worker.js';

	var pdfDoc = null,
		pageNum = 1,
		pageRendering = false,
		pageNumPending = null,
		scale = 1.5,
		canvas = document.getElementById('the-canvas'),
		ctx = canvas.getContext('2d');

	/**
	 * Get page info from document, resize canvas accordingly, and render page.
	 * @param num Page number.
	 */
	function renderPage(num) {
		pageRendering = true;
		// Using promise to fetch the page
		pdfDoc.getPage(num).then(function (page) {
			var viewport = page.getViewport(scale);
			canvas.height = viewport.height;
			canvas.width = viewport.width;

			// Render PDF page into canvas context
			var renderContext = {
				canvasContext: ctx,
				viewport: viewport
			};
			var renderTask = page.render(renderContext);

			// Wait for rendering to finish
			renderTask.promise.then(function () {
				pageRendering = false;
				if (pageNumPending !== null) {
					// New page rendering is pending
					renderPage(pageNumPending);
					pageNumPending = null;
				}
			});
			Viewer.get($(".close")).center();
		});

		// Update page counters
		document.getElementById('page_num').textContent = pageNum;
	}

	/**
	 * If another page rendering in progress, waits until the rendering is
	 * finised. Otherwise, executes rendering immediately.
	 */
	function queueRenderPage(num) {
		if (pageRendering) {
			pageNumPending = num;
		} else {
			renderPage(num);
		}
	}

	/**
	 * Displays previous page.
	 */
	function onPrevPage() {
		if (pageNum <= 1) {
			return;
		}
		pageNum--;
		queueRenderPage(pageNum);
	}
	document.getElementById('prev').addEventListener('click', onPrevPage);

	/**
	 * Displays next page.
	 */
	function onNextPage() {
		if (pageNum >= pdfDoc.numPages) {
			return;
		}
		pageNum++;
		queueRenderPage(pageNum);
	}
	document.getElementById('next').addEventListener('click', onNextPage);

	/**
	 * Asynchronously downloads PDF.
	 */
	PDFJS.getDocument(url).then(function (pdfDoc_) {
		pdfDoc = pdfDoc_;
		document.getElementById('page_count').textContent = pdfDoc.numPages;

		// Initial/first page rendering
		renderPage(pageNum);
	}).then(null, function (error) {
		Viewer.get($(".close")).hideAndUnload();
		Boxy.alert("Sorry, we couldn't load the attached document.");
	});
</script>

</body>
</html>