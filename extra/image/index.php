<?php

// The following two lines will show any errors in our code when viewing in a web browser.
// You can comment these out for production if you want, but it's useful in testing to see where errors are.
ini_set('display_errors', 1);
error_reporting(E_ALL);


// Get the letter dimensions JSON file and set it as an array.
// If it doesn't exist, create it.

if (file_exists('dimensions.json')) {

	// The dimensions.json file exists, so we read it into memory and turn it into an array we can work with
	$all_dimensions = json_decode(file_get_contents("dimensions.json"), true);

} else {

	// There is no dimensions.json file yet, so we are going to create one.
	// First, we'll start off by creating an array for ourselves so we can use this afterwards to create our final image
	$all_dimensions = array();

	// Next, we'll go through each of the images in the 'letters/' directory.
	// This will create an array of all the filenames that end in .jpg, .png, .gif, and .bmp.
	$files = glob('letters/*.{jpg,png,gif,bmp}', GLOB_BRACE);

	// Now we loop through the files and create our dimensions object.
	foreach($files as $file) {

		// Get the filename and use whatever is before the extension as the letter

		// The raw filename is 'letters/a.png' (for example), so we're going to replace the '.' with a slash
		$temp_name = str_replace('.', '/', $file);

		// Now we have a string like 'letters/a/png', so we can turn that into an array by using the "explode" function
		$filename = explode('/', $temp_name);

		/* This gives us something like this:
			
			$filename = array(
				[0] => 'letters',
				[1] => 'a',
				[2] => 'png'
			);
		*/

		// All we have to do to get the letter is assign the $letter variable to the second element in that array like so:
		$letter = $filename[1];

		// Now that we have the letter we're working with, we can work out the dimensions of each
		$this_dimensions = getimagesize($file);

		// This returns an array with a bunch of information about the image, but we are interested in the width and height, which are [0] and [1], respectively.
		$this_width = $this_dimensions[0];
		$this_height = $this_dimensions[1];

		// Now we can push all of the information to the array.
		$all_dimensions[$letter]['width'] = $this_width;
		$all_dimensions[$letter]['height'] = $this_height;

		/* Now the array will look like this:

		$all_dimensions = array(
			[a] => Array
				(
					[width] => 270
					[height] => 361
				)
			);
		*/

		// We'll encode this as JSON and save it out so we don't have to do this every time.
		$json_file = fopen('dimensions.json', 'w');
		fwrite($json_file, json_encode($all_dimensions));
		fclose($json_file);
	}
	
}

// Now we get to the fun stuff.
// First we'll check if the 'name' URL parameter is set, or set it to our fallback if not.

$fallback = 'there';

if (isset($_GET['name'])) {
	$name = $_GET['name'];
} else {
	$name = $fallback;
}


// Do some more checking. Make sure it's at least two letters long.
// Since this works with English letters only, we'll also check for that, as well as replacing any hyphens with spaces.
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


// Now we're going to turn the $name variable into an array so we can do some calculations with the letters
$name_array = str_split($name);

// We're going to figure out how wide we need our image to be using a for loop and looping through the letters.
$total_width = 0;
$image_height = 0;
foreach ($name_array as $key => $letter) {

	// determine if we have a space or a letter
	if (
		empty($letter) ||
		$letter === '' ||
		$letter === ' '
	) {
		// we have a space
		$letter = "space";
	}

	// Here we are using the $all_dimensions array we set up above to get the width of the letter we are on in the loop
	$letter_width = $all_dimensions[$letter]['width'];

	// Next, we add that to the current $total_width. += is the same as saying $total_width = $total_width + $letter_width
	$total_width += $letter_width;

	// Finally, find out how high we need the image. If all your letters are the same height this won't matter, but mine are sleightly different, so we're going to look for the letter with the largest height and use that.
	$letter_height = $all_dimensions[$letter]['height'];
	if ($image_height < $letter_height) {

		// this will set the $image_height to the current $letter_height if it is smaller
		$image_height = $letter_height;
	}
}

// Great, we now have the dimensions of the final image, so let's start making it.

// First we'll create a canvas
$final_image = imagecreatetruecolor($total_width, $image_height);

// This will ensure alpha transparency is preserved
imagesavealpha($final_image, true);

// Create a transparent 'color' to use as the background of our canvas (127 = 100% transparency)
$transparent_bg = imagecolorallocatealpha($final_image, 0, 0, 0, 127);

// Fill our canvas with the transparent 'color' we created above
imagefill($final_image, 0, 0, $transparent_bg);


// Now start placing our letters.
// To do this, we'll need to keep track of the X position (horizontal) so our letters aren't on top of each other
$x_pos = 0;

// set up an angle so we can rotate our letters alternating -- set this to 0 if you don't want this.
$rotation_angle = 2;

// Loop through the $name_array letters
foreach ($name_array as $key => $letter) {

	// determine if we have a space or a letter
	if (
		empty($letter) ||
		$letter === '' ||
		$letter === ' '
	) {
		// we have a space
		$letter = "space";
	}

	// Get the filename of the letter
	$letter_image = 'letters/'.$letter.'.png';

	// Create a copy of that letter to place onto the $final_image
	$letter_copy = imagecreatefrompng($letter_image);

	// We're going to alternate rotating to be a bit playful
	$rotation = imagerotate($letter_copy, $rotation_angle, $transparent_bg);

	// Get the width and height again to use to copy onto the final canvas
	$letter_width = $all_dimensions[$letter]['width'];
	$letter_height = $all_dimensions[$letter]['height'];

	// Place it on top of the $final_image canvas at the current X position (Y will always be 0 in this case)
	// imagecopy(dst_im, src_im, dst_x, dst_y, src_x, src_y, src_w, src_h) <- the values here

	imagecopy($final_image, $rotation, $x_pos, 0, 0, 0, $letter_width, $letter_height);

	// Finally, increment the X position for the next letter and alternate the angle
	$x_pos += $letter_width;
	$rotation_angle *= -1;

	imagedestroy($letter_copy);
	imagedestroy($rotation);

}

// Here we can test how much memory this takes.
// Comment this code out for production.

// $peak_memory = memory_get_peak_usage();
// echo round($peak_memory / 1024).'kb</strong> of memory used at peak.';
// exit();


// Output the final image to the browser
header('Content-Type: image/png');

imagepng($final_image);
imagedestroy($final_image);