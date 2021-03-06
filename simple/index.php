<!DOCTYPE html>
<html>
<head>
	<title></title>

	<style>
		body,
		html {
			padding: 0;
			margin: 0;
		}
		body {
			min-height: 100%;
			height: 100%;
			width: 100%;
		}

		.doge {
			max-width: 700px;
			height: auto;
			margin: auto;
			display: block;
			padding-top: 40px;
		}
		form {
			max-width: 200px;
			margin: auto;
		}
		input,
		button {
			width: 100%;
			font-size: 26px;
			font-weight: bold;
			margin-top: 10px;
			display: block;
			font-family: cursive, sans-serif;
			box-sizing: border-box;
			padding: 8px 15px;
		}
	</style>
</head>
<body>
	<?php

		// a bit of setup here so that we don't produce an error if there is no fname parameter in the URL
		$fname = '';

		if (isset($_GET['fname'])) {
			$fname = $_GET['fname'];
		}

		// this can also be written like this:
		/**

		$fname = isset($_GET['fname']) ? $_GET['fname']    :          '';
						^              ^      ^            ^          ^
				is there a fname param ?  set as fname  else   set to '' (blank);
		**/

	?>
	<img src="http://localhost/fname/simple/image/?fname=<?php echo $fname; ?>" class="doge">
	<form>
		<input type="text" name="fname">
		<button>doge me</button>
	</form>
</body>
</html>