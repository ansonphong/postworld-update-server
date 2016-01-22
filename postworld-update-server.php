<?php
/*
Plugin Name: Postworld Update Server
Description: Update server for updating Postworld-based themes.
Version: 1.0
Author: Phong
Author URI: https://phong.com
Special Thanks: Yahnis Elsts
*/

add_action( 'after_setup_theme', 'wp_updater_init' );
function wp_updater_init(){
	// Require Postworld
	if( !defined( 'POSTWORLD' ) )
		return false;
	
	global $pw;
	//include 'admin.php';
	include 'post-types.php';
	include 'class.php';

	// Test : http://artdroid/?update_action=artdroid&update_slug=artdroid
	
}
