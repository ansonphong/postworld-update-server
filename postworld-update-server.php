<?php
/*
Plugin Name: Postworld Update Server
Description: Update server for updating Postworld-based themes.
Version: 1.0.9
Author: Phong
Author URI: https://phong.com
Special Thanks: Yahnis Elsts
*/

add_action( 'after_setup_theme', 'pw_update_server_init' );
function pw_update_server_init(){
	global $pw_update_server;
	$pw_update_server = array(
		'version' => 1.09,
		);
	define( 'PW_UPDATE_SERVER_VERSION', 'postworld-update-server-version' );

	// Require Postworld
	if( !defined( 'POSTWORLD' ) )
		return false;
	
	global $pw;
	include 'php/database.php';
	//include 'php/admin.php';
	include 'php/post-types.php';
	include 'php/core.php';

	// Instantiate the DB
	global $pws_database;
	$pws_database = new PW_Update_Server_Database();

	// Test : http://artdroid/?update_action=artdroid&update_slug=artdroid	
}



