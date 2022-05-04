<?php
/*
Plugin Name: SMTP by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/smtp/
Description: Configure SMTP server to receive email messages from WordPress to Gmail, Yahoo, Hotmail and other services.
Author: BestWebSoft
Text Domain: bws-smtp
Domain Path: /languages
Version: 1.1.8
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2021  BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! function_exists( 'bwssmtp_dashboard_menu' ) ) {
	function bwssmtp_dashboard_menu() {
		$settings = add_menu_page( __( 'SMTP Settings', 'bws-smtp' ), 'SMTP', 'manage_options', 'bwssmtp_settings', 'bwssmtp_settings_page', 'none' );
		add_submenu_page( 'bwssmtp_settings', __( 'SMTP Settings', 'bws-smtp' ), __( 'Settings', 'bws-smtp' ), 'manage_options', 'bwssmtp_settings', 'bwssmtp_settings_page' );
		add_submenu_page( 'bwssmtp_settings', 'BWS Panel', 'BWS Panel', 'manage_options', 'smtp-bws-panel', 'bws_add_menu_render' );
		add_action( 'load-' . $settings, 'bwssmtp_screen_options' );
	}
}

/**
 * Internationalization
 */
if ( ! function_exists( 'bwssmtp_plugins_loaded' ) ) {
	function bwssmtp_plugins_loaded() {
		load_plugin_textdomain( 'bws-smtp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/* Plugin initialization. */
if ( ! function_exists ( 'bwssmtp_init' ) ) {
	function bwssmtp_init() {
		global $bwssmtp_plugin_info;

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $bwssmtp_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$bwssmtp_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version  */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $bwssmtp_plugin_info, '4.5' );
	}
}

/* Plugin initialization in the Dashboard. */
if ( ! function_exists( 'bwssmtp_admin_init' ) ) {
	function bwssmtp_admin_init() {
		global $bws_plugin_info, $bwssmtp_plugin_info;

		if ( empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '185', 'version' => $bwssmtp_plugin_info['Version'] );

		/* Call default options function */
		if ( isset( $_GET['page'] ) && 'bwssmtp_settings' == $_GET['page'] )
			bwssmtp_register_settings();
	}
}

/* Plugin activate */
if ( ! function_exists( 'bwssmtp_plugin_activate' ) ) {
	function bwssmtp_plugin_activate() {
		/* registering uninstall hook */
		if ( is_multisite() ) {
			switch_to_blog( 1 );
			register_uninstall_hook( __FILE__, 'bwssmtp_uninstall' );
			restore_current_blog();
		} else {
			register_uninstall_hook( __FILE__, 'bwssmtp_uninstall' );
		}
	}
}

if ( ! function_exists( 'bwssmtp_get_options_default' ) ) {
	function bwssmtp_get_options_default() {
		global $bwssmtp_plugin_info;

		$sitename = strtolower( $_SERVER['SERVER_NAME'] );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		$from_email = 'wordpress@' . $sitename;

		$default_options = array(
			'plugin_option_version' 	=> $bwssmtp_plugin_info['Version'],
			'use_plugin_settings_from'	=> 1,
			'confirmed'					=> 0,
			'SMTP'						=> array(
				'from_email'		=> $from_email,
				'from_name'			=> get_bloginfo( 'name' ),
				'host'				=> 'localhost',
				'port'				=> 25,
				'secure'			=> 'none',
				'authentication'	=> 0,
				'username'			=> '',
				'password'			=> ''
			),
			'suggest_feature_banner'	=> 1
		);

		return $default_options;
	}
}

if ( ! function_exists( 'bwssmtp_register_settings' ) ) {
	function bwssmtp_register_settings() {
	    global $bwssmtp_options, $bwssmtp_plugin_info;

		if ( ! get_option( 'bwssmtp_options' ) )
			add_option( 'bwssmtp_options', bwssmtp_get_options_default() );

		$bwssmtp_options = get_option( 'bwssmtp_options' );

		if ( ! isset( $bwssmtp_options['plugin_option_version'] ) || $bwssmtp_options['plugin_option_version'] != $bwssmtp_plugin_info['Version'] ) {
			bwssmtp_plugin_activate();
			$bwssmtp_options = array_merge( bwssmtp_get_options_default(), $bwssmtp_options );
			$bwssmtp_options['plugin_option_version'] = $bwssmtp_plugin_info['Version'];
			update_option( 'bwssmtp_options', $bwssmtp_options );
		}
	}
}

/* Add script and styles to the dashboard. */
if ( ! function_exists( 'bwssmtp_dashboard_script_styles' ) ) {
	function bwssmtp_dashboard_script_styles() {
		wp_enqueue_style( 'bwssmtp_icon', plugins_url( 'css/icon.css', __FILE__ ) );
		if ( isset( $_GET['page'] ) && 'bwssmtp_settings' == $_GET['page'] ) {
			wp_enqueue_style( 'bwssmtp_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'bwssmtp_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
			bws_enqueue_settings_scripts();
		}
	}
}

if ( !function_exists( 'bwssmtp_return_bytes' ) ) {
	function bwssmtp_return_bytes( $size ) {
		if ( false == $size ) {
			return false;
		}
		$latter = substr( $size, -1 );
		$upload_filesize = substr( $size, 0, strlen( $size ) - 1 );

		switch ( strtoupper( $latter )) {
			case 'P':
				$upload_filesize *= 1024;
			case 'T':
				$upload_filesize *= 1024;
			case 'G':
				$upload_filesize *= 1024;
			case 'M':
				$upload_filesize *= 1024;
			case 'K':
				$upload_filesize *= 1024;
				break;
		}
		return $upload_filesize;
	}
}

if ( ! function_exists( 'bwssmtp_settings_page' ) ) {
    function bwssmtp_settings_page() {
        if ( ! class_exists( 'Bws_Settings_Tabs' ) )
            require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
        require_once( dirname( __FILE__ ) . '/includes/class-bwssmtp-settings.php' );
        $page = new Bwssmtp_Settings_Tabs( plugin_basename( __FILE__ ) );
	    if ( method_exists( $page,'add_request_feature' ) )
		    $page->add_request_feature(); ?>
        <div class="wrap">
            <h1><?php _e( 'SMTP Settings', 'bws-smtp' ); ?></h1>
            <noscript>
                <div class="error below-h2">
                    <p><strong><?php _e( 'WARNING', 'bws-smtp' ); ?>:</strong> <?php _e( 'The plugin works correctly only if JavaScript is enabled.', 'bws-smtp' ); ?></p>
                </div>
            </noscript>
            <?php $page->display_content(); ?>
        </div>
    <?php }
}

/* Configure phpmailer. */
if ( ! function_exists( 'bwssmtp_phpmailer_init' ) ) {
	function bwssmtp_phpmailer_init( $phpmailer ) {
		global $bwssmtp_options, $phpmailer;

		if ( empty( $bwssmtp_options ) )
			bwssmtp_register_settings();

		$phpmailer->IsSMTP();

		if ( $bwssmtp_options['use_plugin_settings_from'] ) {
			$from_email = $bwssmtp_options['SMTP']['from_email'];
			$from_name  = $bwssmtp_options['SMTP']['from_name'];
			$phpmailer->SetFrom( $from_email, $from_name );
		}

		if ( 'none' != $bwssmtp_options['SMTP']['secure'] ) {
			$phpmailer->SMTPSecure = $bwssmtp_options['SMTP']['secure'];
		} else {
            $phpmailer->SMTPSecure = false;
            $phpmailer->SMTPAutoTLS = false;
        }
		$phpmailer->Host = $bwssmtp_options['SMTP']['host'];
		$phpmailer->Port = $bwssmtp_options['SMTP']['port'];
		if ( $bwssmtp_options['SMTP']['authentication'] ) {
			$phpmailer->SMTPAuth = true;
			$phpmailer->Username = $bwssmtp_options['SMTP']['username'];
			$phpmailer->Password = $bwssmtp_options['SMTP']['password'];
		}
	}
}

if ( ! function_exists( 'bwssmtp_action_links' ) ) {
	function bwssmtp_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			$base = plugin_basename( __FILE__ );
			if ( $file == $base ) {
				$settings_link = '<a href="admin.php?page=bwssmtp_settings">' . __( 'Settings', 'bws-smtp' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}

if ( ! function_exists( 'bwssmtp_links' ) ) {
	function bwssmtp_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() ) {
				$links[] = '<a href="admin.php?page=bwssmtp_settings">' . __( 'Settings', 'bws-smtp' ) . '</a>';
			}
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200908825" target="_blank">' . __( 'FAQ', 'bws-smtp' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'bws-smtp' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'bwssmtp_admin_notices' ) ) {
	function bwssmtp_admin_notices() {
		global $hook_suffix, $bwssmtp_plugin_info, $bstwbsftwppdtplgns_cookie_add, $bwssmtp_options;

		if ( empty( $bwssmtp_options ) )
			bwssmtp_register_settings();

		if ( 'plugins.php' == $hook_suffix && ! $bwssmtp_options['confirmed'] ) {
			if ( ! isset( $bstwbsftwppdtplgns_cookie_add ) ) {
				wp_enqueue_script( 'bwssmtp_cookie', plugins_url( 'bws_menu/js/c_o_o_k_i_e.js', __FILE__ ) );
			    $bstwbsftwppdtplgns_cookie_add = true;
			}

			$script = '(function($) {
                    $(document).ready( function() {
                        var hide_message = $.cookie( "bwssmtp_hide_banner_on_plugin_page" );
                        if ( "true" === hide_message ) {
                            $( ".bwssmtp_message" ).css( "display", "none" );
                        } else {
                            $( ".bwssmtp_message" ).css( "display", "block" );
                        }
                        $( ".bwssmtp_close_icon" ).click( function() {
                            $( ".bwssmtp_message" ).css( "display", "none" );
                            $.cookie( "bwssmtp_hide_banner_on_plugin_page", "true", { expires: 32 } );
                        });
                    });
                })(jQuery);';
			wp_register_script( 'bwssmtp_banner_on_plugin_page', '//' );
			wp_enqueue_script( 'bwssmtp_banner_on_plugin_page' );
			wp_add_inline_script( 'bwssmtp_banner_on_plugin_page', sprintf( $script ) ); ?>
            <div class="updated bwssmtp_message" style="padding: 0; margin: 0; border: none; background: none;">
                <div class="bws_banner_on_plugin_page">
                    <button class="bwssmtp_close_icon close_icon notice-dismiss bws_hide_settings_notice" title="<?php _e( 'Close notice', 'bws-smtp' ); ?>"></button>
                    <div class="icon">
                        <img title="" src="//ps.w.org/bws-smtp/assets/icon-128x128.png" alt="" />
                    </div>
                    <div class="text">
                        <?php _e( 'Configure the "SMTP by BestWebSoft" plugin for sending email messages via SMTP', 'bws-smtp' ); ?><br />
                        <a href="admin.php?page=bwssmtp_settings"><?php _e( 'Go to the settings', 'bws-smtp' ); ?></a>
                    </div>
                </div>
            </div>
		<?php }

		if ( isset( $_REQUEST['page'] ) && 'bwssmtp_settings' == $_REQUEST['page'] ) {
			bws_plugin_suggest_feature_banner( $bwssmtp_plugin_info, 'bwssmtp_options', 'bws-smtp' );
		}
	}
}

/* Screen option */
if ( ! function_exists( 'bwssmtp_screen_options' ) ) {
	function bwssmtp_screen_options() {
		$screen = get_current_screen();
		$args = array(
			'id'      => 'bwssmtp',
			'section' => '200908825'
		);
		bws_help_tab( $screen, $args );
	}
}

/* Delete options. */
if ( ! function_exists( 'bwssmtp_uninstall' ) ) {
	function bwssmtp_uninstall() {
		global $wpdb;
		/* Delete options */
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'bwssmtp_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'bwssmtp_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'bwssmtp_plugin_activate' );
/* Add menu to the dashboard. */
add_action( 'admin_menu', 'bwssmtp_dashboard_menu' );
/* Initialization */
add_action( 'init', 'bwssmtp_init' );
add_action( 'admin_init', 'bwssmtp_admin_init' );
add_action( 'plugins_loaded', 'bwssmtp_plugins_loaded' );
/* Add script and styles to the dashboard. */
add_action( 'admin_enqueue_scripts', 'bwssmtp_dashboard_script_styles' );
/* Setup phpmailer. */
add_action( 'phpmailer_init', 'bwssmtp_phpmailer_init' );
/* Add additional links for plugin on the plugins page */
add_filter( 'plugin_action_links', 'bwssmtp_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'bwssmtp_links', 10, 2 );
add_action( 'admin_notices', 'bwssmtp_admin_notices' );
