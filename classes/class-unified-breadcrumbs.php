<?php
class Unified_Breadcrumbs {
	var $version = '0.1a';
	var $home_name = null;
	var $home_link = null;
	var $parents = array();
	var $bcargs = array();
	
	function __construct() {
		/**
		 * This plugin is temporarily limited to Genesis-based themes
		 */
		if ( ! function_exists( 'genesis' ) )
			return;
		
		/**
		 * No point in doing anything if this isn't a multisite install
		 */
		if ( ! is_multisite() )
			return;
		
		$this->home_name = __( 'UMW' );
		$this->home_link = 'http://www.umw.edu/';
		
		add_filter( 'genesis_breadcrumb_args', array( $this, 'breadcrumb_args' ) );
	}
	
	function breadcrumb_args( $args=array() ) {
		$this->bcargs = $args;
		$pre = $args['labels']['prefix'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', $this->home_link, $this->home_name );
		$pre = $this->append_parents( $pre, $GLOBALS['blog_id'] );
		
		$args['labels']['prefix'] .= $pre;
	}
	
	function append_parents( $pre='', $blog_id=null ) {
		$parent = get_blog_option( $blog_id, '_breadcrumb_parent_site', false );
		if ( in_array( $parent, $this->parents ) )
			return $pre;
		
		if ( false === $parent || ! is_numeric( $parent ) )
			return $pre;
		
		$this->parents[] = $parent;
		$p = get_blog_details( $parent );
		$pre .= $this->bcargs['sep'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', sprintf( '//%1$s%2$s', $p->domain, $p->path ), $p->blog_name );
		
		return $this->append_parents( $pre, $parent );
	}
}