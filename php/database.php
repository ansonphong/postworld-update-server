<?php

class PW_Update_Server_Database {

	public function __construct(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $pw_update_server;
		global $wpdb;
		global $pw;

		// If the database is out of date, update it.
		if( $this->db_is_old() ){
			$this->update_database();
		}
	}

	public function table_name(){
		global $wpdb;
		return $wpdb->prefix.'postworld_update_server_log';
	}

	/**
	 * Updates / installs the database.
	 */
	private function update_database(){
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		global $pw_update_server;
		global $wpdb;
		global $pw;

		$log_table_name = self::table_name();
		$sql_postworld_server_log = "CREATE TABLE $log_table_name (
				log_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				action CHAR(50) NOT NULL,
				slug TEXT NULL,
				remote_ipv4 CHAR(128),
				remote_addr CHAR(128),
				remote_host CHAR(255),
				site_url CHAR(255),
				time TIMESTAMP NOT NULL,
				vars LONGTEXT  NULL,
				UNIQUE KEY log_id (log_id)
			);";
		dbDelta( $sql_postworld_server_log );

		// Save the current version of the server in the DB
		update_option( PW_UPDATE_SERVER_VERSION, $pw_update_server['version'] );
	}


	/**
	 * Check if the database is old.
	 *
	 * @return boolean If the database is old.
	 */
	private function db_is_old(){
		global $pw;
		global $pw_update_server;
		$current_version = floatval( get_option( PW_UPDATE_SERVER_VERSION, 0 ) );
		$new_version = floatval($pw_update_server['version']);
		return (bool) ( $new_version > $current_version );
	}

	/**
	 * Takes columns from Wpup_UpdateServer::filterLogInfo()
	 */
	public function add_request_log( $request ){
		$vars = $_GET;
		$vars['wp_version'] = $request->wpVersion;
		self::add_log(array(
			'action' => $request->action,
			'slug' => $request->slug,
			'site_url' => $request->wpSiteUrl,
			'vars' => $vars
			));
	}

	/**
	 * Adds a log to the database.
	 */
	public function add_log( $vars = array() ){
		global $wpdb;

		if( isset( $vars['vars'] ) )
			$vars['vars'] = json_encode($vars['vars']);

		$default_vars = array(
			'action' => 'default',
			'slug' => 'default',
			'remote_ipv4' => ip2long( $_SERVER['REMOTE_ADDR'] ),
			'remote_addr' => $_SERVER['REMOTE_ADDR'],
			'remote_host' => $_SERVER['REMOTE_HOST'],
			'time' => date('Y-m-d H:i:s',time()),
			'vars' => json_encode($_GET),
			'site_url' => '',
			);
		$vars = array_replace( $default_vars, $vars );
		return $wpdb->insert( self::table_name(), $vars );
	}
	

}






