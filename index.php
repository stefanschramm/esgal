<?

// configuration
define('PREVIEW_SIZE', 150);
define('PREVIEW_QUALITY', 90);
define('SHOW_FILENAME', true);
define('VIEW_IN_PAGE', false);

// title (if null, determined automatically by name of current directory)
// define('ESGAL_TITLE', "Test");
define('ESGAL_TITLE', null);

define('THUMBNAILS_DIR_PREFIX', 'thumbnails_');
define('PREVIEW_GET_VAR', 'preview');
define('VIEW_GET_VAR', 'view');
define('JPEG_FILENAME_REGEXP', '/\.(jpg|jpeg|JPG|JPEG)$/');

if (isset($_GET[PREVIEW_GET_VAR])) {
	esgal_output_preview($_GET[PREVIEW_GET_VAR]);
	exit();
}

function esgal_init() {
	esgal_ensure_preview_dir();
}

// output preview of specified image; generate preview if it doesn't exist yet
function esgal_output_preview($image) {
	$image = basename($image);
	if ( ! esgal_file_filter($image) || ! is_file($image)) {
		exit();
	}
	$previewFile = THUMBNAILS_DIR_PREFIX . PREVIEW_SIZE . '/' . $image;
	esgal_ensure_preview_dir();
	if ( ! is_file($previewFile)) {
		esgal_generate_preview($image, $previewFile, PREVIEW_SIZE);
	}
	header("Content-type: image/jpeg");
	echo file_get_contents($previewFile);
}

// create preview directory if it doesn't exist yet
function esgal_ensure_preview_dir() {
	if ( ! is_dir(THUMBNAILS_DIR_PREFIX . PREVIEW_SIZE)) {
		mkdir(THUMBNAILS_DIR_PREFIX . PREVIEW_SIZE);
	}
}

// returns true, if specified file matches pattern for allowed filenames
function esgal_file_filter($filename) {
	// accept only JPEG-extensions
	return preg_match(JPEG_FILENAME_REGEXP, $filename);
}

// get alphabetically sorted list of images of the directory the script resides in
function esgal_get_images() {
	// get sorted list of JPEG images from current directory
	$images = array_filter(scandir(dirname(__FILE__)), 'esgal_file_filter');
	sort($images);
	return $images;
}

// get image in list before specified image; empty string if specified image was first image
function esgal_get_previous($image) {
	$images = esgal_get_images();
	if ($images[0] == $image) {
		// first image
		return "";
	}
	for ($i = 0; $i < count($images); $i++) {
		if ($images[$i] == $image) {
			return $images[$i-1];
		}
	}
	return ""; // not found
}

// get image in list after specified image; empty string if specified image was last image
function esgal_get_next($image) {
	$images = esgal_get_images();
	if ($images[count($images)-1] == $image) {
		// last image
		return "";
	}
	for ($i = 0; $i < count($images); $i++) {
		if ($images[$i] == $image) {
			return $images[$i+1];
		}
	}
	return ""; // not found
}

// get image src for a preview for the specified image
function esgal_get_preview_link($image) {
	// TODO: do an is_file here and only generate, if thumbnails doesnt exist?
	return $_SERVER['SCRIPT_NAME'] . '?' . PREVIEW_GET_VAR . '=' . h($image);
}

// get link for viewing the specified image
function esgal_get_link($image = "") {
	if ($image == "") {
		return $_SERVER['SCRIPT_NAME'];
	}
	if (VIEW_IN_PAGE) {
		return $_SERVER['SCRIPT_NAME'] . '?' . VIEW_GET_VAR . '=' . h($image);
	}
	else {
		return h($image);
	}
}

// get title of gallery
function esgal_get_title() {
	if (ESGAL_TITLE === null) {
		return basename(dirname($_SERVER['SCRIPT_NAME']));
	}
	else {
		return ESGAL_TITLE;
	}
}

// generate preview for specified image
function esgal_generate_preview($source, $target, $size) {
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

// TODO: prefix?
function h($text) {
	return htmlspecialchars($text);
}


// TODO: exit, if script was included (or at least skip html part)

esgal_init();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de"> 
	<head>
		<title><?= h(esgal_get_title()) ?></title>
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

#viewimage img {
	width: 100%;
}

#navigation {
	border-bottom: 1px solid #000;
	margin-bottom: 3px;
}

#navigation a, #navigation span {
	float: left;
	font-weight: bold;
	font-size: large;
	width: 30px;
	text-align: center;
}

#footer {
	border-top: 1px solid #000;
	padding: 2px 0 0 0;
	font-size: small;
	text-align: right;
}

#footer a {
	text-decoration: underline;
}

		</style>
	</head>
	<body>
		<h1><?= h(esgal_get_title()) ?></h1>
<? if (isset($_GET[VIEW_GET_VAR])): ?>
		<div id="navigation">
<?
$previous = esgal_get_previous($_GET[VIEW_GET_VAR]);
$next = esgal_get_next($_GET[VIEW_GET_VAR]);
?>
<? if ($previous != ""): ?>
			<a class="previous" href="<?= h(esgal_get_link($previous)) ?>">&lt;</a>
<? else: ?>
			<span>&nbsp;</span>
<? endif ?>
			<a class="up" href="<?= h(esgal_get_link()) ?>">^</a>
<? if ($next != ""): ?>
			<a class="next" href="<?= h(esgal_get_link($next)) ?>">&gt;</a>
<? else: ?>
			<span>&nbsp;</span>
<? endif ?>
			<div class="clear"></div>
		</div>
		<div id="viewimage">
			<img src="<?= h($_GET[VIEW_GET_VAR]) ?>" alt="<?= h($_GET[VIEW_GET_VAR]) ?>" />
		</div>
<? else: ?>
		<div id="images">
<? foreach(esgal_get_images() as $image): ?>
				<a class="image" href="<?= h(esgal_get_link($image)) ?>"><img src="<?= esgal_get_preview_link($image) ?>" alt="<?= h($image) ?>" /><? if(SHOW_FILENAME): ?><br /><?= h($image) ?><? endif ?></a>
<? endforeach ?>
			<div class="clear"></div>
		</div>
<? endif ?>
		<div id="footer">Gallery generated automatically using <a href="http://stefanschramm.net/dev/esgal/">ESGAL</a>.</div>
	</body>
</html>

