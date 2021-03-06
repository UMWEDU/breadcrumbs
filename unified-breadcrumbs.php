<?php
/**
 * Plugin Name: Unified Breadcrumbs
 * Description: Creates a unified breadcrumb hierarchy throughout a multisite or multi-network environment
 * Version: 2019.0.1
 * Author: cgrymala
 * License: GPL2
 */

if ( ! class_exists( 'Unified_Breadcrumbs' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . '/classes/class-unified-breadcrumbs.php' );
	function inst_unified_breadcrumb_obj() {
		global $unified_breadcrumb_obj;
		$unified_breadcrumb_obj = new Unified_Breadcrumbs;
	}
	add_action( 'plugins_loaded', 'inst_unified_breadcrumb_obj' );
}