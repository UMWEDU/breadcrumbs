<?php
class Unified_Breadcrumbs {
	var $version = '0.1.2';
	var $home_name = null;
	var $home_link = null;
	var $parents = array();
	var $bcargs = array();
	
	function __construct() {
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		$this->settings_field = 'genesis-settings';
	}
	
	function after_setup_theme() {
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
		add_action( 'genesis_theme_settings_metaboxes', array( $this, 'metaboxes' ) );
		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
		add_filter( 'genesis_theme_settings_defaults', array( $this, 'settings_defaults' ) );
	}
	
	function settings_defaults( $defaults=array() ) {
		$settings['_breadcrumb_list'] = array();
		$settings['_breadcrumb_parent_site'] = 0;
		return $settings;
	}
	
	function metaboxes( $pagehook ) {
		add_meta_box( 'genesis-theme-settings-unified-breadcrumbs', __( 'Unified Breadcrumbs', 'genesis' ), array( $this, 'breadcrumbs_box' ), $pagehook, 'main' );
	}
	
	function sanitizer_filters() {
		genesis_add_option_filter(
			'absint',
			$this->settings_field,
			array(
				'_breadcrumb_parent_site',
			)
		);
		genesis_add_option_filter( 
			array( $this, 'temp_sanitize_setting' ), 
			$this->settings_field, 
			array( 
				'_breadcrumb_list'
			)
		);
	}
	
	function get_option( $key, $blog=false, $default=false ) {
		if ( empty( $blog ) || intval( $blog ) === $GLOBALS['blog_id'] ) {
			$opt = genesis_get_option( $key );
		} else {
			$opt = get_blog_option( $blog, GENESIS_SETTINGS_FIELD );
			if ( ! is_array( $opt ) || ! array_key_exists( $key, $opt ) )
				$opt = $default;
			else
				$opt = $opt[$key];
		}
		
		return $opt;
	}
	
	function breadcrumb_args( $args=array() ) {
		$args['home'] = get_bloginfo( 'name' );
		
		$this->bcargs = $args;
		
		$pre = $args['labels']['prefix'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $this->home_link ), $this->home_name );
		/*$pre = $this->append_parents( $pre, $GLOBALS['blog_id'] );*/
		
		$parents = $this->append_parents( '', $GLOBALS['blog_id'] );
		
		$pre .= $parents;
		
		$args['labels']['prefix'] = $pre . $args['sep'];
		
		return $args;
	}
	
	function append_parents( $pre='', $blog_id=null ) {
		$parent = $this->get_option( '_breadcrumb_parent_site', $blog_id, false );
		if ( false === $parent || ! is_numeric( $parent ) ) {
			error_log( '[UBC Debug]: The parent site was empty' );
			return $pre;
		}
		
		if ( in_array( $parent, $this->parents ) ) {
			error_log( '[UBC Debug]: The parent site was found in the array of used parents' );
			return $pre;
		}
		
		$this->parents[] = $parent;
		$p = get_blog_details( $parent );
		$pre = $this->bcargs['sep'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( sprintf( '//%1$s%2$s', $p->domain, $p->path ) ), $p->blogname ) . $pre;
		error_log( '[UBC Debug]: Just added the details for the blog with an ID of ' . $parent . ' to the breadcrumbs' );
		error_log( '[UBC Debug]: At this point, $pre looks like: ' . $pre );
		
		return $this->append_parents( $pre, $parent );
	}
	
	function get_site_list() {
		if ( function_exists( 'get_mnetwork_transient' ) ) {
			$blogs = get_mnetwork_transient( 'unified-bc-site-list-' . $this->version );
			if ( false !== $blogs )
				return $blogs;
		}
		global $wpdb;
		$local_blog_list = $wpdb->get_results( $wpdb->prepare( "SELECT blog_id, domain, path, public FROM {$wpdb->blogs} WHERE public >= %d AND archived=%d AND mature=%d AND spam=%d AND deleted=%d ORDER BY domain, path", 0, 0, 0, 0, 0 ) );
		foreach( $local_blog_list as $b ) {
			$b->blogname = get_blog_option( $b->blog_id, 'blogname' );
		}
		$request = wp_remote_get( 'http://academics.umw.edu/feed/site-feed.json' );
		if ( 200 == wp_remote_retrieve_response_code( $request ) ) {
			$ext_blog_list = @json_decode( wp_remote_retrieve_body( $request ) );
		}
		if ( ! isset( $ext_blog_list ) || empty( $ext_blog_list ) || ! is_array( $ext_blog_list ) ) {
			$ext_blog_list = array();
		}
		$tmp = array_merge( $local_blog_list, $ext_blog_list );
		$blogs = array();
		foreach( $tmp as $blog ) {
			$blogs[$blog->domain . $blog->path] = $blog->blogname . ' [' . esc_url( $blog->domain . $blog->path ) . ']';
		}
		if ( function_exists( 'set_mnetwork_transient' ) ) {
			set_mnetwork_transient( 'unified-bc-site-list-' . $this->version, $blogs, DAY_IN_SECONDS );
		}
		return $blogs;
	}
	
	function get_field_id( $name ) {
		return sprintf( '%s[%s]', GENESIS_SETTINGS_FIELD, $name );
	}
	
	function field_id( $name ) {
		echo $this->get_field_id( $name );
	}
	
	function get_field_name( $name ) {
		return sprintf( '%s[%s]', GENESIS_SETTINGS_FIELD, $name );
	}
	
	function field_name( $name ) {
		echo $this->get_field_name( $name );
	}
	
	function breadcrumbs_box() {
		return $this->temp_breadcrumbs_box();
		
		$current = $this->get_option( '_breadcrumb_parent_site' );
		$sites = $this->get_site_list();
		
?>
<p>
	<label for="<?php $this->field_id( '_breadcrumb_parent_site' ) ?>">
		<?php _e( 'Which site is the parent of this site?' ) ?>
	</label>
	<select name="<?php $this->field_name( '_breadcrumb_parent_site' ) ?>" id="<?php $this->field_id( '_breadcrumb_parent_site' ) ?>">
		<option value="0"<?php selected( $current, 0 ) ?>><?php _e( 'None' ) ?></option>
<?php
		foreach ( $sites as $id => $url ) {
?>
		<option value="<?php echo $id ?>"<?php selected( $current, $id ) ?>><?php echo $url ?></option>
<?php
		}
?>
	</select>
</p>
<p><span class="description"><?php _e( 'Please select the address of the site that serves as the parent of this site.', 'genesis' ); ?></span></p>
<?php
	}
	
	function temp_breadcrumbs_box() {
		$current = $this->get_option( '_breadcrumb_list' );
		$names = array(
			1 => __( 'Top-Level Site %s' ), 
			2 => __( 'Second-Level Site %s' ), 
			3 => __( 'Third-Level Site %s' )
		);
		
		foreach ( $names as $i=>$n ) {
?>
<p>
	<label for="<?php $this->field_id( sprintf( '_breadcrumb_list[%d][name]', $i ) ) ?>">
		<?php printf( $n, 'Name' ) ?>
	</label>
	<input type="text" name="<?php $this->field_name( sprintf( '_breadcrumb_list[%d][name]', $i ) ) ?>" id="<?php $this->field_id( sprintf( '_breadcrumb_list[%d][name]', $i ) ) ?>" value="<?php echo $current[$i]['name'] ?>"/>
</p>
<p>
	<label for="<?php $this->field_id( sprintf( '_breadcrumb_list[%d][url]', $i ) ) ?>">
		<?php printf( $n, 'URL' ) ?>
	</label>
	<input type="url" name="<?php $this->field_name( sprintf( '_breadcrumb_list[%d][url]', $i ) ) ?>" id="<?php $this->field_id( sprintf( '_breadcrumb_list[%d][url]', $i ) ) ?>" value="<?php echo $current[$i]['url'] ?>"/>
</p>
<?php
		}
	}
	
	function temp_sanitize_setting( $val=array() ) {
		return $val;
	}
}