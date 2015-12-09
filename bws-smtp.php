<?php
/*
Plugin Name: SMTP by BestWebSoft
Plugin URI: http://bestwebsoft.com/products/
Description: This plugin introduces an easy way to configure sending email messages via SMTP.
Author: BestWebSoft
Text Domain: bws-smtp
Domain Path: /languages
Version: 1.0.5
Author URI: http://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2015  BestWebSoft  ( http://support.bestwebsoft.com )

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
		bws_general_menu();
		$hook = add_submenu_page( 'bws_plugins', 'SMTP', 'SMTP', 'manage_options', 'bwssmtp_settings', 'bwssmtp_settings_page' );
		add_action( "load-$hook", 'bwssmtp_screen_options' );
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
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $bwssmtp_plugin_info, '3.8', '3.2' );
	}
}

/* Plugin initialization in the Dashboard. */
if ( ! function_exists( 'bwssmtp_admin_init' ) ) {
	function bwssmtp_admin_init() {
		global $bws_plugin_info, $bwssmtp_plugin_info;

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '185', 'version' => $bwssmtp_plugin_info['Version'] );

		/* Call default options function */
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'bwssmtp_settings' )
			bwssmtp_default_options();
	}
}

/* Set default or get current options. */
if ( ! function_exists ( 'bwssmtp_default_options' ) ) {
	function bwssmtp_default_options() {
		global $bwssmtp_options, $bwssmtp_default_options, $bwssmtp_plugin_info;

		$bwssmtp_default_options = array(
			'plugin_option_version' 	=> $bwssmtp_plugin_info['Version'],
			'area'  					=> 'anywhere',
			'confirmed'     			=> false,
			'settings_changed'			=> false,
			'SMTP' 						=> array(
				'from_email'     => preg_replace( '|^(https?:\/\/)?(www\.)?([\w.]+)/?.*?$|u', 'wordpress@$3', strtolower( $_SERVER['SERVER_NAME'] ) ),
				'from_name'      => get_bloginfo( 'name' ),
				'host'           => 'localhost',
				'port'           => 25,
				'secure'         => 'none',
				'authentication' => 0,
				'username'       => '',
				'password'       => ''
			)
		);

		if ( ! get_option( 'bwssmtp_options' ) )
			add_option( 'bwssmtp_options', $bwssmtp_default_options );

		$bwssmtp_options = get_option( 'bwssmtp_options' );

		if ( ! isset( $bwssmtp_options['plugin_option_version'] ) || $bwssmtp_options['plugin_option_version'] != $bwssmtp_plugin_info['Version'] ) {
			if ( true == $bwssmtp_options['confirmed'] )
				$bwssmtp_options['settings_changed'] = true;

			$bwssmtp_options = array_merge( $bwssmtp_default_options, $bwssmtp_options );
			$bwssmtp_options['plugin_option_version'] = $bwssmtp_plugin_info['Version'];
			update_option( 'bwssmtp_options', $bwssmtp_options );
		}
	}
}

/* Add script and styles to the dashboard. */
if ( ! function_exists( 'bwssmtp_dashboard_script_styles' ) ) {
	function bwssmtp_dashboard_script_styles() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'bwssmtp_settings' ) {
			wp_enqueue_style( 'bwssmtp_stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
			wp_enqueue_script( 'bwssmtp_script', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ) );
		}
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
						'text' => sprintf( __( 'Email address %s in the field "%s" is not valid!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', stripslashes( esc_html( $_POST['bwssmtp_from_email'] ) ) ), sprintf( '<strong>%s</strong>', __( 'From Email', 'bws-smtp' ) ) )
					);
				}
			} else {
				$bwssmtp_notices['bwssmtp_from_email'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'From Email', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_from_name'] ) && empty( $_POST['bwssmtp_from_name'] ) ) {
				$bwssmtp_notices['bwssmtp_from_name'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'From Name', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_host'] ) && empty( $_POST['bwssmtp_host'] ) ) {
				$bwssmtp_notices['bwssmtp_host'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Host', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_port'] ) && empty( $_POST['bwssmtp_port'] ) ) {
				$bwssmtp_notices['bwssmtp_port'] = array(
					'type' => 'error',
					'text' => sprintf(	__( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Port', 'bws-smtp' ) ) )
				);
			} else {
				if ( ! preg_match( '/^\d+$/', $_POST['bwssmtp_port'] ) ) {
					$bwssmtp_notices['bwssmtp_port'] = array(
						'type' => 'error',
						'text' => sprintf( __( 'The field "%s" must contain numbers only!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Port', 'bws-smtp' ) ) )
					);
				}
			}

			if ( isset( $_POST['bwssmtp_authentication'] ) && isset( $_POST['bwssmtp_username'] ) && empty( $_POST['bwssmtp_username'] ) ) {
				$bwssmtp_notices['bwssmtp_username'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Username', 'bws-smtp' ) ) )
				);
			}

			if ( isset( $_POST['bwssmtp_authentication'] ) && isset( $_POST['bwssmtp_password'] ) && empty( $_POST['bwssmtp_password'] ) ) {
				$bwssmtp_notices['bwssmtp_password'] = array(
					'type' => 'error',
					'text' => sprintf( __( 'You have not filled the field "%s"!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'SMTP Password', 'bws-smtp' ) ) )
				);
			}

			/* Create new options. */
			$bwssmtp_new_options['area'] = isset( $_POST['bwssmtp_area'] ) ? stripslashes( esc_html( $_POST['bwssmtp_area'] ) ) : 'anywhere';
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
				$bwssmtp_options = array_merge( $bwssmtp_options, $bwssmtp_new_options );
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
					'text'  => sprintf( __( 'You have not entered an email address which you want to send a test email to!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
				);
			} elseif ( ! is_email( $bwssmtp_test_to ) ) {
				$bwssmtp_notices['bwssmtp_test_to'] = array(
					'type'  => 'error',
					'text'  => sprintf( __( 'Email address %s is not valid!', 'bws-smtp' ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
				);
			} else {
				require_once( ABSPATH . WPINC . '/class-phpmailer.php' );

				$bwssmtp_phpmailer = new PHPMailer();
				$bwssmtp_phpmailer->IsSMTP();
				$from_email = $bwssmtp_options['SMTP']['from_email'];
				$from_name  = $bwssmtp_options['SMTP']['from_name'];
				$bwssmtp_phpmailer->SetFrom( $from_email, $from_name );

				if ( $bwssmtp_options['SMTP']['secure'] !== 'none' ) {
					$bwssmtp_phpmailer->SMTPSecure = $bwssmtp_options['SMTP']['secure'];
				}

				$bwssmtp_phpmailer->Host = $bwssmtp_options['SMTP']['host'];
				$bwssmtp_phpmailer->Port = $bwssmtp_options['SMTP']['port'];

				if (  $bwssmtp_options['SMTP']['authentication'] == 1 ) {
					$bwssmtp_phpmailer->SMTPAuth = true;
					$bwssmtp_phpmailer->Username = $bwssmtp_options['SMTP']['username'];
					$bwssmtp_phpmailer->Password = $bwssmtp_options['SMTP']['password'];
				}

				$bwssmtp_phpmailer->CharSet = 'UTF-8';
				$bwssmtp_phpmailer->isHTML( false );
				$bwssmtp_phpmailer->Subject = sprintf( __( 'SMTP by BestWebSoft plugin: Test email to %s', 'bws-smtp' ), ' ' . $bwssmtp_test_to );
				$bwssmtp_phpmailer->MsgHTML( sprintf( __( 'Please, do not reply. This is a test email sent via SMTP by BestWebSoft plugin from %s.', 'bws-smtp' ), get_option( 'home' ) ) );
				$bwssmtp_phpmailer->AddAddress( $bwssmtp_test_to );

				if ( $bwssmtp_test_log == 1 ) {
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
						'text'  => sprintf( __( '%s: A test email was sent to %s.', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'Success', 'bws-smtp' ) ), sprintf( '<strong>%s</strong>', $bwssmtp_test_to ) )
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
			$bwssmtp_href = get_admin_url( null, 'admin.php?page=bwssmtp_settings' );
			wp_redirect( $bwssmtp_href );
		}

		/* Warn about different domain names. */
		if ( ! empty( $bwssmtp_options['SMTP']['from_email'] ) && is_email( $bwssmtp_options['SMTP']['from_email'] ) ) {
			$bwssmtp_from_email = explode( '@', $bwssmtp_options['SMTP']['from_email'] );
			$bwssmtp_from_email_host = $bwssmtp_from_email[ 1 ];
			if ( ! strpos( $bwssmtp_options['SMTP']['host'], $bwssmtp_from_email_host ) && ! $bwssmtp_options['confirmed'] ) {
				array_unshift( $bwssmtp_notices,
					array(
						'type'  => 'warning',
						'text'  => sprintf( __( 'A problem with email sending may occur, since most servers require domain name match in "%s" and "%s" fields.', 'bws-smtp' ), sprintf( '<strong>%s</strong>', __( 'From Email', 'bws-smtp' ) ), sprintf( '<strong>%s</strong>', __( 'SMTP Host', 'bws-smtp' ) ) )
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
			<h2 class="nav-tab-wrapper">
				<a class="nav-tab <?php if ( ! isset( $_GET['action'] ) ) echo ' nav-tab-active'; ?>" href="admin.php?page=bwssmtp_settings"><?php _e( 'Settings', 'bws-smtp' ); ?></a>
				<a class="nav-tab <?php if ( isset( $_GET['action'] ) && $_GET['action'] == 'test_email' ) echo ' nav-tab-active'; ?>" href="admin.php?page=bwssmtp_settings&action=test_email"><?php _e( 'Send A Test Email', 'bws-smtp' ); ?></a>
			</h2>
			<?php if ( ! $bwssmtp_options['settings_changed'] ) { ?>
				<div class="updated"><p><strong><?php _e( 'Configure plugin for sending email messages via SMTP. For more info see', 'bws-smtp' ); ?>&nbsp;<a target="_blank" href="https://docs.google.com/document/d/1zCvr7FarorqcggQC1PcyjHaxadrCgS3-CMMw3JsvW1M/edit#heading=h.jxgxn2x6c109"><?php _e( 'plugin instruction', 'bws-smtp' ); ?></a>.</strong></p></div>
			<?php }
			if ( ! empty( $bwssmtp_notices ) ) {
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
								<tr class="bwssmtp_settings_status" valign="top">
									<th scope="row">
										<label for="bwssmtp_from_email"><?php _e( 'Settings Status', 'bws-smtp' ); ?></label>
									</th>
									<td class="<?php echo ( $bwssmtp_options['confirmed'] ) ? 'bwssmtp_confirmed' : 'bwssmtp_not_confirmed'; ?>">
										<?php if ( $bwssmtp_options['confirmed'] ) {
											_e( 'Confirmed', 'bws-smtp' );
										} else {
											_e( 'Not confirmed', 'bws-smtp' ); ?>
											<span class="bwssmtp_tooltip"><?php printf( __( 'To confirm the settings, please send a test email, and then click "%s" button after successful sending.', 'bws-smtp' ), __( 'Settings Are Correct', 'bws-smtp' ) ); ?></span>
										<?php } ?>
									</td>
								</tr>
								<tr class="bwssmtp_settings_area" valign="top">
									<th scope="row">
										<label><?php _e( 'Where To Use', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<div>
											<input id="bwssmtp_area_where_selected" type='radio' name="bwssmtp_area" value="where_selected" <?php if ( $bwssmtp_options['area'] == "where_selected" ) echo 'checked="checked"'; ?> maxlength="250" /><label for="bwssmtp_area_where_selected"><?php _e( 'Where Selected', 'bws-smtp' ); ?></label>
										</div>
										<div>
											<input id="bwssmtp_area_anywhere" type='radio' name="bwssmtp_area" value="anywhere" <?php if ( $bwssmtp_options['area'] == "anywhere" ) echo 'checked="checked"'; ?> /><label for="bwssmtp_area_anywhere"><?php _e( 'Anywhere', 'bws-smtp' ); ?></label>
										</div>
									</td>
								</tr>
								<tr class="bwssmtp_settings_from_email" valign="top">
									<th scope="row">
										<label for="bwssmtp_from_email"><?php _e( 'From Email', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_from_email" <?php if ( array_key_exists( 'bwssmtp_from_email', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_email" value="<?php echo $bwssmtp_options['SMTP']['from_email']; ?>" maxlength="250" />
										<span class="bwssmtp_tooltip"><?php printf( __( 'Enter an email, which will be used in the message "%s" field.', 'bws-smtp' ), sprintf( '<span class="bwssmtp_strtolower">%s</span>', __( 'From Email', 'bws-smtp' ) ) ); ?></span>
										<span class="bwssmtp_tooltip"><?php printf( __( '(Most mail servers can change the email in the "%s" field)', 'bws-smtp' ), sprintf( '<span class="bwssmtp_strtolower">%s</span>', __( 'From Email', 'bws-smtp' ) ) ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_from_name" valign="top">
									<th scope="row">
										<label for="bwssmtp_from_name"><?php _e( 'From Name', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_from_name" <?php if ( array_key_exists( 'bwssmtp_from_name', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_from_name" value="<?php echo $bwssmtp_options['SMTP']['from_name']; ?>" maxlength="250" />
										<span class="bwssmtp_tooltip"><?php printf( __( 'Enter the name which will be used in the message "%s" field.', 'bws-smtp' ), sprintf( '<span class="bwssmtp_strtolower">%s</span>', __( 'From Name', 'bws-smtp' ) ) ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_host" valign="top">
									<th scope="row">
										<label for="bwssmtp_host"><?php _e( 'SMTP Host', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_host" <?php if ( array_key_exists( 'bwssmtp_host', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_host" value="<?php echo $bwssmtp_options['SMTP']['host']; ?>" maxlength="250"/>
										<span class="bwssmtp_tooltip"><?php _e( 'Enter mail server host name or IP address.', 'bws-smtp' ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_port" valign="top">
									<th scope="row">
										<label for="bwssmtp_port"><?php _e( 'SMTP Port', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_port" <?php if ( array_key_exists( 'bwssmtp_port', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="number" name="bwssmtp_port" value="<?php echo $bwssmtp_options['SMTP']['port']; ?>" min="1" max="65535" step="1"/>
										<span class="bwssmtp_tooltip"><?php _e( 'Enter the mail server port.', 'bws-smtp' ); ?></span>
										<span class="bwssmtp_tooltip"><?php _e( '(Most mail servers use port 465)', 'bws-smtp' ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_secure" valign="top">
									<th scope="row"><label><?php _e( 'SMTP Secure Connection', 'bws-smtp' ); ?></label></th>
									<td><fieldset>
										<div>
											<input id="bwssmtp_secure_none" type='radio' name="bwssmtp_secure" value="none" <?php if ( $bwssmtp_options['SMTP']['secure'] == "none" ) echo 'checked="checked"'; ?> /><label for="bwssmtp_secure_none"><?php _e( 'None', 'bws-smtp' ); ?></label>
										</div>
										<div>
											<input id="bwssmtp_secure_ssl" type='radio' name="bwssmtp_secure" value="ssl" <?php if ( $bwssmtp_options['SMTP']['secure'] == "ssl" ) echo 'checked="checked"'; ?> /><label for="bwssmtp_secure_ssl"><?php _e( 'SSL', 'bws-smtp' ); ?></label>
										<div>
										</div>
											<input id="bwssmtp_secure_tls" type='radio' name="bwssmtp_secure" value="tls" <?php if ( $bwssmtp_options['SMTP']['secure'] == "tls" ) echo 'checked="checked"'; ?> /><label for="bwssmtp_secure_tls"><?php _e( 'TLS', 'bws-smtp' ); ?></label>
										</div>
										<span class="bwssmtp_tooltip"><?php _e( 'Select the type of secure connection with the mail server.', 'bws-smtp' ); ?></span>
										<span class="bwssmtp_tooltip"><?php _e( '(Most mail servers use SSL connection)', 'bws-smtp' ); ?></span>
									</fieldset></td>
								</tr>
								<tr class="bwssmtp_settings_authentication" valign="top">
									<th scope="row"><label for="bwssmtp_authentication"><?php _e( 'SMTP Authentication', 'bws-smtp' ); ?></label></th>
									<td>
										<input id="bwssmtp_authentication" type="checkbox" name="bwssmtp_authentication" value="1" <?php if ( $bwssmtp_options['SMTP']['authentication'] == 1 ) echo 'checked="checked"'; ?> />
										<span class="bwssmtp_tooltip"><?php _e( 'Mark the checkbox if authentication is required on the mail server.', 'bws-smtp' ); ?></span>
										<span class="bwssmtp_tooltip"><?php _e( '(Most mail servers require entering username and password)', 'bws-smtp' ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_username bwssmtp_authentication_settings<?php if ( $bwssmtp_options['SMTP']['authentication'] != 1 ) echo ' bwssmtp_hidden"'; ?>" valign="top">
									<th scope="row">
										<label for="bwssmtp_username"><?php _e( 'SMTP Username', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_username" <?php if ( array_key_exists( 'bwssmtp_username', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" autocomplete="off" name="bwssmtp_username" value="<?php echo $bwssmtp_options['SMTP']['username']; ?>" maxlength="250" />
										<span class="bwssmtp_tooltip"><?php _e( 'Enter the username for authentication on the mail server.', 'bws-smtp' ); ?></span>
									</td>
								</tr>
								<tr class="bwssmtp_settings_password bwssmtp_authentication_settings<?php if ( $bwssmtp_options['SMTP']['authentication'] != 1 ) echo ' bwssmtp_hidden"'; ?>" valign="top">
									<th scope="row">
										<label for="bwssmtp_password"><?php _e( 'SMTP Password', 'bws-smtp' ); ?></label>
									</th>
									<td>
										<input id="bwssmtp_password" <?php if ( array_key_exists( 'bwssmtp_password', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="password" name="bwssmtp_password" autocomplete="off" value="<?php echo $bwssmtp_options['SMTP']['password']; ?>" maxlength="250" />
										<span class="bwssmtp_tooltip"><?php _e( 'Enter the password for authentication on the mail server.', 'bws-smtp' ); ?></span>
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
			} elseif ( $_GET['action'] == 'test_email' ) { ?>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">
								<label><?php _e( 'Current Settings', 'bws-smtp' ); ?></label>
							</th>
							<td>
								<table id="bwssmtp_current_settings">
									<tbody>
										<tr>
											<th><?php _e( 'From:', 'bws-smtp' ); ?></th>
											<td><?php echo $bwssmtp_options['SMTP']['from_name']; ?> &#60;<?php echo  $bwssmtp_options['SMTP']['from_email'] ?>&#62;</td>
										</tr>
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
											<td><?php echo ( $bwssmtp_options['SMTP']['secure'] == 'none' ) ? __( ucfirst( $bwssmtp_options['SMTP']['secure'] ), 'bws-smtp' ) : strtoupper( $bwssmtp_options['SMTP']['secure'] ); ?></td>
										</tr>
										<tr>
											<th><?php _e( 'SMTP Authentication', 'bws-smtp' ); ?>:</th>
											<td><?php ( $bwssmtp_options['SMTP']['authentication'] == 1 ) ? _e( 'Yes', 'bws-smtp' ) : _e( 'No', 'bws-smtp' ); ?></td>
										</tr>
										<?php if ( $bwssmtp_options['SMTP']['authentication'] == 1 ) { ?>
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
								<span class="bwssmtp_tooltip"><?php _e( 'These settings will be used when sending a test email.', 'bws-smtp' ); ?></span>
								<?php if ( isset( $bwssmtp_phpmailer ) && $bwssmtp_result == true ) { ?>
									<form id="bwssmtp_confirm_form" method="post" action="admin.php?page=bwssmtp_settings&action=test_email&noheader=true">
										<input id="bwssmtp_confirm_settings" class="button-secondary" type="submit" name="bwssmtp_confirm_settings" value="<?php _e( 'Settings Are Correct', 'bws-smtp' ); ?>">
										<?php wp_nonce_field( $plugin_basename, 'bwssmtp_nonce_confirm' ); ?>
									</form>
								<?php } ?>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row">
								<label for="bwssmtp_test_to"><?php _e( 'Send A Test Email To', 'bws-smtp' ); ?></label>
							</th>
							<td>
								<form id="bwssmtp_test_form" method="post" action="admin.php?page=bwssmtp_settings&action=test_email">
									<input id="bwssmtp_test_to" <?php if ( array_key_exists( 'bwssmtp_test_to', $bwssmtp_notices ) ) echo 'class="bwssmtp_error"'; ?> type="text" name="bwssmtp_test_to" value="<?php if ( isset( $bwssmtp_test_to ) ) echo $bwssmtp_test_to; ?>" maxlength="250" />
									<span class="bwssmtp_tooltip"><?php _e( 'Enter an email address which you want to send a test email to.', 'bws-smtp' ); ?></span>
									<p>
										<input id="bwssmtp_test_log" type="checkbox" name="bwssmtp_test_log" value="1" <?php if ( isset( $bwssmtp_test_log ) && $bwssmtp_test_log == 1 ) echo 'checked="checked"'; ?> />
										<label for="bwssmtp_test_log"><?php _e( 'Display log', 'bws-smtp' ); ?></label>
										<span class="bwssmtp_tooltip"><?php _e( 'Mark the checkbox, if you want to display the log of sending a test email.', 'bws-smtp' ); ?></span>
									</p>
									<input id="bwssmtp_test_send" class="button-secondary" type="submit" name="bwssmtp_test_send" value="<?php _e( 'Send A Test Email', 'bws-smtp' ) ?>" />
									<?php wp_nonce_field( $plugin_basename, 'bwssmtp_nonce_test' ); ?>
								</form>
							</td>
						</tr>
					</tbody>
				</table>
				<?php if ( isset( $bwssmtp_test_log ) && $bwssmtp_test_log == 1 && isset( $bwssmtp_phpmailer ) ) { ?>
					<div id="bwssmtp_log" class="bwssmtp_notice bwssmtp_notice_<?php echo ( $bwssmtp_result == true ) ? 'success' : 'error'; ?> bwssmtp_log_<?php echo ( $bwssmtp_result == true ) ? 'success' : 'error'; ?>">
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
		$from_email = $bwssmtp_options['SMTP']['from_email'];
		$from_name  = $bwssmtp_options['SMTP']['from_name'];
		$phpmailer->SetFrom( $from_email, $from_name );
		if ( $bwssmtp_options['SMTP']['secure'] !== 'none' ) {
			$phpmailer->SMTPSecure = $bwssmtp_options['SMTP']['secure'];
		}
		$phpmailer->Host = $bwssmtp_options['SMTP']['host'];
		$phpmailer->Port = $bwssmtp_options['SMTP']['port'];
		if (  $bwssmtp_options['SMTP']['authentication'] == 1 ) {
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
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=bwssmtp_settings">' . __( 'Settings', 'bws-smtp' ) . '</a>';
			$links[]	=	'<a href="http://wordpress.org/plugins/bws-smtp/faq/" target="_blank">' . __( 'FAQ', 'bws-smtp' ) . '</a>';
			$links[]	=	'<a href="http://support.bestwebsoft.com">' . __( 'Support', 'bws-smtp' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'bwssmtp_admin_notices' ) ) {
	function bwssmtp_admin_notices() {
		global $hook_suffix, $bstwbsftwppdtplgns_cookie_add, $bwssmtp_options;
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
							var hide_message  = $.cookie( 'bwssmtp_hide_banner_on_plugin_page' );
							if ( hide_message == "true" ) {
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
						<img class="bwssmtp_close_icon close_icon" title="" src="<?php echo plugins_url( 'bws_menu/images/close_banner.png', __FILE__ ); ?>" alt=""/>
						<div class="icon">
							<img title="" src="<?php echo plugins_url( 'bws_menu/icons/bws-smtp.png', __FILE__ ); ?>" alt="" />
						</div>
						<div class="text" style="margin-top: 32px;">
							<?php _e( 'Configure the "SMTP by BestWebSoft" plugin for sending email messages via SMTP', 'bws-smtp' ); ?>
						</div>
						<div class="button_div">
							<a class="button" href="admin.php?page=bwssmtp_settings"><?php _e( 'Go to the settings', 'bws-smtp' ); ?></a>
						</div>
					</div>
				</div>
		<?php }
	}
}

/* Screen option */
if ( ! function_exists( 'bwssmtp_screen_options' ) ) {
	function bwssmtp_screen_options() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'bwssmtp',
			'section' 		=> '200908825'
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
	}
}

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
/* Uninstall plugin. */
register_uninstall_hook( __FILE__, 'bwssmtp_uninstall' );