<?

// configuration
define('PREVIEW_SIZE', 150);
define('PREVIEW_QUALITY', 90);
define('SHOW_FILENAME', true);

function simplegallery_init() {
	ensure_preview_dir();
}

if (isset($_GET['preview'])) {
	// TODO: sanitize input correctly!!! (basename?)
	if ( ! file_filter($_GET['preview'])) {
		exit();
	}
	output_preview($_GET['preview']);
	exit();
}

function output_preview($image) {
	$previewFile = 'thumbnails_' . PREVIEW_SIZE . '/' . $image;
	ensure_preview_dir();
	if ( ! is_file($previewFile)) {
		generate_preview($image, $previewFile, PREVIEW_SIZE);
	}
	header("Content-type: image/jpeg");
	echo file_get_contents($previewFile);
}

function ensure_preview_dir() {
	if ( ! is_dir('thumbnails_' . PREVIEW_SIZE)) {
		mkdir('thumbnails_' . PREVIEW_SIZE);
	}
}

function file_filter($filename) {
	// accept only JPEG-extensions
	return preg_match('/\.(jpg|jpeg|JPG|JPEG)$/', $filename);
}

function get_images() {
	// get sorted list of JPEG images from current directory
	$images = array_filter(scandir(dirname(__FILE__)), 'file_filter');
	sort($images);
	return $images;
}

function get_preview_link($image) {
	return '?preview=' . h($image);
}

function generate_preview($source, $target, $size) {
	$image = imagecreatefromjpeg($source);
	$imageW = imagesx($image);
	$imageH = imagesy($image);
	$previewW = ($imageW >= $imageH) ? $size : $imageW * ($size / $imageH);
	$previewH = ($imageW <= $imageH) ? $size : $imageH * ($size / $imageW);
	$preview = imagecreatetruecolor($previewW, $previewH);
	imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewW, $previewH, $imageW, $imageH);
	imagejpeg($preview, $target, PREVIEW_QUALITY);
	imagedestroy($image);
	imagedestroy($preview);
}

function h($text) {
	return htmlspecialchars($text);
}


// TODO: exit, if script was included (or at least skip html part)

simplegallery_init();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de"> 
	<head>
		<title>Fotos</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
		<style type="text/css">
body {
	margin: 0;
	padding: 20px;
	background-color: #fff;
	color: #000;
	font-family: sans-serif;
}

h1 {
	margin: 0;
	padding: 0;
	border-bottom: 1px solid #000;
}

img {
	border: 0;
}

a, a:active, a:visited, a:hover {
	color: #000;
	text-decoration: none;
}

.clear {
	clear: both;
}

#images {
	border-bottom: 1px solid #000;
	padding: 10px 0 10px 0;
}

#images a.image {
	display: block;
	float: left;
	padding: 5px;
	text-align: center;
	font-size: small;
	background-color: #fff;
	height: <?= (PREVIEW_SIZE + 10 + (SHOW_FILENAME ? 10 : 0)) ?>px;
	width: <?= (PREVIEW_SIZE + 10) ?>px;
	border: 1px solid #fff;
}

#images a.image:hover {
	border: 1px solid #000;
}

#footer {
	padding: 2px 0 0 0;
	font-size: small;
	text-align: right;
}
		</style>
	</head>
	<body>
		<h1>Fotos</h1>
		<div id="images">
<? foreach(get_images() as $image): ?>
				<a class="image" href="<?= h($image) ?>"><img src="<?= get_preview_link($image) ?>" alt="<?= h($image) ?>" /><? if(SHOW_FILENAME): ?><br /><?= h($image) ?><? endif ?></a>
<? endforeach ?>
			<div class="clear"></div>
		</div>
		<div id="footer">Gallery generated automatically using <a href="http://stefanschramm.net/dev/esgal/">ESGAL</a>.</div>
	</body>
</html>
