<?php

/*
 Plugin Name: Wordpress Helpers
 Plugin URI: #
 Description: Adds helper classes used to ease WordPress development.
 Author: Frank McCoy
 Version: 0.0.1
 Author URI: http://github.com/fmccoy/
 */

 define( 'WPHELPER_DIR', dirname( __FILE__ ) );


 function wphelper_scripts()
 {
 	wp_enqueue_style('wphelper-admin', WPMU_PLUGIN_URL . '/wordpress-helpers/assets/css/wphelper-admin.css');
 }
 add_action('admin_enqueue_scripts', 'wphelper_scripts');