<?php
/**
 * Sample File
 */

/* Check for Autoloader */
if( file_exists('vendor/autoload.php')) {
	$define = true;
} else {
	$define = false;
}
define('COMPOSERED', $define );

/* Check for Composer */
if( COMPOSERED != true ){
	echo "Composer Not Defined";
	return;
} 
require_once( 'vendor/autoload.php');

/* Instantiate new Theme */
$theme = new WordPress\Theme("THEMENAME");

/* Instantiate new Custom Post Type */
$cpt = new WordPress\CustomPostType("customer");

?>

<pre>

	<?php

	// Show Theme Details
	echo "<h2>Theme</h2>";
	echo print_r($theme);

	// Separate results.
	echo "<hr />";

	// Show Custom Post Type Details
	echo "<h2>Custom Post Type</h2>";
	print_r($cpt);
	
	?>

</pre>