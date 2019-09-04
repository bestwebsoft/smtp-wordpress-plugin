<?php
/*
Plugin Name: SMTP by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/smtp/
Description: Configure SMTP server to receive email messages from WordPress to Gmail, Yahoo, Hotmail and other services.
Author: BestWebSoft
Text Domain: bws-smtp
Domain Path: /languages
Version: 1.1.4
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2019  BestWebSoft  ( https://support.bestwebsoft.com )

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
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $bwssmtp_plugin_info, '3.9' );
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
			bwssmtp_default_options();
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

/* Set default or get current options. */
if ( ! function_exists ( 'bwssmtp_default_options' ) ) {
	function bwssmtp_default_options() {
		global $bwssmtp_options, $bwssmtp_default_options, $bwssmtp_plugin_info;

		$bwssmtp_default_options = array(
			'plugin_option_version' 	=> $bwssmtp_plugin_info['Version'],
			'use_plugin_settings_from'	=> 1,
			'confirmed'					=> false,
			'settings_changed'			=> false,
			'SMTP'						=> array(
				'from_email'		=> preg_replace( '|^(https?:\/\/)?(www\.)?([\w.]+)/?.*?$|u', 'wordpress@$3', strtolower( $_SERVER['SERVER_NAME'] ) ),
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

		if ( ! get_option( 'bwssmtp_options' ) )
			add_option( 'bwssmtp_options', $bwssmtp_default_options );

		$bwssmtp_options = get_option( 'bwssmtp_options' );

		if ( ! isset( $bwssmtp_options['plugin_option_version'] ) || $bwssmtp_options['plugin_option_version'] != $bwssmtp_plugin_info['Version'] ) {
			if ( true == $bwssmtp_options['confirmed'] ) {
				$bwssmtp_options['settings_changed'] = true;
			}
			bwssmtp_plugin_activate();
			$bwssmtp_options = array_merge( $bwssmtp_default_options, $bwssmtp_options );
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

/* Display settings page. */
if ( ! function_exists( 'bwssmtp_settings_page' ) ) {
	function bwssmtp_settings_page() {
		global $bwssmtp_options, $bwssmtp_plugin_info, $bwssmtp_default_options;

		$bwssmtp_notices = array();
		$plugin_basename = plugin_basename( __FILE__ );

		if ( isset( $_POST['bwssmtp_submit'] ) && check_admin_referer( $plugin_basename, 'bwssmtp_nonce_settings' ) ) {
			/* Check for errors and add notices. */
			if ( isset( $_POST['bwssmtp_from_email'] ) && ! empty( $_POST['bwssmtp_from_email'] ) ) {
				if ( ! is_email( $_POST['bwssmtp_from_email'] ) ) {
					$bwssmtp_notices['bwssmtp_from_email'] = array(
						'type' => 'error',
						'text' => sprintf( __( 'Email address %s in the "%s" field is not valid!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', stripslashes( esc_html( $_POST['bwssmtp_from_email'] ) ) ), sprintf( '<strong>%s</strong>', __( '"From" Field Email', 'bws-smtp' ) ) )
					);
				}
			} else {
				$bwssmtp_notices['bwssmtp_from_email'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( '"From" Field Email', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_from_name'] ) && empty( $_POST['bwssmtp_from_name'] ) ) {
				$bwssmtp_notices['bwssmtp_from_name'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( '"From" Field Name', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_host'] ) && empty( $_POST['bwssmtp_host'] ) ) {
				$bwssmtp_notices['bwssmtp_host'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Host', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_port'] ) && empty( $_POST['bwssmtp_port'] ) ) {
				$bwssmtp_notices['bwssmtp_port'] = array(
					'type' => 'error',
					'text' => sprintf(	__( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Port', 'bws-smtp' ) ) )
				);
			} else {
				if ( ! preg_match( '/^\d+$/', $_POST['bwssmtp_port'] ) ) {
					$bwssmtp_notices['bwssmtp_port'] = array(
						'type' => 'error',
						'text' => sprintf( __( 'The field "%s" must contain only numbers!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Port', 'bws-smtp' ) ) )
					);
				}
			}

			if ( isset( $_POST['bwssmtp_authentication'] ) && isset( $_POST['bwssmtp_username'] ) && empty( $_POST['bwssmtp_username'] ) ) {
				$bwssmtp_notices['bwssmtp_username'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Username', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_authentication'] ) && isset( $_POST['bwssmtp_password'] ) && empty( $_POST['bwssmtp_password'] ) ) {
				$bwssmtp_notices['bwssmtp_password'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Password', 'bws-smtp' ) ) )
				);
			}

			/* Create new options. */
			$bwssmtp_new_options['use_plugin_settings_from'] = isset( $_POST['bwssmtp_use_plugin_settings_from'] ) ? 1 : 0;
			$bwssmtp_new_options['SMTP'] = array(
				'from_email'     => isset( $_POST['bwssmtp_from_email'] ) ? stripslashes( esc_html( $_POST['bwssmtp_from_email'] ) ) : '',
				'from_name'      => isset( $_POST['bwssmtp_from_name'] ) ? stripslashes( esc_html( $_POST['bwssmtp_from_name'] ) ) : '',
				'host'           => isset( $_POST['bwssmtp_host'] ) ? stripslashes( esc_html( $_POST['bwssmtp_host'] ) ) : '',
				'port'           => isset( $_POST['bwssmtp_port'] ) ? stripslashes( esc_html( ltrim( $_POST['bwssmtp_port'], '0' ) ) ) : '',
				'secure'         => isset( $_POST['bwssmtp_secure'] ) ? stripslashes( esc_html( $_POST['bwssmtp_secure'] ) ) : 'none',
				'authentication' => isset( $_POST['bwssmtp_authentication'] ) ? 1 : 0,
				'username'       => isset( $_POST['bwssmtp_username'] ) ? stripslashes( esc_html( $_POST['bwssmtp_username'] ) ) : '',
				'password'       => isset( $_POST['bwssmtp_password'] ) ? stripslashes( esc_html( $_POST['bwssmtp_password'] ) ) : ''
			);
			/* If no errors, update options. */
			if ( $bwssmtp_notices ) {
				$bwssmtp_notices['settings'] = array(
					'type'  => 'error',
					'text'  => __( 'Settings are not saved.', 'bws-smtp' )
				);
			} else {
				$bwssmtp_diff = array_diff( $bwssmtp_new_options['SMTP'], $bwssmtp_options['SMTP'] );
				if ( $bwssmtp_diff ) {
					$bwssmtp_new_options['confirmed'] = false;
				}
				$bwssmtp_new_options['settings_changed'] = true;
				$bwssmtp_options = array_merge( $bwssmtp_options, $bwssmtp_new_options );
				update_option( 'bwssmtp_options', $bwssmtp_options );
				$bwssmtp_notices['settings'] = array(
					'type'  => 'success',
					'text'  => __( 'Settings saved.', 'bws-smtp' )
				);
			}
		}

		/* Send a test email. */
		if ( isset( $_POST['bwssmtp_test_send'] ) && check_admin_referer( $plugin_basename, 'bwssmtp_nonce_test' ) ) {

			$bwssmtp_test_to = isset( $_POST['bwssmtp_test_to'] ) ? stripslashes( esc_html( $_POST['bwssmtp_test_to'] ) ) : '';
			$bwssmtp_test_log = isset( $_POST['bwssmtp_test_log'] ) ? 1 : 0;

			if ( empty( $bwssmtp_test_to ) ) {
				$bwssmtp_notices['bwssmtp_test_to'] = array(
					'type'  => 'error',
					'text'  => sprintf( __( 'You have not entered an email address which will receive your test email!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
				);
			} elseif ( ! is_email( $bwssmtp_test_to ) ) {
				$bwssmtp_notices['bwssmtp_test_to'] = array(
					'type'  => 'error',
					'text'  => sprintf( __( 'Email address %s is not valid!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
				);
			}

			if ( isset( $_FILES['bwssmtp_test_file_attach'] ) && '' != $_FILES['bwssmtp_test_file_attach']['name'] ) {
				$bwssmtp_test_file_flag = 1;
				$bwssmtp_test_file_name = $_FILES['bwssmtp_test_file_attach']['name'];
				$bwssmtp_test_file_type = $_FILES['bwssmtp_test_file_attach']['type'];
				$bwssmtp_test_file_tmp_name = $_FILES['bwssmtp_test_file_attach']['tmp_name'];
				if ( 0 == $_FILES['bwssmtp_test_file_attach']['error'] ) {
					$bwssmtp_mime_type = array(
						'xl'    => 'application/excel',
						'js'    => 'application/javascript',
						'doc'   => 'application/msword',
						'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
						'ppsx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
						'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
						'sldx'  => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
						'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
						'pdf'   => 'application/pdf',
						'ai'    => 'application/postscript',
						'eps'   => 'application/postscript',
						'ps'    => 'application/postscript',
						'xls'   => 'application/vnd.ms-excel',
						'ppt'   => 'application/vnd.ms-powerpoint',
						'wbxml' => 'application/vnd.wap.wbxml',
						'gtar'  => 'application/x-gtar',
						'tar'   => 'application/x-tar',
						'tgz'   => 'application/x-tar',
						'xht'   => 'application/xhtml+xml',
						'xhtml' => 'application/xhtml+xml',
						'zip'   => 'application/zip',
						'mid'   => 'audio/midi',
						'midi'  => 'audio/midi',
						'mp3'   => 'audio/mpeg',
						'wav'   => 'audio/x-wav',
						'bmp'   => 'image/bmp',
						'gif'   => 'image/gif',
						'jpeg'  => 'image/jpeg',
						'jpe'   => 'image/jpeg',
						'jpg'   => 'image/jpeg',
						'png'   => 'image/png',
						'tiff'  => 'image/tiff',
						'tif'   => 'image/tiff',
						'eml'   => 'message/rfc822',
						'css'   => 'text/css',
						'html'  => 'text/html',
						'htm'   => 'text/html',
						'shtml' => 'text/html',
						'log'   => 'text/plain',
						'text'  => 'text/plain',
						'txt'   => 'text/plain',
						'rtx'   => 'text/richtext',
						'rtf'   => 'text/rtf',
						'vcf'   => 'text/vcard',
						'vcard' => 'text/vcard',
						'xml'   => 'text/xml',
						'xsl'   => 'text/xml',
						'csv'   => 'text/csv',
					);
					if ( ! in_array( $bwssmtp_test_file_type, $bwssmtp_mime_type ) ) {
						$file_ext = explode( '.', $bwssmtp_test_file_name );
						$file_ext = end( $file_ext );
						$bwssmtp_notices['bwssmtp_test_file_attach'] = array(
							'type'  => 'error',
							'text'  => sprintf( __( 'It\'s forbidden to attach files with %s extension!', 'bws-smtp' ), sprintf( '<strong>.%s</strong>', $file_ext ) )
						);
					}
				} else {
					if ( 1 == $_FILES['bwssmtp_test_file_attach']['error'] ) {
						if ( bwssmtp_return_bytes( ini_get( 'upload_max_filesize' ) ) >= bwssmtp_return_bytes( ini_get( 'post_max_size' ) ) ) {
							$bwssmtp_max_file_size = ini_get( 'post_max_size' );
						} else {
							$bwssmtp_max_file_size = ini_get( 'upload_max_filesize' );
						}
						$bwssmtp_notices['bwssmtp_test_file_size'] = array(
							'type'  => 'error',
							'text'  => sprintf( __( 'Max. size of the attached file is - %s!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $bwssmtp_max_file_size ) )
						);
					} else {
						$bwssmtp_notices['bwssmtp_test_file_error'] = array(
							'type'  => 'error',
							'text'  => __( 'File upload error!', 'bws-smtp' ),
						);
					}
				}
			} else {
				$bwssmtp_test_file_flag = 0;
			}

			if ( empty( $bwssmtp_options['use_plugin_settings_from'] ) ) {
				$test_from_email = isset( $_POST['bwssmtp_from_email_test'] ) ? stripslashes( esc_html( $_POST['bwssmtp_from_email_test'] ) ) : '';
				$test_from_name = isset( $_POST['bwssmtp_from_name_test'] ) ? stripslashes( esc_html( $_POST['bwssmtp_from_name_test'] ) ) : '';

				if ( empty( $test_from_email ) ) {
					$bwssmtp_notices['bwssmtp_from_email_test'] = array(
						'type'  => 'error',
						'text'  => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( '"From" Field Email', 'bws-smtp' ) ) )
					);
				} elseif ( ! is_email( $test_from_email ) ) {
					$bwssmtp_notices['bwssmtp_from_email_test'] = array(
						'type'  => 'error',
						'text'  => sprintf( __( 'Email address %s is not valid!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $test_from_email ) )
					);
				}

				if ( empty( $test_from_name ) ) {
					$bwssmtp_notices['bwssmtp_from_name_test'] = array(
						'type' => 'error',
						'text' => sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( '"From" Field Name', 'bws-smtp' ) ) )
					);
				}
			}

			if ( empty( $bwssmtp_notices ) ) {
				require_once( ABSPATH . WPINC . '/class-phpmailer.php' );

				$bwssmtp_phpmailer = new PHPMailer();
				$bwssmtp_phpmailer->IsSMTP();

				if ( ! empty( $bwssmtp_options['use_plugin_settings_from'] ) ) {
					$from_email = $bwssmtp_options['SMTP']['from_email'];
					$from_name  = $bwssmtp_options['SMTP']['from_name'];
					$bwssmtp_phpmailer->SetFrom( $from_email, $from_name );
				} else {
					$from_email = $test_from_email;
					$from_name  = $test_from_name;
					if ( ! empty( $from_email ) && ! empty( $from_name ) ) {
						$bwssmtp_phpmailer->SetFrom( $from_email, $from_name );
					}
				}

				if ( 'none' !== $bwssmtp_options['SMTP']['secure'] ) {
					$bwssmtp_phpmailer->SMTPSecure = $bwssmtp_options['SMTP']['secure'];
				} else {
                    $bwssmtp_phpmailer->SMTPSecure = false;
                    $bwssmtp_phpmailer->SMTPAutoTLS = false;
                }

				$bwssmtp_phpmailer->Host = $bwssmtp_options['SMTP']['host'];
				$bwssmtp_phpmailer->Port = $bwssmtp_options['SMTP']['port'];

				if ( ! empty( $bwssmtp_options['SMTP']['authentication'] ) ) {
					$bwssmtp_phpmailer->SMTPAuth = true;
					$bwssmtp_phpmailer->Username = $bwssmtp_options['SMTP']['username'];
					$bwssmtp_phpmailer->Password = $bwssmtp_options['SMTP']['password'];
				}

				$bwssmtp_phpmailer->CharSet = 'UTF-8';
				$bwssmtp_phpmailer->isHTML( false );
				$bwssmtp_phpmailer->Subject = sprintf( __( 'SMTP by BestWebSoft plugin: Test email to %s', 'bws-smtp' ), ' ' . $bwssmtp_test_to );
				$bwssmtp_phpmailer->MsgHTML( sprintf( __( 'Please do not reply. This is a test email sent via SMTP by BestWebSoft plugin from %s.', 'bws-smtp' ), get_option( 'home' ) ) );
				$bwssmtp_phpmailer->AddAddress( $bwssmtp_test_to );
				if ( 1 == $bwssmtp_test_file_flag ) {
					$bwssmtp_phpmailer->addAttachment( $bwssmtp_test_file_tmp_name, $bwssmtp_test_file_name, 'base64', $bwssmtp_test_file_type );
				}

				if ( 1 == $bwssmtp_test_log ) {
					$bwssmtp_phpmailer->SMTPDebug = true;
					ob_start();
					$bwssmtp_result = $bwssmtp_phpmailer->Send();
					$bwssmtp_log = ob_get_contents();
					ob_end_clean();
				} else {
					$bwssmtp_result = $bwssmtp_phpmailer->Send();
				}

				$bwssmtp_phpmailer->ClearAddresses();
				$bwssmtp_phpmailer->ClearAllRecipients();

				if ( $bwssmtp_result ) {
					$bwssmtp_notices['bwssmtp_test_result'] = array(
						'type'  => 'success',
						'text'  => sprintf( __( '%s: A test email has been sent %s.', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'Successfully', 'bws-smtp' ) ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
					);
				} else {
					$bwssmtp_notices['bwssmtp_test_result'] = array(
						'type'  => 'error',
						'text'  => sprintf( __( '%s: A test email was not sent.', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'Error', 'bws-smtp' ) ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
					);
				}
			}
		}

		/* Confirm the correct settings. */
		if ( isset( $_POST['bwssmtp_confirm_settings'] ) && check_admin_referer( $plugin_basename, 'bwssmtp_nonce_confirm' ) ) {
			$bwssmtp_options['confirmed'] = true;
			update_option( 'bwssmtp_options', $bwssmtp_options );
		}

		/* Warn about different domain names. */
		if ( ! empty( $bwssmtp_options['SMTP']['from_email'] ) && is_email( $bwssmtp_options['SMTP']['from_email'] ) && ! empty( $bwssmtp_options['use_plugin_settings_from'] ) ) {
			$bwssmtp_from_email = explode( '@', $bwssmtp_options['SMTP']['from_email'] );
			$bwssmtp_from_email_host = $bwssmtp_from_email[ 1 ];
		} elseif ( isset( $_POST['bwssmtp_from_email_test'] ) && is_email( $_POST['bwssmtp_from_email_test'] ) && empty( $bwssmtp_options['use_plugin_settings_from'] ) ) {
			if ( ! empty( $test_from_email ) ) {
				$bwssmtp_from_email = $test_from_email;
				$bwssmtp_from_email = explode( '@', $bwssmtp_from_email );
				$bwssmtp_from_email_host = $bwssmtp_from_email[ 1 ];
			}
		}

		if ( ! empty( $bwssmtp_from_email_host ) ) {
			if ( ! strpos( $bwssmtp_options['SMTP']['host'], $bwssmtp_from_email_host ) && ! $bwssmtp_options['confirmed'] ) {
				array_unshift( $bwssmtp_notices,
					array(
						'type'  => 'warning',
						'text'  => sprintf( __( 'A problem with email sending may occur, since most servers require domain name match in "%s" and "%s" fields.', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( '"From" Field Email', 'bws-smtp' ) ), sprintf( '<strong>%s</strong>', __( 'SMTP Host', 'bws-smtp' ) ) )
					)
				);
			}
		}
		if ( isset( $_REQUEST['bws_restore_confirm'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
			$bwssmtp_options = $bwssmtp_default_options;
			update_option( 'bwssmtp_options', $bwssmtp_options );
			$bwssmtp_notices['bwssmtp_restore_result'] = array(
				'type'  => 'success',
				'text'  => __( 'All plugin settings were restored.', 'bws-smtp' )
			);
		} ?>
		<div class="wrap">
			<h1>SMTP <?php _e( 'Settings', 'bws-smtp' )?></h1>
			<ul class="subsubsub bwssmtp_how_to_use">
				<li><a href="https://docs.google.com/document/d/1u2QAHYmoeRMYDD8eq_8uCiKob7x86ms1lJhmQlwWEpw/" target="_blank"><?php _e( 'How to Use Step-by-step Instruction', 'bws-smtp' ); ?></a></li>
			</ul>
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=bwssmtp_settings"><?php _e( 'Settings', 'bws-smtp' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && 'test_email' == $_GET['action'] ) echo ' nav-tab-active'; ?>" href="admin.php?page=bwssmtp_settings&action=test_email"><?php _e( 'Send A Test Email', 'bws-smtp' ); ?></a>
			</h2>
            <noscript>
                <div class="error below-h2">
                    <p><strong>
							<?php _e( 'Please, enable JavaScript in your browser.', 'bws-smtp' ); ?>
                        </strong></p>
                </div>
            </noscript>
			<?php if ( ! empty( $bwssmtp_notices ) ) {
				foreach ( $bwssmtp_notices as $bwssmtp_field => $bwssmtp_notice ) {
					$bwssmtp_for = $bwssmtp_notice['type'] . '_' . $bwssmtp_field;
					printf( '<div class="bwssmtp_notice bwssmtp_notice_%s %s"><p>%s</p></div>', $bwssmtp_notice['type'], $bwssmtp_for, $bwssmtp_notice['text'] );
				}
			}
			if ( ! isset( $_GET['action'] ) ) {
				if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $plugin_basename, 'bws_settings_nonce_name' ) ) {
					bws_form_restore_default_confirm( $plugin_basename );
				} else {
					bws_show_settings_notice(); ?>
                    <form id="bwssmtp_settings_form" class="bws_form" method="post" action="admin.php?page=bwssmtp_settings" autocomplete="on">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row">
										<label for="bwssmtp_from_email"><?php _e( 'Settings Status', 'bws-smtp' ); ?></label>
									</th>
									<td class="<?php echo ( $bwssmtp_options['confirmed'] ) ? 'bwssmtp_confirmed' : 'bwssmtp_not_confirmed'; ?>">
										<?php if ( $bwssmtp_options['confirmed'] ) {
											_e( 'Confirmed', 'bws-smtp' );
										} else {
											_e( 'Not confirmed', 'bws-smtp' ); ?>
											<div class="bws_info"><?php printf( __( 'To confirm the settings, please send a test email, and then click "%s" button after successful sending.', 'bws-smtp' ), __( 'Settings Are Correct', 'bws-smtp' ) ); ?></div>
										<?php } ?>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label><?php _e( 'Set "From" field (name, email)', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_use_plugin_settings_from" type="checkbox" name="bwssmtp_use_plugin_settings_from" value="1" <?php checked( $bwssmtp_options['use_plugin_settings_from'] ); ?>/>
										<div class="bws_info"><?php _e( 'Unmark the checkbox if you want to use "From" field from other plugins', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr class="bwssmtp_plugin_settings_from<?php if ( empty( $bwssmtp_options['use_plugin_settings_from'] ) ) echo ' bwssmtp_hidden'; ?>" valign="top">
									<th scope="row"><label for="bwssmtp_from_email"><?php _e( '"From" Field Email', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_from_email" <?php if ( array_key_exists( 'bwssmtp_from_email', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_email" value="<?php echo $bwssmtp_options['SMTP']['from_email']; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter an email, which will be used in the message "From" field.', 'bws-smtp' ); ?></div>
										<div class="bws_info"><?php _e( 'Most mail servers can change the email address in the message "From" field.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr class="bwssmtp_plugin_settings_from<?php if ( empty( $bwssmtp_options['use_plugin_settings_from'] ) ) echo ' bwssmtp_hidden'; ?>" valign="top">
									<th scope="row"><label for="bwssmtp_from_name"><?php _e( '"From" Field Name', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_from_name" <?php if ( array_key_exists( 'bwssmtp_from_name', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_name" value="<?php echo $bwssmtp_options['SMTP']['from_name']; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter the name which will be used in the message "From" field.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="bwssmtp_host"><?php _e( 'SMTP Host', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_host" <?php if ( array_key_exists( 'bwssmtp_host', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_host" value="<?php echo $bwssmtp_options['SMTP']['host']; ?>" maxlength="250"/>
										<div class="bws_info"><?php _e( 'Enter mail server host name or IP address.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row">
										<label for="bwssmtp_port"><?php _e( 'SMTP Port', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_port" <?php if ( array_key_exists( 'bwssmtp_port', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="number" name="bwssmtp_port" value="<?php echo $bwssmtp_options['SMTP']['port']; ?>" min="1" max="65535" step="1"/>
										<div class="bws_info"><?php _e( 'Enter the mail server port. Most mail servers use port 465.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label><?php _e( 'SMTP Secure Connection', 'bws-smtp' ); ?></label></th>
									<td><fieldset>
										<div>
											<input id="bwssmtp_secure_none" type='radio' name="bwssmtp_secure" value="none" <?php checked( 'none', $bwssmtp_options['SMTP']['secure'] ); ?> /><label for="bwssmtp_secure_none"><?php _e( 'None', 'bws-smtp' ); ?></label>
										</div>
										<div>
											<input id="bwssmtp_secure_ssl" type='radio' name="bwssmtp_secure" value="ssl" <?php checked( 'ssl', $bwssmtp_options['SMTP']['secure'] ); ?> /><label for="bwssmtp_secure_ssl"><?php _e( 'SSL', 'bws-smtp' ); ?></label>
										<div>
										</div>
											<input id="bwssmtp_secure_tls" type='radio' name="bwssmtp_secure" value="tls" <?php checked( 'tls', $bwssmtp_options['SMTP']['secure'] ); ?> /><label for="bwssmtp_secure_tls"><?php _e( 'TLS', 'bws-smtp' ); ?></label>
										</div>
										<span class="bws_info"><?php _e( 'Select the type of secure connection with the mail server.', 'bws-smtp' ); ?></span>
										<span class="bws_info"><?php _e( 'Most mail servers use SSL connection.', 'bws-smtp' ); ?></span>
									</fieldset></td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="bwssmtp_authentication"><?php _e( 'SMTP Authentication', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_authentication" type="checkbox" name="bwssmtp_authentication" value="1" <?php checked( $bwssmtp_options['SMTP']['authentication'] ); ?> />
										<span class="bws_info"><?php _e( 'Mark the checkbox if authentication is required on the mail server.', 'bws-smtp' ); ?></span>
										<span class="bws_info"><?php _e( 'Most mail servers require entering username and password.', 'bws-smtp' ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_authentication_settings<?php if ( empty( $bwssmtp_options['SMTP']['authentication'] ) ) echo ' bwssmtp_hidden'; ?>" valign="top">
									<th scope="row">
										<label for="bwssmtp_username"><?php _e( 'SMTP Username', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_username" <?php if ( array_key_exists( 'bwssmtp_username', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" autocomplete="off" name="bwssmtp_username" value="<?php echo $bwssmtp_options['SMTP']['username']; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter the username for authentication on the mail server.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr class="bwssmtp_authentication_settings<?php if ( empty( $bwssmtp_options['SMTP']['authentication'] ) ) echo ' bwssmtp_hidden'; ?>" valign="top">
									<th scope="row">
										<label for="bwssmtp_password"><?php _e( 'SMTP Password', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_password" <?php if ( array_key_exists( 'bwssmtp_password', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="password" name="bwssmtp_password" autocomplete="off" value="<?php echo $bwssmtp_options['SMTP']['password']; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter the password for authentication on the mail server.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
							</tbody>
						</table>
						<p>
							<?php wp_nonce_field( $plugin_basename, 'bwssmtp_nonce_settings' ); ?>
							<input id="bws-submit-button" type="submit" class="button-primary" name="bwssmtp_submit" value="<?php _e( 'Save Changes', 'bws-smtp' ); ?>" />
						</p>
					</form>
					<?php bws_form_restore_default_settings( $plugin_basename );
				}
			} elseif ( 'test_email' == $_GET['action'] ) {
				if ( bwssmtp_return_bytes( ini_get( 'upload_max_filesize' ) ) >= bwssmtp_return_bytes( ini_get( 'post_max_size' ) ) ) {
					$bwssmtp_max_file_size = ini_get( 'post_max_size' );
				} else {
					$bwssmtp_max_file_size = ini_get( 'upload_max_filesize' );
				} ?>
                <div class="error below-h2" id="bwssmtp_error_to_show" style="display:none" value="">
                    <p><strong>
							<?php _e( 'The file size exceeds the limit allowed', 'bws-smtp' ); ?>
                        </strong></p>
                </div>
				<form id="bwssmtp_test_form" enctype="multipart/form-data" method="post" action="admin.php?page=bwssmtp_settings&action=test_email">
                    <table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row">
									<label><?php _e( 'Current Settings', 'bws-smtp' ); ?></label>
								</th>
								<td>
									<table id="bwssmtp_current_settings">
										<tbody>
											<?php if ( ! empty( $bwssmtp_options['use_plugin_settings_from'] ) ) { ?>
												<tr>
													<th><?php _e( 'From:', 'bws-smtp' ); ?></th>
													<td><?php echo $bwssmtp_options['SMTP']['from_name']; ?> &#60;<?php echo $bwssmtp_options['SMTP']['from_email'] ?>&#62;</td>
												</tr>
											<?php } ?>
											<tr>
												<th><?php _e( 'SMTP Host', 'bws-smtp' ); ?>:</th>
												<td><?php echo $bwssmtp_options['SMTP']['host']; ?></td>
											</tr>
											<tr>
												<th><?php _e( 'SMTP Port', 'bws-smtp' ); ?>:</th>
												<td><?php echo $bwssmtp_options['SMTP']['port']; ?></td>
											</tr>
											<tr>
												<th><?php _e( 'SMTP Secure Connection', 'bws-smtp' ); ?>:</th>
												<td><?php echo ( 'none' == $bwssmtp_options['SMTP']['secure'] ) ? __( ucfirst( $bwssmtp_options['SMTP']['secure'] ), 'bws-smtp' ) : strtoupper( $bwssmtp_options['SMTP']['secure'] ); ?></td>
											</tr>
											<tr>
												<th><?php _e( 'SMTP Authentication', 'bws-smtp' ); ?>:</th>
												<td><?php ( 1 == $bwssmtp_options['SMTP']['authentication'] ) ? _e( 'Yes', 'bws-smtp' ) : _e( 'No', 'bws-smtp' ); ?></td>
											</tr>
											<?php if ( 1 == $bwssmtp_options['SMTP']['authentication'] ) { ?>
												<tr>
													<th><?php _e( 'SMTP Username', 'bws-smtp' ); ?>:</th>
													<td><?php echo $bwssmtp_options['SMTP']['username']; ?></td>
												</tr>
												<tr>
													<th><?php _e( 'SMTP Password', 'bws-smtp' ); ?>:</th>
													<td><?php echo str_repeat( '*', strlen( $bwssmtp_options['SMTP']['password'] ) ); ?></td>
												</tr>
											<?php } ?>
										</tbody>
									</table>
									<div class="bws_info"><?php _e( 'These settings will be used when sending a test email.', 'bws-smtp' ); ?></div>
									<?php if ( isset( $bwssmtp_phpmailer ) && true == $bwssmtp_result ) { ?>
										<form id="bwssmtp_confirm_form" method="post" action="admin.php?page=bwssmtp_settings&action=test_email&noheader=true">
											<input id="bwssmtp_confirm_settings" class="button-secondary" type="submit" name="bwssmtp_confirm_settings" value="<?php _e( 'Settings Are Correct', 'bws-smtp' ); ?>">
											<?php wp_nonce_field( $plugin_basename, 'bwssmtp_nonce_confirm' ); ?>
										</form>
									<?php } ?>
								</td>
							</tr>
							<?php if ( empty( $bwssmtp_options['use_plugin_settings_from'] ) ) { ?>
								<tr valign="top">
									<th scope="row"><label for="bwssmtp_from_email_test"><?php _e( '"From" Field Email', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_from_email_test" <?php if ( array_key_exists( 'bwssmtp_from_email_test', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_email_test" value="<?php if ( isset( $test_from_email ) ) echo $test_from_email; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter an email, which will be used in the message "From" field.', 'bws-smtp' ); ?></div>
										<div class="bws_info" ><?php _e( 'Most mail servers can change the email address in the message "From" field.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="bwssmtp_from_name_test"><?php _e( '"From" Field Name', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_from_name_test" <?php if ( array_key_exists( 'bwssmtp_from_name_test', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_name_test" value="<?php if ( isset( $test_from_name ) ) echo $test_from_name; ?>" maxlength="250" />
										<div class="bws_info"><?php _e( 'Enter the name which will be used in the message "From" field.', 'bws-smtp' ); ?></div>
									</td>
								</tr>
							<?php } ?>
							<tr valign="top">
								<th scope="row">
									<label for="bwssmtp_test_to"><?php _e( 'Send A Test Email To', 'bws-smtp' ); ?></label>
								</th>
								<td>
									<input id="bwssmtp_test_to" <?php if ( array_key_exists( 'bwssmtp_test_to', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_test_to" value="<?php if ( isset( $bwssmtp_test_to ) ) echo $bwssmtp_test_to; ?>" maxlength="250" />
									<div class="bws_info"><?php _e( 'Enter an email address which you want to send a test email to.', 'bws-smtp' ); ?></div>
									<input id="bwssmtp_test_file_attach" <?php if ( array_key_exists( 'bwssmtp_test_file_attach', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="file" name="bwssmtp_test_file_attach" />
                                    <input id="bwssmtp_test_file_attach_size" value="<?php echo $bwssmtp_max_file_size;?>" style="display:none">
									<div class="bws_info"><?php printf( __( 'Select a file for the test message. Max. upload file size - %s.', 'bws-smtp' ), $bwssmtp_max_file_size ); ?></div>
									<p>
										<input id="bwssmtp_test_log" type="checkbox" name="bwssmtp_test_log" value="1" <?php if ( isset( $bwssmtp_test_log ) && 1 == $bwssmtp_test_log ) echo 'checked="checked"'; ?> />
										<label for="bwssmtp_test_log"><?php _e( 'Display log', 'bws-smtp' ); ?></label>
										<div class="bws_info"><?php _e( 'Mark the checkbox, if you want to display the log of sending a test email.', 'bws-smtp' ); ?></div>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="submit"><input id="bwssmtp_test_send" class="button-secondary" type="submit" name="bwssmtp_test_send" value="<?php _e( 'Send A Test Email', 'bws-smtp' ) ?>" /></p>
					<?php wp_nonce_field( $plugin_basename, 'bwssmtp_nonce_test' ); ?>
				</form>
				<?php if ( isset( $bwssmtp_test_log ) && 1 == $bwssmtp_test_log && isset( $bwssmtp_phpmailer ) ) { ?>
					<div id="bwssmtp_log" class="bwssmtp_notice bwssmtp_notice_<?php echo ( true == $bwssmtp_result ) ? 'success' : 'error'; ?> bwssmtp_log_<?php echo ( true == $bwssmtp_result ) ? 'success' : 'error'; ?>">
						<div class="bwssmtp_log_stage"><?php _e( 'Sending results:', 'bws-smtp' ); ?></div>
						<div class="bwssmtp_log_result"><?php var_dump( $bwssmtp_result ); ?></div>
						<div class="bwssmtp_log_stage"><?php _e( 'Sending log:', 'bws-smtp' ); ?></div>
						<pre class="bwssmtp_log_result"><?php var_dump( $bwssmtp_phpmailer ); ?></pre>
						<div class="bwssmtp_log_stage"><?php _e( 'SMTP log:', 'bws-smtp' ); ?></div>
						<pre class="bwssmtp_log_result"><?php var_dump( $bwssmtp_log ); ?></pre>
					</div>
				<?php }
			}
			bws_plugin_reviews_block( $bwssmtp_plugin_info['Name'], 'bws-smtp' ); ?>
		</div>
	<?php }
}

/* Configure phpmailer. */
if ( ! function_exists( 'bwssmtp_phpmailer_init' ) ) {
	function bwssmtp_phpmailer_init( $phpmailer ) {
		global $bwssmtp_options;

		$bwssmtp_options = get_option( 'bwssmtp_options' );

		$phpmailer->IsSMTP();

		if ( ! empty( $bwssmtp_options['use_plugin_settings_from'] ) ) {
			$from_email = $bwssmtp_options['SMTP']['from_email'];
			$from_name  = $bwssmtp_options['SMTP']['from_name'];
			$phpmailer->SetFrom( $from_email, $from_name );
		}

		if ( 'none' !== $bwssmtp_options['SMTP']['secure'] ) {
			$phpmailer->SMTPSecure = $bwssmtp_options['SMTP']['secure'];
		} else {
            $phpmailer->SMTPSecure = false;
            $phpmailer->SMTPAutoTLS = false;
        }
		$phpmailer->Host = $bwssmtp_options['SMTP']['host'];
		$phpmailer->Port = $bwssmtp_options['SMTP']['port'];
		if ( ! empty( $bwssmtp_options['SMTP']['authentication'] ) ) {
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
			$bwssmtp_options = get_option( 'bwssmtp_options' );
		if ( 'plugins.php' == $hook_suffix && ! $bwssmtp_options['settings_changed'] ) {
			if ( ! isset( $bstwbsftwppdtplgns_cookie_add ) ) {
					echo '<script type="text/javascript" src="' . plugins_url( 'bws_menu/js/c_o_o_k_i_e.js', __FILE__ ) . '"></script>';
					$bstwbsftwppdtplgns_cookie_add = true;
				} ?>
				<script type="text/javascript">
					(function($) {
						$(document).ready( function() {
							var hide_message = $.cookie( 'bwssmtp_hide_banner_on_plugin_page' );
							if ( "true" == hide_message ) {
								$( ".bwssmtp_message" ).css( "display", "none" );
							} else {
								$( ".bwssmtp_message" ).css( "display", "block" );
							};
							$( ".bwssmtp_close_icon" ).click( function() {
								$( ".bwssmtp_message" ).css( "display", "none" );
								$.cookie( "bwssmtp_hide_banner_on_plugin_page", "true", { expires: 32 } );
							});
						});
					})(jQuery);
				</script>
				<div class="updated bwssmtp_message" style="padding: 0; margin: 0; border: none; background: none;">
					<div class="bws_banner_on_plugin_page">
						<button class="bwssmtp_close_icon close_icon notice-dismiss bws_hide_settings_notice" title="<?php _e( 'Close notice', 'bws-smtp' ); ?>"></button>
						<div class="icon">
							<img title="" src="//ps.w.org/bws-smtp/assets/icon-128x128.png" alt="" />
						</div>
						<div class="text">
							<?php _e( 'Configure the "SMTP by BestWebSoft" plugin for sending email messages via SMTP', 'bws-smtp' ); ?></br>
							<a href="admin.php?page=bwssmtp_settings"><?php _e( 'Go to the settings', 'bws-smtp' ); ?></a>
						</div>
					</div>
				</div>
		<?php }
		if ( isset( $_REQUEST['page'] ) && 'bwssmtp_settings' == $_REQUEST['page'] )
			bws_plugin_suggest_feature_banner( $bwssmtp_plugin_info, 'bwssmtp_options', 'bws-smtp' );
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
