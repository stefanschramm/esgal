<?

// configuration
define('PREVIEW_SIZE', 150);
define('SHOW_FILENAME', true);

function simplegallery_init() {
	ensure_preview_dir();
}

if (isset($_GET['preview'])) {

	// TODO: sanitize input correctly!!!
	if ( ! preg_match('/\.(jpg|jpeg|JPG|JPEG)$/', $_GET['preview'])) {
		exit();
	}

	$image = $_GET['preview'];
	$previewFile = 'thumbnails_' . PREVIEW_SIZE . '/' . $image;
	ensure_preview_dir();
	if ( ! is_file($previewFile)) {
		generate_preview($image, $previewFile, PREVIEW_SIZE);
	}
	header("Content-type: image/jpeg");
	echo file_get_contents($previewFile);
	exit();
}

function ensure_preview_dir() {
	if ( ! is_dir('thumbnails_' . PREVIEW_SIZE)) {
		mkdir('thumbnails_' . PREVIEW_SIZE);
	}
}

function get_images() {
	// ensure that _thumbnails folder exists
	// read dir content; remove pseudo dirs
	$files = array_values(array_diff(scandir(dirname(__FILE__)), array('.', '..')));
	foreach ($files as $i => $file) {
		if ( ! preg_match('/\.(jpg|jpeg|JPG|JPEG)$/', $file)) {
			unset($files[$i]);
		}
	}
	return $files;
}

function get_preview_link($image) {
	return '?preview=' . $image;
}

function generate_preview($source, $target, $size) {
	$image = imagecreatefromjpeg($source);
	$imageW = imagesx($image);
	$imageH = imagesy($image);
	if ($imageW > $imageH) {
		$previewW = $size;
		$previewH = $imageH * ($size / $imageW);
	}
	else {
		$previewH = $size;
		$previewW = $imageW * ($size / $imageH);
	}
	$preview = imagecreatetruecolor($previewW, $previewH);
	imagecopyresampled($preview, $image, 0, 0, 0, 0, $previewW, $previewH, $imageW, $imageH);
	imagejpeg($preview, $target);
	imagedestroy($image);
	imagedestroy($preview);
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
				<a class="image" href="<?= $image ?>"><img src="<?= get_preview_link($image) ?>" alt="<?= $image ?>" /><? if(SHOW_FILENAME): ?><br /><?= $image ?><? endif ?></a>
<? endforeach ?>
			<div class="clear"></div>
		</div>
		<div id="footer">Gallery generated automatically using ESGAL.</div>
	</body>
</html>
