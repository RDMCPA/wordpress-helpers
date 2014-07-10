<?php
/*
 Plugin Name: Wordpress Helpers Loader
 Plugin URI: https://github.com/fmccoy/wordpress-helpers
 Description: Loads the helper plugin file.
 Author: Frank McCoy
 Version: 0.0.1
 Author URI: http://github.com/fmccoy/
 */

// Check for Composer support.
if( WP_COMPOSER != true ) exit;

require_once( WPMU_PLUGIN_DIR . '/wordpress-helpers/wordpress-helpers.php' );