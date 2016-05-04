<?php

add_action( 'init', 'pw_updater_post_types' );
function pw_updater_post_types(){

	///// POST TYPE : THEME /////
	$theme_labels = array(
		'name' => "Themes",
		'singular_name' => "Theme",
		'add_new' => 'New Theme',
		'add_new_item' => 'New Theme',
		'edit_item' => 'Edit Theme',
		'new_item' => 'New Theme',
		'all_items' => 'All Themes',
		'view_item' => 'View Theme',
		'search_items' => 'Search Theme',
		'not_found' =>  'Nothing found',
		'not_found_in_trash' => 'No Themes found in Trash', 
		'parent_item_colon' => '',
		'menu_name' => 'Themes'
	);
	
	$theme_vars = array(
		'labels' => $theme_labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => array( 'slug' => 'theme' ),
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => 7,
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author' ),
		'taxonomies' => array(),
		'menu_icon' => 'dashicons-welcome-view-site',
	);
	register_post_type( 'theme', $theme_vars );


	///// POST TYPE : THEME VERSION /////
	$theme_labels = array(
		'name' => "Theme Version",
		'singular_name' => "Theme Version",
		'add_new' => 'New Theme Version',
		'add_new_item' => 'New Theme Version',
		'edit_item' => 'Edit Theme Version',
		'new_item' => 'New Theme Version',
		'all_items' => 'All Theme Versions',
		'view_item' => 'View Theme  Version',
		'search_items' => 'Search Theme Versions',
		'not_found' =>  'Nothing found',
		'not_found_in_trash' => 'No Theme Versions found in Trash', 
		'parent_item_colon' => '',
		'menu_name' => 'Theme Versions'
	);
	
	$theme_vars = array(
		'labels' => $theme_labels,
		'public' => false,
		'publicly_queryable' => false,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => false,
		'rewrite' => array( 'slug' => 'version' ),
		'capability_type' => 'post',
		'has_archive' => false, 
		'hierarchical' => false,
		'menu_position' => 7,
		'supports' => array( 'title' ),
		'taxonomies' => array(),
		'menu_icon' => 'dashicons-album',
	);
	register_post_type( 'theme_version', $theme_vars );

}

/**
 * Add Postwold Metaboxes
 */
add_action('init', 'pw_updater_add_metaboxes' );
function pw_updater_add_metaboxes(){
	/**
	 * Add metabox on theme version to select parent theme.
	 */
	pw_add_metabox_post_parent( array(
		'labels'	=>	array(
			'title'		=>	'Theme',
			'search'	=>	'Search themes...'
			),
		'post_types' 	=> array( 'theme_version' ),
		'query'	=>	array(
			'post_type'			=>	'theme',
			),
		));

	/**
	 * Add metabox on theme version to select theme files / details.
	 */
	pw_add_metabox_wp_postmeta( array(
		'post_types'	=>	array( 'theme_version' ),
		'metabox'		=>	array(
			'title'		=>	'Theme Version',
			'context'	=>	'normal',
			),
		'fields'	=>	array(
			array(
				'type'				=>	'file-id',
				'label'				=>	'Theme Version File',
				'description'		=>	'A .zip file containing the theme.',
				'meta_key'			=>	'theme_file',
				'icon'				=>	'pwi-file',
				),
			
			array(
				'type'				=>	'text-input',
				'label'				=>	'Details URL',
				'description'		=>	'URL for the details page for this version.',
				'meta_key'			=>	'theme_details_url',
				'icon'				=>	'pwi-link',
				),
			
			
			),

		));
}