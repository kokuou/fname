<?php

// The following two lines will show any errors in our code when viewing in a web browser.
// You can comment these out for production if you want, but it's useful in testing to see where errors are.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// First we'll check if the 'name' URL parameter is set, or set it to our fallback if not.

$fallback = 'doge';

if (isset($_GET['fname'])) {
	$name = $_GET['fname'];
} else {
	$name = $fallback;
}

// Do some more checking. Make sure it's at least two letters long.
// In my case, I'm using English alphabet letters only, so we'll also check for that, as well as replacing any hyphens with spaces.
// I also like to limit to about 14 characters, so I'll check for that too.
// We'll set a fallback word if any of this criteria are met.

// First we'll replace hyphens and change to lower case since we only have one image for each letter
$name = str_replace('-', ' ', $name);
$name = strtolower($name);

if (
	empty($name) || // if $name is null or empty
	strlen($name) < 2 || // if $name has less than two characters
	strlen($name) > 14 || // if $name has more than 14 characters
	!preg_match('/^[a-z ]+$/', $name) // if $name does NOT (the ! indicates 'not') contain only letters a-z or spaces
) {
	// Then we set our fallback word
	$name = $fallback;
}

// First we'll create a canvas using our base image
$doge_image = imagecreatefromjpeg('doge2.jpg');

// create some variables for position, fonts, font size, font color, final text
// adjust these as necessary
$font = '../../fonts/comic-neue-2.3/Web/ComicNeue-Bold.ttf';
$font_size = 42;
$text = 'so '.$name;
$color = imagecolorallocate($doge_image, 255, 0, 0);
$x = 70;
$y = 470;

// Now place our name
imagettftext($doge_image, $font_size, 0, $x, $y, $color, $font, $text);


// Output the final image to the browser
header('Content-Type: image/jpeg');

imagepng($doge_image);
imagedestroy($doge_image);