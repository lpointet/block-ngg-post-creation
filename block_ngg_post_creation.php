<?php
/*
Plugin Name: Block NGG post creation
Plugin URI: https://github.com/lpointet/block-ngg-post-creation/
Description: Blocks post creation for all users when NextGen Gallery is activated on the site
Author: Lionel Pointet
Version: 1.0
*/

if( !class_exists('BPC') ) {

	class BPC {
		/**
		 * Register needed hooks.
		 */
		public static function hooks() {
			add_action( 'init', array( __CLASS__, 'init' ) );
		}

		/**
		 * Run at the 'init' hook.
		 *
		 * This function will disable post creation ability for all post types
		 */
		public static function init() {
			// Don't use is_plugin_active because :
			//  1. it doesn't exist on front
			//  2. it implies that we know the plugin directory

			if( !class_exists(  'C_NextGEN_Bootstrap' ) )
				return;

			// NextGen Gallery plugin is active
			// => disable post creation for each registered post type

			$post_types = apply_filters( 'bpc_post_types', get_post_types( array(
				'public' => true,
			) ) );

			foreach( $post_types as $post_type ) {
				// Little tweak: for media post type, use 'map_meta_cap' filter instead
				if( 'media' === $post_type ) {
					add_filter( 'map_meta_cap', array( __CLASS__, 'map_meta_cap' ), 10, 2 );
					continue;
				}

				$post_type_object = get_post_type_object( $post_type );
				$post_type_object->cap->create_posts = 'do_not_allow';
			}
		}

		/**
		 * Map meta capabilities to primitive capabilities.
		 *
		 * This function is used for 'media' post type, for which 'upload_files'
		 * capability is used directly to check the ability to create a media.
		 * This will return 'do_not_allow' cap if needed.
		 *
		 * @param string $caps Actual capabilities for meta capability.
		 * @param string $cap Capability name.
		 * @return array Actual capabilities for meta capability.
		 */
		public static function map_meta_cap( $caps, $cap ) {
			if( 'upload_files' !== $cap )
				return $caps;

			return array( 'do_not_allow' );
		}
	}
}

BPC::hooks();