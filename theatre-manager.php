<?php
/**
 * Plugin Name: Theatre Manager
 * Plugin URI: https://github.com/Elleo/theatre-manager
 * Description: Handles ticketing and room hire for theatres based on Woocommerce. 
 * Version: 0.1.0
 * Author: Mike Sheldon
 * Author URI: https://mikeasoft.com
 * Developer: Mike Sheldon
 * Developer URI: https://mikeasoft.com
 *
 * Text Domain: theatre-manager
 * Domain Path: /languages
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce\Admin
 */

/**
 * Register the JS.
 */
function add_extension_register_script() {
	if ( ! class_exists( 'Automattic\WooCommerce\Admin\PageController' ) || ! \Automattic\WooCommerce\Admin\PageController::is_admin_or_embed_page() ) {
		return;
	}

	$script_path       = '/build/index.js';
	$script_asset_path = dirname( __FILE__ ) . '/build/index.asset.php';
	$script_asset      = file_exists( $script_asset_path )
		? require( $script_asset_path )
		: array( 'dependencies' => array(), 'version' => filemtime( $script_path ) );
	$script_url = plugins_url( $script_path, __FILE__ );

	wp_register_script(
		'theatre-manager',
		$script_url,
		$script_asset['dependencies'],
		$script_asset['version'],
		true
	);

	wp_register_style(
		'theatre-manager',
		plugins_url( '/build/index.css', __FILE__ ),
		// Add any dependencies styles may have, such as wp-components.
		array(),
		filemtime( dirname( __FILE__ ) . '/build/index.css' )
	);

	wp_enqueue_script( 'theatre-manager' );
	wp_enqueue_style( 'theatre-manager' );
}

add_action( 'admin_enqueue_scripts', 'add_extension_register_script' );
