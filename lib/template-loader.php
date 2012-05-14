<?php
/**
 * These functions manage loading of templates for WooCommerce
 *
 * @package genesis_connect_woocommerce
 * @version 0.9.3
 *
 * @since 0.9.0
 */

/**
 * Prevent direct access to this file
 */
if ( ! defined( 'ABSPATH' ) )
	exit( _( 'Sorry, you are not allowed to access this file directly.' ) );



/**
 * Load the Genesis-fied templates, instead of the WooCommerce defaults.
 *
 * Hooked to 'template_include' filter
 *
 * This template loader determines which template file will be used for the requested page, and uses the
 * following hierarchy to find the template:
 * 1. First looks in the child theme's 'woocommerce' folder.
 * 2. If no template found, falls back to GCW's templates.
 *
 * For taxonomy templates, first looks in child theme's 'woocommerce' folder and searches for term specific template,
 * then taxonomy specific template, then taxonomy.php. If no template found, falls back to GCW's taxonomy.php.
 *
 * GCW provides three templates in the plugin's 'templates' directory:
 * - single-product.php
 * - archive-product.php
 * - taxonomy.php
 *
 * Users can override GCW templates by placing their own templates in their child theme's 'woocommerce' folder.
 * The 'woocommerce' folder must be a folder in the child theme's root directory, eg themes/my-child-theme/woocommerce
 * Permitted user templates (as per WP Template Hierarchy) are:
 * - single-product.php
 * - archive-product.php
 * - taxonomy-{taxonomy-name}-{term-name}.php
 * - taxonomy-{taxonomy-name}.php
 * - taxonomy.php
 *
 * Note that in the case of taxonomy templates, this function accommodates ALL taxonomies registered to the
 * 'product' custom post type. This means that it will cater for users' own custom taxonomies as well as WooC's.
 *
 * @since 0.9.0
 *
 * @param string $template Template file as per template hierarchy
 * @return string $template Specific GCW template if a product page (single or archive)
 * or a product taxonomy term, or returns original template
 */
function gencwooc_template_loader( $template ) {


	if ( is_single() && 'product' == get_post_type() ) {

		$template = locate_template( array( 'woocommerce/single-product.php' ) );

		if ( ! $template )
			$template = GCW_TEMPLATE_DIR . '/single-product.php';

	}
	elseif ( is_post_type_archive( 'product' ) ||  is_page( get_option( 'woocommerce_shop_page_id' ) ) ) {

		$template = locate_template( array( 'woocommerce/archive-product.php' ) );

		if ( ! $template )
			$template = GCW_TEMPLATE_DIR . '/archive-product.php';

	}
	elseif ( is_tax() ) {

		$term = get_query_var( 'term' );

		$tax = get_query_var( 'taxonomy' );

		/** Get an array of all relevant taxonomies */
		$taxonomies = get_object_taxonomies( 'product', 'names' );

		if ( in_array( $tax, $taxonomies ) ) {

			$tax = sanitize_title( $tax );
			$term = sanitize_title( $term );

			$templates = array(
				'woocommerce/taxonomy-'.$tax.'-'.$term.'.php',
				'woocommerce/taxonomy-'.$tax.'.php',
				'woocommerce/taxonomy.php',
			);

			$template = locate_template( $templates );

			/** Fallback to GCW template */
			if ( ! $template )
				$template = GCW_TEMPLATE_DIR . '/taxonomy.php';
		}
	}

	return $template;

}



/**
 * Shop Loop 'template part' loader
 *
 * Function looks for loop-shop.php in child theme's 'woocommerce' folder. If it doesn't exist,
 * loads the default WooCommerce loop-shop.php file.
 *
 * Note: loop-shop.php is used to display products on the archive and taxonomy pages
 *
 * Users can override the default WooCommerce loop-shop.php by placing their own template (named loop-shop.php) in
 * their child theme's 'woocommerce' folder. The'woocommerce' folder must be a folder in the
 * child theme's root directory, eg themes/my-child-theme/woocommerce.
 * It is recommended to use woocommerce/templates/loop-shop.php as the starting point of
 * any custom loop template.
 *
 * Based on woocommerce_get_template_part()
 *
 * Note: updated v0.9.3 to reflect changes to woocommerce_get_template_part() introduced in
 * WooC v1.4+
 * Effectively, this function is a clone of woocommerce_get_template_part()
 * and is ONLY RETAINED FOR BACKWARDS COMPATIBILITY for pre-0.9.2 custom templates which
 * may use this function.
 *
 * @since 0.9.0
 * @global object $woocommerce WooCommerce instance
 */
 function gencwooc_get_template_part( $slug, $name = '' ) {

	global $woocommerce;
	$template = '';
	
	// Look in yourtheme/slug-name.php and yourtheme/woocommerce/slug-name.php
	if ( $name ) 
		$template = locate_template( array ( "{$slug}-{$name}.php", "{$woocommerce->template_url}{$slug}-{$name}.php" ) );
	
	// Get default slug-name.php
	if ( !$template && $name && file_exists( $woocommerce->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = $woocommerce->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/woocommerce/slug.php
	if ( !$template ) 
		$template = locate_template( array ( "{$slug}.php", "{$woocommerce->template_url}{$slug}.php" ) );

	if ( $template ) 
		load_template( $template, false );
}