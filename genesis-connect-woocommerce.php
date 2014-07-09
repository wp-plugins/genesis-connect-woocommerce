<?php
/*
Plugin Name: Genesis Connect for WooCommerce
Plugin URI: http://www.studiopress.com/plugins/genesis-connect-woocommerce
Version: 0.9.6
Author: StudioPress
Author URI: http://www.studiopress.com/
Description: Allows you to seamlessly integrate WooCommerce with the Genesis Framework and Genesis child themes.

License: GNU General Public License v2.0 (or later)
License URI: http://www.opensource.org/licenses/gpl-license.php

Special thanks to Ade Walker (http://www.studiograsshopper.ch/) for his contributions to this plugin.
*/



/** Define the Genesis Connect for WooCommerce constants */
define( 'GCW_TEMPLATE_DIR',	dirname( __FILE__ ) . '/templates' );
define( 'GCW_LIB_DIR',		dirname( __FILE__ ) . '/lib');
define( 'GCW_SP_DIR',		dirname( __FILE__ ) . '/sp-plugins-integration' );
define( 'GCW_GEN_MIN_VER',	'2.0' );



register_activation_hook( __FILE__, 'gencwooc_activation' );
/**
 * Check the environment when plugin is activated
 *
 * Requirements:
 * - WooCommerce needs to be installed and activated
 * - Genesis (min version) must be current theme 'Template'
 *
 * Note: register_activation_hook() isn't run after auto or manual upgrade, only on activation
 *
 * @since 0.9.0
 * @updated 0.9.6
 */
function gencwooc_activation() {

	$message = '';
	
	// Check that WooC is installed
	// @TODO Check WooC version
	if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		$message .= sprintf( '<br /><br />%s', __( 'Install and activate the WooCommerce plugin.', 'gencwooc') );
	}
	
	// Check that Genesis min version is installed
	if ( version_compare( PARENT_THEME_VERSION, GCW_GEN_MIN_VER, '<' ) ) {
	
		$message .= sprintf( __( '<br /><br />Install and activate <a href="%s">Genesis Framework %s</a> or greater', 'gencwooc' ), 'http://my.studiopress.com/downloads/genesis', GCW_GEN_MIN_VER );
	
	}

	
	// Display messages if necessary
	if ( ! empty( $message ) ) {

		deactivate_plugins( plugin_basename( __FILE__ ) ); // Deactivate ourself

		$message = __( 'Sorry! In order to use the Genesis Connect for WooCommerce plugin you need to do the following:', 'gencwooc' ) . $message;

		wp_die( $message, 'Genesis Connect for WooCommerce Plugin', array( 'back_link' => true ) );

	}
}


add_action( 'after_setup_theme', 'gencwooc_setup' );
/**
 * Setup GCW
 *
 * Checks whether WooCommerce is active, then checks if current parent theme
 * is Genesis minimum version. Once past these checks, loads the necessary
 * files, actions and filters for the plugin to do its thing.
 *
 * Note: genesis_connect_woocommerce theme support requirement now dropped from 1.0.0
 *
 * @since 0.9.0
 * @updated 0.9.6
 */
function gencwooc_setup() {

	/** Fail silently if WooCommerce is not activated */
	// @TODO Check WooC version
	if ( ! in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) )
		return;


	/** Fail silently if Genesis min version isn't installed */
	// 
	if ( version_compare( PARENT_THEME_VERSION, GCW_GEN_MIN_VER, '<' ) )
		return false;
	

	
	/** Environment is OK, let's go! */

	global $woocommerce;

	/** Load GCW files */
	require_once( GCW_LIB_DIR . '/template-loader.php' );

	/** Load modified Genesis breadcrumb filters and callbacks */
	if ( ! current_theme_supports( 'gencwooc-woo-breadcrumbs') )
		require_once( GCW_LIB_DIR . '/breadcrumb.php' );
		
	/** Add GCW support for backwards compatibility only (since 0.9.6) */
	add_theme_support( 'genesis-connect-woocommerce' );
	
	/** Ensure WooCommerce 2.0+ compatibility */
	add_theme_support( 'woocommerce' );

	/** Add Genesis Layout and SEO options to Product edit screen */
	add_post_type_support( 'product', array( 'genesis-layouts', 'genesis-seo' ) );

	/** Add Studiopress plugins support */
	add_post_type_support( 'product', array( 'genesis-simple-sidebars', 'genesis-simple-menus' ) );

	/** Take control of shop template loading */
	remove_filter( 'template_include', array( &$woocommerce, 'template_loader' ) );
	add_filter( 'template_include', 'gencwooc_template_loader', 20 );

	/** Integration - Genesis Simple Sidebars */
	if ( in_array( 'genesis-simple-sidebars/plugin.php', get_option( 'active_plugins' ) ) )
		require_once( GCW_SP_DIR . '/genesis-simple-sidebars.php' );

	/** Integration - Genesis Simple Menus */
	if ( in_array( 'genesis-simple-menus/simple-menu.php', get_option( 'active_plugins' ) ) )
		require_once( GCW_SP_DIR . '/genesis-simple-menus.php' );

}