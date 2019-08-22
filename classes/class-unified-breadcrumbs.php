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
		add_filter( 'umw-site-settings-sanitized', array( $this, 'sanitize_settings' ), 10, 2 );
		add_filter( 'umw-outreach-settings-defaults', array( $this, 'settings_defaults' ) );
	}

	/**
	 * Attempt to convert the old genesis-settings array to the new settings pattern
     *
     * @access private
     * @since  2019.01
     * @return void
	 */
	private function convert_genesis_settings( $blog=false ) {
		$old_settings_field = defined( 'GENESIS_SETTINGS_FIELD' ) ? GENESIS_SETTINGS_FIELD : 'genesis-settings';

		if ( empty( $blog ) || intval( $blog ) === intval( $GLOBALS['blog_id'] ) ) {
			$oldopts = get_option( $old_settings_field, array() );
			$newopts = get_option( $this->settings_field, array() );
			if ( empty( $oldopts ) || ! array_key_exists( '_breadcrumb_list', $oldopts ) ) {
				return;
			}

			if ( is_array( $oldopts['_breadcrumb_list'] ) ) {
				foreach ( $oldopts['_breadcrumb_list'] as $k => $v ) {
					$newopts[ 'breadcrumb-' . $k . '-name' ] = $v['name'];
					$newopts[ 'breadcrumb-' . $k . '-url' ]  = $v['url'];
				}
			}

			unset( $oldopts['_breadcrumb_list'] );
			update_option( $old_settings_field, $oldopts );
			update_option( $this->settings_field, $newopts );

			return;
		} else {
			$oldopts = get_blog_option( $blog, $old_settings_field, array() );
			$newopts = get_blog_option( $blog, $this->settings_field, array() );
			if ( empty( $oldopts ) || ! array_key_exists( '_breadcrumb_list', $oldopts ) ) {
				return;
			}

			if ( is_array( $oldopts['_breadcrumb_list'] ) ) {
				foreach ( $oldopts['_breadcrumb_list'] as $k => $v ) {
					$newopts[ 'breadcrumb-' . $k . '-name' ] = $v['name'];
					$newopts[ 'breadcrumb-' . $k . '-url' ]  = $v['url'];
				}
			}

			unset( $oldopts['_breadcrumb_list'] );
			update_blog_option( $blog, $old_settings_field, $oldopts );
			update_blog_option( $blog, $this->settings_field, $newopts );

			return;
		}
    }

	/**
	 * Sanitize our breadcrumbs settings
	 * @param $settings array the existing settings that have already been sanitized
	 * @param $values array the form values submitted
	 *
	 * @access public
	 * @since  2019.08
	 * @return array the sanitized options
	 */
    public function sanitize_settings( $settings, $values ) {
    	for ( $i=1; $i<=3; $i++ ) {
    		$settings['breadcrumb-' . $i . '-name'] = empty( $values['breadcrumb-' . $i . '-name'] ) ? null : sanitize_text_field( $values['breadcrumb-' . $i . '-name'] );
    		$settings['breadcrumb-' . $i . '-url'] = esc_url( $values['breadcrumb-' . $i . '-url'] ) ? esc_url_raw( $values['breadcrumb-' . $i . '-url'] ) : null;
	    }

		return $settings;
    }

	/**
	 * Add the default settings for this plugin to the list of default settings for UMW Outreach
	 * @param array $settings
	 *
	 * @access public
	 * @since  2019.08
	 * @return array the updated list of default settings
	 */
    public function settings_defaults( $settings=array() ) {
    	for ( $i=1; $i<=3; $i++ ) {
    		$settings['breadcrumb-' . $i . '-name'] = null;
    		$settings['breadcrumb-' . $i . '-url'] = null;
	    }

    	return $settings;
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
	}

	/**
	 * Retrieve and organize the breadcrumb settings
	 *
	 * @access public
	 * @since  2019.08
	 * @return array the organized array of settings
	 */
	public function get_settings() {
    	$opts = get_option( $this->settings_field, array() );

		$args = array();
		for ( $i = 1; $i <= 3; $i ++ ) {
			if ( array_key_exists( 'breadcrumb-' . $i . '-name', $opts ) && ! empty( $opts['breadcrumb-' . $i . '-name'] ) ) {
				$args['breadcrumb-' . $i . '-name'] = $opts['breadcrumb-' . $i . '-name'];
				$args['breadcrumb-' . $i . '-url'] = $opts['breadcrumb-' . $i . '-url'];
			}
		}

		return empty( $args ) ? array() : $args;
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
    	$parents = $this->get_settings();
		if ( empty( $parents ) || ! is_array( $parents ) ) {
			return $pre;
		}

		for ( $i=3; $i>=1; $i-- ) {
			if ( array_key_exists( 'breadcrumb-' . $i . '-name', $parents ) && ! empty( $parents['breadcrumb-' . $i . '-name'] ) ) {
				$pre = $this->bcargs['sep'] . sprintf( '<a href="%1$s" title="%2$s">%2$s</a>', esc_url( $parents['breadcrumb-' . $i . '-url'] ), $parents['breadcrumb-' . $i . '-name'] ) . $pre;
			}
		}

		return $pre;
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