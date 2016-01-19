<?php
class WP_Updater {
	protected $updateServer;

	public function __construct() {
		require_once __DIR__ . '/lib/wp-update-server/loader.php';
		$this->updateServer = new Wpup_UpdateServer(home_url('/'));

		add_filter('query_vars', array($this, 'addQueryVariables'));
		add_action('template_redirect', array($this, 'handleUpdateApiRequest'));
	}

	public function addQueryVariables($queryVariables) {
		$queryVariables = array_merge($queryVariables, array(
			'update_action',
			'update_slug',
		));
		return $queryVariables;
	}

	public function handleUpdateApiRequest() {
		if ( get_query_var('update_action') ) {
			
			$this->updateServer->handleRequest(array(
				'action' => get_query_var('update_action'),
				'slug'   => get_query_var('update_slug'),
			));
		}
	}
}

$wp_updater = new WP_Updater();