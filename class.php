<?php

require_once __DIR__ . '/lib/wp-update-server/loader.php';

class WP_Update_Server extends Wpup_UpdateServer{
	/**
	 * Replaces the built-in findPackage() function.
	 */
	protected function findPackage($request) {
		$package = (array) $request;

		//Check if there's a slug.zip file in the package directory.
		$package['slug'] = preg_replace('@[^a-z0-9\-_\.,+!]@i', '', $request->slug );
		$package['safeSlug'] = preg_replace('@[^a-z0-9\-_\.,+!]@i', '', $package['slug'] );
		//$package['filename'] = $this->packageDirectory . '/' . $package['safeSlug'] . '.zip';
		
		// Filter so that filename can be changed
		//$package = apply_filters( 'wp_update_server_find_package', $package );

		// Find the ID of the slug of the theme with the specified slug
		$theme = get_page_by_path( $package['safeSlug'], ARRAY_A, 'theme' );

		// Exit if no theme found
		if( empty( $theme ) )
			$this->exitWithError( 'No Theme with the specified slug.', 400 );

		/**
		 * @todo Make a new input type on the Media Library, or ability to handle non-images
		 * so that a ZIP file can be selected, and it shows the appropriate selection.
		 * Make a variation on the wp-media-library directive...
		 */
		$package['filename'] = "/Users/phong/Projects/Artdroid/_WWW/wp-content/plugins/wp-updater/packages/artdroid.zip";


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
				'slug'   => get_query_var('update_slug'),
			));
		}
	}
}

$wp_updater = new WP_Updater();














/**
 * Search for the latest version of the requested theme
 * And return the meta-data associated with it.
 */
add_filter( 'wp_update_server_find_package', 'wp_update_server_find_package_filter' );
function wp_update_server_find_package_filter( $package ){

	// Find the ID of the slug of the theme with the specified slug
	$theme = get_page_by_path( $package['safeSlug'], ARRAY_A, 'theme' );

	// Exit if no theme found
	if( empty( $theme ) )
		pw_exit_with_error( 'No Theme with the specified slug.', 400 );

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
		pw_exit_with_error( 'No Theme Version found with the specified slug.', 400 );

	// Specify the link to the latest version of the theme
	$package['details_url'] = get_permalink( $query->posts[0] );

	//pw_log( 'theme_version_permalink', $theme_version_permalink );
	// Return the latest version
	// Get related blog post if available

	/**
	 * @todo Make a new input type on the Media Library, or ability to handle non-images
	 * so that a ZIP file can be selected, and it shows the appropriate selection.
	 * Make a variation on the wp-media-library directive...
	 */

	$package['filename'] = "/Users/phong/Projects/Artdroid/_WWW/wp-content/plugins/wp-updater/packages/artdroid.zip";


	return $package;
}