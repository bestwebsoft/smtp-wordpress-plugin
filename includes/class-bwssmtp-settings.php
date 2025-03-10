<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Bwssmtp_Settings_Tabs' ) ) {
	/**
	 * Class Bwssmtp_Settings_Tabs for Settings
	 */
	class Bwssmtp_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Max file size.
		 *
		 * @var string
		 */
		private $max_file_size;
		/**
		 * Flag for send mail.
		 *
		 * @var bool
		 */
		private $success_mail_send;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename Plugin basename.
		 */
		public function __construct( $plugin_basename ) {
			global $bwssmtp_options, $bwssmtp_plugin_info;

			$tabs = array(
				'settings' => array( 'label' => __( 'Settings', 'bws-smtp' ) ),
				'misc'     => array( 'label' => __( 'Misc', 'bws-smtp' ) ),
			);

			parent::__construct(
				array(
					'plugin_basename'    => $plugin_basename,
					'plugins_info'       => $bwssmtp_plugin_info,
					'prefix'             => 'bwssmtp',
					'default_options'    => bwssmtp_get_options_default(),
					'options'            => $bwssmtp_options,
					'is_network_options' => is_network_admin(),
					'tabs'               => $tabs,
					'wp_slug'            => 'bws-smtp',
					'link_pn'            => '185',
					'doc_link'           => 'https://bestwebsoft.com/documentation/smtp/smtp-user-guide/',
				)
			);
			add_action( get_parent_class( $this ) . '_display_second_postbox', array( $this, 'display_second_postbox' ) );

			if ( bwssmtp_return_bytes( ini_get( 'upload_max_filesize' ) ) >= bwssmtp_return_bytes( ini_get( 'post_max_size' ) ) ) {
				$this->max_file_size = ini_get( 'post_max_size' );
			} else {
				$this->max_file_size = ini_get( 'upload_max_filesize' );
			}

			$this->success_mail_send = true;
		}

		/**
		 * Save plugin options to the database
		 *
		 * @access public
		 * @return array    The action results
		 */
		public function save_options() {
			$message = '';
			$notice  = '';
			$error   = '';

			/* Send test email */
			if ( isset( $_POST['bwssmtp_test_send'] ) && check_admin_referer( $this->plugin_basename, 'bwssmtp_nonce_test' ) ) {
				$to = isset( $_POST['bwssmtp_test_to'] ) ? sanitize_email( wp_unslash( $_POST['bwssmtp_test_to'] ) ) : null;
				if ( ! empty( $to ) ) {
					$attachment = array();

					$subject       = sprintf( __( 'SMTP by BestWebSoft plugin: Test email to %s', 'bws-smtp' ), ' ' . $to );
					$email_message = sprintf( __( 'Please do not reply. This is a test email sent via SMTP by BestWebSoft plugin from %s.', 'bws-smtp' ), get_option( 'home' ) );
					$headers       = 'Content-type: text/html; charset=utf-8';

					if ( isset( $_FILES['bwssmtp_test_file_attach'] ) && '' !== $_FILES['bwssmtp_test_file_attach']['name'] ) {
						$bwssmtp_test_file_name     = sanitize_text_field( wp_unslash( $_FILES['bwssmtp_test_file_attach']['name'] ) );
						$bwssmtp_test_file_type     = sanitize_text_field( wp_unslash( $_FILES['bwssmtp_test_file_attach']['type'] ) );
						$bwssmtp_test_file_tmp_name = sanitize_text_field( wp_unslash( $_FILES['bwssmtp_test_file_attach']['tmp_name'] ) );
						$validate_file_type = wp_check_filetype( $bwssmtp_test_file_name );
						if ( false === $validate_file_type['type'] || false === $validate_file_type['ext'] ) {
							$error .= __( 'File type is not allowed.', 'bws-smtp' ) . '<br />';
						} elseif ( 0 === absint( $_FILES['bwssmtp_test_file_attach']['error'] ) ) {
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
							$file_ext = explode( '.', $bwssmtp_test_file_name );
							if ( ! in_array( $bwssmtp_test_file_type, $bwssmtp_mime_type ) || ! in_array( end( $file_ext ), array_keys( $bwssmtp_mime_type ) ) ) {
								$file_ext = explode( '.', $bwssmtp_test_file_name );
								$file_ext = end( $file_ext );
								$error   .= sprintf( __( 'It\'s forbidden to attach files with %s extension!', 'bws-smtp' ), $file_ext ) . '<br />';
							} else {
								$uploads  = wp_upload_dir();
								$new_file = $uploads['path'] . '/' . $bwssmtp_test_file_name;
								move_uploaded_file( $bwssmtp_test_file_tmp_name, $new_file );
								$attachment = array( $new_file );								
							}
						}
					}

					if ( '' === $error && wp_mail( $to, $subject, $email_message, $headers, $attachment ) ) {
						$message .= sprintf( __( 'Successfully: A test email has been sent to %s.', 'bws-smtp' ), $to ) . '<br />';

						if ( 0 === absint( $this->options['confirmed'] ) ) {
							$message .= '<a class="button-secondary" href="' . wp_nonce_url( 'admin.php?page=bwssmtp_settings&action=bwssmtp_nonce_confirm', 'bwssmtp_nonce_confirm' ) . '">' . __( 'Settings Are Correct', 'bws-smtp' ) . '</a>';
						}
					} else {
						$link_to_view_log        = isset( $_POST['bwssmtp_test_log'] ) ? sprintf( ' <a href="#postbox">%s</a>', __( 'View log', 'bws-smtp' ) ) : '';
						$error                  .= __( 'A test email was not sent.', 'bws-smtp' ) . $link_to_view_log . '<br />';
						$this->success_mail_send = false;
					}
				} else {
					$error .= __( 'You have not entered an email address which will receive your test email!', 'bws-smtp' ) . '<br />';
				}

				return compact( 'message', 'notice', 'error' );
			}

			$this->options['use_plugin_settings_from'] = isset( $_POST['bwssmtp_use_plugin_settings_from'] ) ? 1 : 0;

			$this->options['SMTP'] = array(
				'from_email'     => isset( $_POST['bwssmtp_from_email'] ) ? sanitize_email( wp_unslash( $_POST['bwssmtp_from_email'] ) ) : '',
				'from_name'      => isset( $_POST['bwssmtp_from_name'] ) ? sanitize_text_field( wp_unslash( $_POST['bwssmtp_from_name'] ) ) : '',
				'host'           => isset( $_POST['bwssmtp_host'] ) ? sanitize_text_field( wp_unslash( $_POST['bwssmtp_host'] ) ) : '',
				'secure'         => isset( $_POST['bwssmtp_secure'] ) && in_array( sanitize_text_field( wp_unslash( $_POST['bwssmtp_secure'] ) ), array( 'none', 'ssl', 'tls' ) ) ? sanitize_text_field( wp_unslash( $_POST['bwssmtp_secure'] ) ) : 'none',
				'port'           => isset( $_POST['bwssmtp_port'] ) ? absint( $_POST['bwssmtp_port'] ) : 0,
				'authentication' => isset( $_POST['bwssmtp_authentication'] ) ? 1 : 0,
				'username'       => isset( $_POST['bwssmtp_username'] ) ? sanitize_text_field( wp_unslash( $_POST['bwssmtp_username'] ) ) : '',
				'password'       => isset( $_POST['bwssmtp_password'] ) ? sanitize_text_field( wp_unslash( $_POST['bwssmtp_password'] ) ) : '',
			);

			if ( 1 === absint( $this->options['use_plugin_settings_from'] ) ) {
				if ( empty( $this->options['SMTP']['from_email'] ) ) {
					$error .= sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), __( 'From Email Address', 'bws-smtp' ) ) . '<br />';
				}
				if ( empty( $this->options['SMTP']['from_name'] ) ) {
					$error .= sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), __( 'From Name', 'bws-smtp' ) ) . '<br />';
				}
			}

			if ( empty( $this->options['SMTP']['host'] ) ) {
				$error .= sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), __( 'SMTP Host', 'bws-smtp' ) ) . '<br />';
			}

			if ( 1 === absint( $this->options['SMTP']['authentication'] ) ) {
				if ( empty( $this->options['SMTP']['username'] ) ) {
					$error .= sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), __( 'SMTP Username', 'bws-smtp' ) ) . '<br />';
				}
				if ( empty( $this->options['SMTP']['password'] ) ) {
					$error .= sprintf( __( 'You have not filled the "%s" field!', 'bws-smtp' ), __( 'SMTP Password', 'bws-smtp' ) ) . '<br />';
				}
			}

			if ( empty( $error ) ) {
				$this->options['confirmed'] = 0;
				update_option( 'bwssmtp_options', $this->options );
				$message .= __( 'Settings saved.', 'bws-smtp' );
			} else {
				$error .= __( 'Settings are not saved.', 'bws-smtp' ) . '<br />';
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display tab Settings
		 */
		public function tab_settings() {
			/* Confirm the correct settings. */
			if ( isset( $_GET['action'] ) && 'bwssmtp_nonce_confirm' === $_GET['action'] && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bwssmtp_nonce_confirm' ) ) {
				$this->options['confirmed'] = 1;
				update_option( 'bwssmtp_options', $this->options );
			} ?>
			<h3 class="bws_tab_label"><?php esc_html_e( 'SMTP Settings', 'bws-smtp' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th><?php esc_html_e( 'Status', 'bws-smtp' ); ?></th>
					<td class="<?php echo esc_attr( ( $this->options['confirmed'] ) ? 'bwssmtp_confirmed' : 'bwssmtp_not_confirmed' ); ?>">
						<?php
						if ( $this->options['confirmed'] ) {
							esc_html_e( 'Confirmed', 'bws-smtp' );
						} else {
							esc_html_e( 'Not confirmed', 'bws-smtp' );
							?>
							<br />
							<span class="bws_info"><?php esc_html_e( 'To confirm the settings, please send a test email, and then click "Settings Are Correct" button after successful sending.', 'bws-smtp' ); ?></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Test Email Address', 'bws-smtp' ); ?></th>
					<td>
						<input type="text" name="bwssmtp_test_to" value="" maxlength="250"><br /><br />
						<input id="bwssmtp_test_file_attach" type="file" name="bwssmtp_test_file_attach" /><br />
						<input type="hidden" id="bwssmtp_test_file_attach_size" value="<?php echo esc_attr( $this->max_file_size ); ?>">
						<span class="bws_info"><?php printf( esc_html__( 'Select a file for the test message. Max. upload file size - %s.', 'bws-smtp' ), esc_attr( $this->max_file_size ) ); ?></span><br /><br />
						<label><input type="checkbox" name="bwssmtp_test_log" value="1" /><?php esc_html_e( 'Log', 'bws-smtp' ); ?></label><br />
						<span class="bws_info"><?php esc_html_e( 'Enable to display log.', 'bws-smtp' ); ?></span><br /><br />
						<input id="bwssmtp_test_send" class="button-secondary" type="submit" name="bwssmtp_test_send" value="<?php esc_html_e( 'Send Now', 'bws-smtp' ); ?>">
						<?php wp_nonce_field( $this->plugin_basename, 'bwssmtp_nonce_test' ); ?>
						<div id="bwssmtp_error_to_show" class="bwssmtp_not_confirmed" style="display: none;">
							<p>
								<strong>
									<?php esc_html_e( 'The file size exceeds the limit allowed', 'bws-smtp' ); ?>
								</strong>
							</p>
						</div>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'From Fields Importance', 'bws-smtp' ); ?></th>
					<td>
						<label><input type="checkbox" name="bwssmtp_use_plugin_settings_from" class="bws_option_affect" data-affect-show=".bwssmtp_from_fields_show" value="1" <?php checked( $this->options['use_plugin_settings_from'] ); ?> /><span class="bws_info"><?php esc_html_e( 'Enable to use the “From” field values below. Display to use settings specified in other plugins.', 'bws-smtp' ); ?></span></label>
					</td>
				</tr>
				<tr class="bwssmtp_from_fields_show">
					<th><?php esc_html_e( 'From Email Address', 'bws-smtp' ); ?></th>
					<td>
						<input type="text" name="bwssmtp_from_email" value="<?php echo esc_attr( $this->options['SMTP']['from_email'] ); ?>" maxlength="250" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter an email, which will be used in the message "From" field.', 'bws-smtp' ); ?></span><br />
						<span class="bws_info"><?php esc_html_e( 'Most mail servers can change the email address in the message "From" field.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr class="bwssmtp_from_fields_show">
					<th><?php esc_html_e( 'From Name', 'bws-smtp' ); ?></th>
					<td>
						<input type="text" name="bwssmtp_from_name" value="<?php echo esc_attr( $this->options['SMTP']['from_name'] ); ?>" maxlength="250" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter the name which will be used in the message "From" field.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'SMTP Host', 'bws-smtp' ); ?></th>
					<td>
						<input type="text" name="bwssmtp_host" value="<?php echo esc_attr( $this->options['SMTP']['host'] ); ?>" maxlength="250" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter mail server host name or IP address.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'SMTP Connection Type', 'bws-smtp' ); ?></th>
					<td>
						<fieldset>
							<label><input type='radio' name="bwssmtp_secure" value="none" <?php checked( 'none', $this->options['SMTP']['secure'] ); ?> /><?php esc_html_e( 'None', 'bws-smtp' ); ?></label><br />
							<label><input type='radio' name="bwssmtp_secure" value="ssl" <?php checked( 'ssl', $this->options['SMTP']['secure'] ); ?> /><?php esc_html_e( 'SSL', 'bws-smtp' ); ?></label><br />
							<label><input type='radio' name="bwssmtp_secure" value="tls" <?php checked( 'tls', $this->options['SMTP']['secure'] ); ?> /><?php esc_html_e( 'TLS', 'bws-smtp' ); ?></label>
						</fieldset>
						<span class="bws_info"><?php esc_html_e( 'Select the type of secure connection with the mail server. Most mail servers use SSL connection.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'SMTP Port', 'bws-smtp' ); ?></th>
					<td>
						<input class="small-text" type="number" name="bwssmtp_port" value="<?php echo esc_attr( $this->options['SMTP']['port'] ); ?>" min="1" max="65535" step="1" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter the mail server port. Most mail servers use port 465.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'SMTP Authentication', 'bws-smtp' ); ?></th>
					<td>
						<label><input type="checkbox" name="bwssmtp_authentication" class="bws_option_affect" data-affect-show=".bwssmtp_authentication_show" value="1" <?php checked( $this->options['SMTP']['authentication'] ); ?> /><span class="bws_info"><?php esc_html_e( 'Enable to use the SMTP Authentication.', 'bws-smtp' ); ?></span></label>
					</td>
				</tr>
				<tr class="bwssmtp_authentication_show">
					<th><?php esc_html_e( 'SMTP Username', 'bws-smtp' ); ?></th>
					<td>
						<input type="text" name="bwssmtp_username" value="<?php echo esc_attr( $this->options['SMTP']['username'] ); ?>" maxlength="250" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter the username for authentication on the mail server.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
				<tr class="bwssmtp_authentication_show">
					<th><?php esc_html_e( 'SMTP Password', 'bws-smtp' ); ?></th>
					<td>
						<input type="password" name="bwssmtp_password" autocomplete="off" value="<?php echo esc_attr( $this->options['SMTP']['password'] ); ?>" maxlength="250" /><br />
						<span class="bws_info"><?php esc_html_e( 'Enter the password for authentication on the mail server.', 'bws-smtp' ); ?></span>
					</td>
				</tr>
			</table>
			<?php
		}

		/**
		 * Display new postbox
		 */
		public function display_second_postbox() {
			global $phpmailer;

			if ( isset( $_POST['bwssmtp_test_send'], $_POST['bwssmtp_test_log'], $phpmailer ) && check_admin_referer( $this->plugin_basename, 'bwssmtp_nonce_test' ) ) {
				?>
				<div id="postbox" class="postbox">
					<h3 class="hndle">
						<?php esc_html_e( 'Test Email Log', 'bws-smtp' ); ?>
					</h3>
					<div class="bwssmtp_log bwssmtp_log_<?php echo esc_attr( $this->success_mail_send ? 'success' : 'error' ); ?>">
						<div class="bwssmtp_log_stage"><?php esc_html_e( 'Sending results:', 'bws-smtp' ); ?></div>
						<div class="bwssmtp_log_result"><?php var_dump( $this->success_mail_send ); ?></div>
						<div class="bwssmtp_log_stage"><?php esc_html_e( 'SMTP log:', 'bws-smtp' ); ?></div>
						<pre class="bwssmtp_log_result"><?php var_dump( $phpmailer ); ?></pre>
					</div>
				</div>
				<?php
			}
		}
	}
}
