<?php

class Unified_Breadcrumbs {
	var $version = '2019.0.1';
	var $home_name = null;
	var $home_link = null;
	var $parents = array();
	var $bcargs = array();

	function __construct() {
		add_action( 'after_setup_theme', array( $this, 'after_setup_theme' ) );
		$this->settings_field = 'umw-site-settings';

		$this->convert_genesis_settings();

		add_filter( 'umw-outreach-genesis-customizer-config', array( $this, 'add_customizer_section' ) );
	}

	/**
	 * Attempt to convert the old genesis-settings array to the new settings pattern
     *
     * @access private
     * @since  2019.01
     * @return void
	 */
	private function convert_genesis_settings( $blog=false ) {
		$tmp_settings_field = defined( 'GENESIS_SETTINGS_FIELD' ) ? GENESIS_SETTINGS_FIELD : 'genesis-settings';
	    if ( false === $blog || intval( $GLOBALS['blog_id'] ) === intval( $blog ) ) {
	        $allopts = get_option( $tmp_settings_field, array() );

		    if ( ! array_key_exists( '_breadcrumb_list', $allopts ) || empty( $allopts['_breadcrumb_list'] ) ) {
			    return;
		    }

		    $new = array();
		    foreach ( $allopts['_breadcrumb_list'] as $k => $v ) {
			    $new[ 'breadcrumb-' . $k . '-name' ] = $v['name'];
			    $new[ 'breadcrumb-' . $k . '-url' ]  = $v['url'];
		    }

		    unset( $allopts['_breadcrumb_list'] );

		    if ( function_exists( 'genesis_update_settings' ) ) {
			    genesis_update_settings( $new, $this->settings_field );
		    } else {
		    	update_option( $this->settings_field, $new );
		    }

		    update_option( $tmp_settings_field, $allopts );
	    } else {
	        $allopts = get_blog_option( $blog, $tmp_settings_field, array() );

	        if ( ! array_key_exists( '_breadcrumb_list', $allopts ) ) {
	            return;
            }

	        $new = array();
	        foreach ( $allopts['_breadcrumb_list'] as $k=>$v ) {
		        $new[ 'breadcrumb-' . $k . '-name' ] = $v['name'];
		        $new[ 'breadcrumb-' . $k . '-url' ]  = $v['url'];
            }

	        unset( $allopts['_breadcrumb_list'] );

	        update_blog_option( $blog, $this->settings_field, $new );
	        update_blog_option( $blog, $tmp_settings_field, $allopts );
        }
    }

	function after_setup_theme() {
		/**
		 * This plugin is temporarily limited to Genesis-based themes
		 */
		if ( ! function_exists( 'genesis' ) ) {
			return;
		}

		/**
		 * No point in doing anything if this isn't a multisite install
		 */
		if ( ! is_multisite() ) {
			return;
		}

		$this->home_name = __( 'UMW' );
		if ( defined( 'UMW_IS_ROOT' ) ) {
			if ( is_numeric( UMW_IS_ROOT ) ) {
				$this->home_link = get_blog_option( UMW_IS_ROOT, 'siteurl' );
			} else if ( esc_url( UMW_IS_ROOT ) ) {
				$this->home_link = esc_url( UMW_IS_ROOT );
			}
		}

		add_filter( 'genesis_breadcrumb_args', array( $this, 'breadcrumb_args' ) );
		/*add_action( 'admin_init', array( $this, 'sanitizer_filters' ) );
		add_filter( 'genesis_available_sanitizer_filters', array( $this, 'add_sanitizer_filter' ) );
		add_filter( 'genesis_theme_settings_defaults', array( $this, 'settings_defaults' ) );*/
	}

	function add_sanitizer_filter( $filters = array() ) {
		$filters['umw_breadcrumb_filter'] = array( $this, 'temp_sanitize_setting' );

		return $filters;
	}

	function settings_defaults( $defaults = array() ) {
		$settings['_breadcrumb_list']        = array();
		$settings['_breadcrumb_parent_site'] = 0;

		return $settings;
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
			'umw_breadcrumb_filter',
			$this->settings_field,
			array(
				'_breadcrumb_list',
			)
		);
	}

	function get_option( $key, $blog = false, $default = false ) {
	    if ( '_breadcrumb_list' == $key ) {
	        if ( false === $blog || intval( $GLOBALS['blog_id'] ) === intval( $blog ) ) {
		        $allopts = get_option( GENESIS_SETTINGS_FIELD, array() );
		        if ( array_key_exists( $key, $allopts ) ) {
			        $this->convert_genesis_settings();
		        }

		        $opts = get_option( $this->settings_field, array() );
	        } else {
	            $allopts = get_blog_option( $blog, GENESIS_SETTINGS_FIELD, array() );
	            if ( array_key_exists( $key, $allopts ) ) {
	                $this->convert_genesis_settings( $blog );
                }

	            $opts = get_blog_option( $blog, $this->settings_field, array() );
            }

		    $args = array();
		    for ( $i = 1; $i <= 3; $i ++ ) {
			    $args[ $i ]['name'] = $opts[ 'breadcrumb-' . $i . '-name' ];
			    $args[ $i ]['url']  = $opts[ 'breadcrumb-' . $i . '-url' ];

			    if ( empty( $args[ $i ]['name'] ) && empty( $args[ $i ]['url'] ) ) {
				    unset( $args[ $i ] );
			    }
		    }

            return empty( $args ) ? $default : $args;
	    }

		/**
		 * If we somehow failed to retrieve the options in their new format, look for them in their old format for now
		 */
		if ( empty( $blog ) || intval( $blog ) === $GLOBALS['blog_id'] ) {
			$opt = genesis_get_option( $key );
		} else {
			$opt = get_blog_option( $blog, GENESIS_SETTINGS_FIELD );
			if ( ! is_array( $opt ) || ! array_key_exists( $key, $opt ) ) {
				$opt = $default;
			} else {
				$opt = $opt[ $key ];
			}
		}

		return $opt;
	}

	function breadcrumb_args( $args = array() ) {
		$args['home'] = get_bloginfo( 'name' );

		$this->bcargs = $args;

		if ( defined( 'UMW_IS_ROOT' ) && is_numeric( UMW_IS_ROOT ) && $GLOBALS['blog_id'] == UMW_IS_ROOT ) {
			$pre = '';
		} else {
			$pre = $args['labels']['prefix'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $this->home_link ), $this->home_name );
		}
		/*$pre = $this->append_parents( $pre, $GLOBALS['blog_id'] );*/

		$parents = $this->append_parents( '', $GLOBALS['blog_id'] );

		$pre .= $parents;
		if ( ! empty( $pre ) ) {
			$args['labels']['prefix'] = $pre . $args['sep'];
		}

		return $args;
	}

	function append_parents( $pre = '', $blog_id = null ) {
		$parents = $this->get_option( '_breadcrumb_list' );
		if ( empty( $parents ) || ! is_array( $parents ) ) {
			return $pre;
		}

		foreach ( array_reverse( $parents, true ) as $p ) {
			if ( ! array_key_exists( 'name', $p ) || ! array_key_exists( 'url', $p ) ) {
				continue;
			}

			if ( ! empty( $p['name'] ) && esc_url( $p['url'] ) ) {
				$pre = $this->bcargs['sep'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $p['url'] ), $p['name'] ) . $pre;
			}
		}

		return $pre;
	}

	function temp_sanitize_setting( $val = array() ) {
		if ( empty( $val ) ) {
			return null;
		}

		/*if ( 2 == get_current_user_id() ) {
			print( '<pre><code>' );
			var_dump( $val );
			print( '</code></pre>' );
			wp_die( 'Stop here' );
		}*/

		$rt = array();
		foreach ( $val as $k => $v ) {
			if ( ! empty( $v['name'] ) && esc_url( $v['url'] ) ) {
				$rt[ $k ] = array( 'name' => esc_attr( $v['name'] ), 'url' => esc_url( $v['url'] ) );
			}
		}

		return $rt;
	}

	/**
	 * Add these settings to a new section of the Customizer
	 *
	 * @param array $config the existing customizer configuration
	 *
	 * @access public
	 * @return array the updated customizer configuration
	 * @since  2019.0.1
	 */
	public function add_customizer_section( $config = array() ) {
		$names = array(
			1 => __( 'Top-Level Site %s' ),
			2 => __( 'Second-Level Site %s' ),
			3 => __( 'Third-Level Site %s' )
		);

		$controls = array();
		foreach ( $names as $k => $v ) {
			$controls[ 'breadcrumb-' . $k . '-name' ] = array(
				'label'       => sprintf( $v, 'Name' ),
				'section'     => 'umw_breadcrumb_settings',
				'type'        => 'text',
				'input_attrs' => array(
					'placeholder' => sprintf( $v, 'Name' ),
				),
				'settings'    => array(
					'default' => '',
				),
			);
			$controls[ 'breadcrumb-' . $k . '-url' ]  = array(
				'label'    => sprintf( $v, 'URL' ),
				'section'  => 'umw_breadcrumb_settings',
				'type'     => 'url',
				'settings' => array(
					'default' => '',
				),
			);
		}

		$config['genesis-umw']['sections']['umw_breadcrumb_settings'] = array(
			'active_callback' => '__return_true',
			'title'           => __( 'Unified Breadcrumbs', 'genesis' ),
			'panel'           => 'genesis-umw',
			'controls'        => $controls,
		);

		return $config;
    }
}