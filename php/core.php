<?php
require_once __DIR__ . '/../lib/wp-update-server/loader.php';

global $WP_Updater_Data;
$WP_Updater_Data = array();

class WP_Update_Server extends Wpup_UpdateServer{
	/**
	 * Replaces the built-in findPackage() function.
	 */
	protected function findPackage($request) {
		global $WP_Updater_Data;

		$package = (array) $request;

		// Sanitize slug
		$package['safeSlug'] = preg_replace('@[^a-z0-9\-_\.,+!]@i', '', $package['slug'] );
		
		// Find the ID of the slug of the theme with the specified slug
		$theme = get_page_by_path( $package['safeSlug'], ARRAY_A, 'theme' );

		// Exit if no theme found
		if( empty( $theme ) )
			$this->exitWithError( 'No Theme with the specified slug.', 400 );

		// Find the most recent child post of the theme, which is the latest version
		$query = new WP_Query( array(
			'post_type' => 'theme_version',
			'post_parent' => $theme['ID'],
			'order' => 'DESC',
			'orderby' => 'date',
			'posts_per_page' => 1,
			));

		// Exit if no theme versions found
		if( empty( $query->posts ) )
			$this->exitWithError( 'No Theme Version found with the specified slug.', 400 );

		// Pluck the latest theme version
		$theme_version = $query->posts[0];

		// Get the ID of the media file attachment
		$file_attachment_id = get_post_meta( $theme_version->ID, 'theme_file', true );
		
		// Get the latest theme file address, in the upoads directory
		$package['filename'] = get_attached_file( $file_attachment_id );

		// Get details URL form postmeta for later referrence
		$package['details_url'] = get_post_meta( $query->posts[0]->ID, 'theme_details_url', true );
		// If there is no saved value, or an empty value, use the theme URL
		if( empty( $package['details_url'] ) )
			$package['details_url'] = get_permalink( $theme['ID'] );

		$WP_Updater_Data['package'] = $package;

		if ( !is_file($package['filename']) || !is_readable($package['filename']) ) {
			return null;
		}

		return call_user_func($this->packageFileLoader, $package['filename'], $package['slug'], $this->cache);
	}

	/**
	 * Load the requested package into the request instance.
	 * Replaces the built-in loadPackageFor() function, 
	 * passing the entire request to findPackage rather than just the slug.
	 *
	 * @param Wpup_Request $request
	 */
	protected function loadPackageFor($request) {
		if ( empty($request->slug) ) {
			return;
		}
		try {
			$request->package = $this->findPackage($request);
		} catch (Wpup_InvalidPackageException $ex) {
			$this->exitWithError(sprintf(
				'Package "%s" exists, but it is not a valid plugin or theme. ' .
				'Make sure it has the right format (Zip) and directory structure.',
				htmlentities($request->slug)
			));
			exit;
		}
	}

	/**
	 * Create a download URL for a plugin.
	 * Replaces the original generateDownloadUrl() function with proper vars.
	 *
	 * @param Wpup_Package $package
	 * @return string URL
	 */
	protected function generateDownloadUrl(Wpup_Package $package) {
		$query = array(
			'update_action' => 'download',
			'update_slug' => $package->slug,
		);
		return self::addQueryArg($query, $this->serverUrl);
	}


	protected function filterLogRequest($request) {
		PW_Update_Server_Database::add_request_log( $request );
		return $request;
	}

}

/**
 * Initializes the WP Update Server
 */
class WP_Updater {
	protected $updateServer;

	public function __construct() {
		//require_once __DIR__ . '/lib/wp-update-server/loader.php';
		$this->updateServer = new WP_Update_Server(home_url('/'));

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
				'slug' => get_query_var('update_slug'),
				'installed_version' => get_query_var('installed_version'),
			));
		}
	}
}

$wp_updater = new WP_Updater();

