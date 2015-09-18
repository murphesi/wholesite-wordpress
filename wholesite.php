<?php
/**
 * Plugin Name: WholeSite
 * Short Name: wholesite
 * Description: Wordpress plugin to connect your WholeSite account.
 * Author: Mtex Media
 * Author URI: http://www.mtex.ca
 * Version: 0.0.6
 * Requires at least: 3.9
 * Tested up to: 4.2.x
 * Contributors: Chris Murphy
 * Requires: PHP 5 >= 5.3.0
 *  
 * 
 * 
 * Copyright (C) 2015 - Mtex Media Corporation - Chris Murphy
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses
 * 
 */
 
namespace WholeSite;
 
// define environment variables
define( 'WS_ENVIRONMENT', 'production' ); // [production|dev]
define( 'WS_VERSION', '0.0.6' );
define( 'WS_URI', plugin_dir_url( __FILE__ ) );
define( 'WS_PATH', plugin_dir_path( __FILE__ ) );
define( 'WS_ENDPOINT', 'https://api.wholesite.com/1.0' );

// include classes
require_once( WS_PATH . 'classes/transaction.php' );
require_once( WS_PATH . 'classes/utility.php' );
require_once( WS_PATH . 'classes/request.php' );
require_once( WS_PATH . 'classes/response.php' );
require_once( WS_PATH . 'classes/user.php' );

class WholeSite {
	
	function __construct() {
		// Admin hooks
		if( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		}

		add_action( 'wp_loaded', array( $this, 'loaded' ) );
	}
	
	/**
	 * Run stuff after Wordpress has loaded
	 */
	function loaded() {

		// If user registration post param has been set, forward form info to WholeSite
		if( isset( $_POST['wholesite_user_registration'] ) && $_POST['wholesite_user_registration'] ) {
			$response = $this->registerUser( $_POST );

			// Future: Log responses so we can view stats and errors
		}
	}
	
	/** PLUGIN SETUP **/
	
	/**
	 * Admin init hook
	 */
	function admin_init() {
		$this->register_settings();
	}
	
	/**
	 * Admin menu hook
	 */
	function admin_menu() {
		// add admin nav page
		add_menu_page( 'WholeSite Dashboard', 'WholeSite', 'activate_plugins', 'wholesite', array( $this, 'render_admin_page' ), 'dashicons-analytics', 30 );
		
		// email template config
		add_submenu_page( 'wholesite', 'Email Confirmation Configuration', 'Transaction Email Confirmations', 'activate_plugins', 'wholesite_email', array( $this, 'render_email_template_config_page' ) );  
	
		// user registration config
		add_submenu_page( 'wholesite', 'User Registration Configuration', 'User Registrations', 'activate_plugins', 'wholesite_registration', array( $this, 'render_user_registration_page' ) );  
	
		// add settings page
		add_submenu_page( 'wholesite', 'WholeSite Settings', 'Settings', 'activate_plugins', 'wholesite-settings', array( $this, 'render_settings_page' ) );  
	}
	
	/**
	 * Render admin page
	 */
	function render_admin_page() {
		?>
		<div class="wrap">
			<h2>WholeSite Dashboard</h2>
		</div>
		<?
	}
	
	/**
	 * Register settings, sections & fields
	 */
	function register_settings() {
		register_setting( 'wholesite_settings', 'wholesite_settings', array( $this, 'sanitize_settings' ) );
		register_setting( 'wholesite_templates', 'wholesite_templates', array( $this, 'sanitize_settings' ) );
		register_setting( 'wholesite_registrations', 'wholesite_registrations', array( $this, 'sanitize_settings' ) );
	
		add_settings_section( 'wholesite_main', 'Site Settings', array( $this, 'settings_help_site' ), 'wholesite-settings' );
		add_settings_field( 'site_id', 'Site ID', array( $this, 'render_site_id_setting'), 'wholesite-settings', 'wholesite_main' );
		add_settings_field( 'license_key', 'License Key', array( $this, 'render_license_key_setting') , 'wholesite-settings', 'wholesite_main' );
	

		add_settings_section( 'wholesite_email_confirmations', '', '__return_null', 'wholesite_email' );
		add_settings_field( 'success_confirmation', 'Enable', array( $this, 'render_confirmation_setting'), 'wholesite_email', 'wholesite_email_confirmations' );


		add_settings_section( 'wholesite_email_settings', 'Email Preferences', '__return_null', 'wholesite_email' );
		add_settings_field( 'email_settings_from_name', 'From Name', array( $this, 'render_email_setting_from_name'), 'wholesite_email', 'wholesite_email_settings' );
		add_settings_field( 'email_settings_from_email', 'From Email', array( $this, 'render_email_setting_from_email'), 'wholesite_email', 'wholesite_email_settings' );
		add_settings_field( 'email_settings_subject', 'Subject', array( $this, 'render_email_setting_subject'), 'wholesite_email', 'wholesite_email_settings' );


		add_settings_section( 'wholesite_email_templates', 'Template to Confirmation Mapping', array( $this, 'render_email_template_config' ), 'wholesite_email' );
		
		add_settings_section( 'wholesite_user_settings', 'Username Prefix', array( $this, 'user_reg_prefix_help' ), 'wholesite_registrations' );
		add_settings_field( 'user_registration_prefix', 'Prefix', array( $this, 'render_user_registration_prefix_setting'), 'wholesite_registrations', 'wholesite_user_settings' );
	
	}

	/**
	 * Get a list of available template files.
	 * Looks in {theme_folder}/wholesite/email-templates
	 * @return array
	 */
	function get_available_email_templates() {
		$templates = array();

		$template_path = get_template_directory() . '/wholesite/email-templates/';

		if( is_dir( $template_path ) ) {
			$files = scandir( $template_path );

			for( $i = 0; $i < count( $files ); $i++ ) {
				$file = $files[$i];

				if( stristr( $file, '.html' ) ) {
					$hash = md5( $file );

					$templates[$hash] = array( 
							'name' => $file,
							'path' => $template_path . $file,
							'hash' => $hash
						);
				}
			}

			if( count( $templates ) == 0 ) {
				echo 'No email templates found at:<br>"' . $template_path . '".'; 
			}
		}
		else {
			echo 'No email template directory found at:<br>"' . $template_path . '".';
		}

		return $templates;
	}
	
	/**
	 * Render settings page
	 */
	function render_settings_page() {
		?>
		<div class="wrap">
			<h2>WholeSite Settings</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wholesite_settings' );
				
				do_settings_sections( 'wholesite-settings' );
				
				submit_button(); 
				?>
			</form>
		</div>
		<?
	}
	
	/**
	 * Render help for user registration prefix
	 */
	function user_reg_prefix_help() {
		echo '<p>Set a prefix to apply to all user registrations.</p>'; 
	}

	/**
	 * Render help for site settings
	 */
	function settings_help_site() {
		echo '<p>Supply the Site ID and License provided by WholeSite.</p>';
	}
	
	/**
	 * Render Site ID Setting
	 */
	function render_site_id_setting() {
		$options = get_option( 'wholesite_settings' );
		$val = isset( $options['site_id'] ) ? $options['site_id'] : '';
		echo '<input id="site_id" type="text" class="regular-text" name="wholesite_settings[site_id]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Render User Registration Prefix Setting
	 */
	function render_user_registration_prefix_setting() {
		$options = get_option( 'wholesite_registrations' );
		$val = isset( $options['user_prefix'] ) ? $options['user_prefix'] : '';
		echo '<input id="user_prefix" type="text" class="regular-text" name="wholesite_registrations[user_prefix]" value="' . esc_attr( $val ) . '"/>';
	}

	/**
	 * Render section for email template configuration
	 */
	function render_email_template_config() {
		echo '<p>Choose a confirmation for available templates.</p>';

		// render available templates settings input fields
		$templates = $this->get_available_email_templates();

		foreach ( $templates as $template ) {
			$file = $template['name'];
			$hash = $template['hash'];

			add_settings_field( 'tpl-' . $hash . $i, $file, array( $this, 'render_email_template_config_setting' ) , 'wholesite_email', 'wholesite_email_templates', array( 'id' => $hash ) );
		}
	}

	/**
	 * Render user registration page
	 */
	function render_user_registration_page() {
		?>
		<div class="wrap">
			<h2>User Registration Configuration</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wholesite_registrations' );
				
				do_settings_sections( 'wholesite_registrations' );
				
				submit_button(); 
				?>
			</form>
		</div>
		<?
	}

	/**
	 * Render email template configuration page
	 */
	function render_email_template_config_page() {
		?>
		<div class="wrap">
			<h2>Transaction Email Confirmation Configuration</h2>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wholesite_templates' );
				
				do_settings_sections( 'wholesite_email' );
				
				submit_button(); 
				?>
			</form>
		</div>
		<?
	}
	
	/**
	 * Render License Key Setting
	 */
	function render_license_key_setting() {
		$options = get_option( 'wholesite_settings' );
		$val = isset( $options['license_key'] ) ? $options['license_key'] : '';
		echo '<input id="license_key" type="text" class="regular-text" name="wholesite_settings[license_key]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Render Email Template Config Setting
	 */
	function render_email_template_config_setting( $args ) {
		$options = get_option( 'wholesite_templates' );
		$id = $args['id'];
		$val = isset( $options['templates'][$id] ) ? $options['templates'][$id] : '';

		$template_path = get_template_directory() . '/wholesite/email-templates/';

		$keys = array();
		$settings_path = $template_path . 'settings.php';

		// Try and load email template settings file
		if ( file_exists( $settings_path ) ) {
			require_once( $settings_path );

			if ( isset( \Wholesite\EmailTemplates\Mapping::$KEY_TO_TEMPLATE_MAP ) ) {
				$keys = \Wholesite\EmailTemplates\Mapping::$KEY_TO_TEMPLATE_MAP;
			}
		}
		else {
			echo 'No key mapping file found at:<br>"' . $settings_path . '". <br>See the help folder for an example settings file.<br>';
		}

		echo '<select id="' . esc_attr( $id ) . '" name="wholesite_templates[templates][' . esc_attr( $id ) . ']"><option value="">-- None --</option>';

		foreach ( $keys as $key => $description ) {
			$selected = ( $val == $key ) ? 'selected' : '';
			echo '<option value="' . esc_attr( $key ) . '" ' . $selected . '>' . esc_html( $description ) . '</option>';
		}

		echo '</select>';
	}
	
	/**
	 * Render Email Confirmation Settings
	 */
	function render_confirmation_setting() {
		$options = get_option( 'wholesite_templates' );
		$checked = isset( $options['confirmations']['success-send'] ) ? 'checked' : '';

		echo '<label for="success_confirmation">';

		echo '<input id="success_confirmation" type="checkbox" name="wholesite_templates[confirmations][success-send]" value="1" '. $checked . ' />';

		echo 'Send all configured confirmations.</label>';
	}
	
	/**
	 * Render Email Settings: From Name
	 */
	function render_email_setting_from_name() {
		$options = get_option( 'wholesite_templates' );
		$val = isset( $options['settings']['from-name'] ) ? $options['settings']['from-name'] : '';
		echo '<input id="email_settings_from_name" type="text" class="regular-text" name="wholesite_templates[settings][from-name]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Render Email Settings: From Email
	 */
	function render_email_setting_from_email() {
		$options = get_option( 'wholesite_templates' );
		$val = isset( $options['settings']['from-email'] ) ? $options['settings']['from-email'] : '';
		echo '<input id="email_settings_from_email" type="text" class="regular-text" name="wholesite_templates[settings][from-email]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Render Email Settings: Subject
	 */
	function render_email_setting_subject() {
		$options = get_option( 'wholesite_templates' );
		$val = isset( $options['settings']['subject'] ) ? $options['settings']['subject'] : '';
		echo '<input id="email_settings_subject" type="text" class="regular-text" name="wholesite_templates[settings][subject]" value="' . esc_attr( $val ) . '"/>';
	}
	
	/**
	 * Sanitize settings
	 */
	function sanitize_settings( $input ) {
		$valid = array();

		// Site settings		
		if ( isset( $input['site_id'] ) ) {
			$valid['site_id'] = sanitize_text_field( $input['site_id'] );
		}
		
		if ( isset( $input['license_key'] ) ) {
			$valid['license_key'] = sanitize_text_field( $input['license_key'] );
		}

		if ( isset( $input['templates'] ) ) {
			$valid['templates'] = array_map( 'sanitize_text_field', $input['templates'] );
		}

		if ( isset( $input['confirmations'] ) ) {
			$valid['confirmations'] = array_map( 'sanitize_text_field', $input['confirmations'] );
		}

		if ( isset( $input['settings'] ) ) {
			$valid['settings'] = array_map( 'sanitize_text_field', $input['settings'] );
		}

		if ( isset( $input['user_prefix'] ) ) {
			$valid['user_prefix'] = str_replace( " ", '_', strtoupper( trim( preg_replace("/[^a-zA-Z0-9_\-\s]+/", '', sanitize_text_field( $input['user_prefix'] ) ) ) ) );
		}

		return $valid;
	}
	
	/**
	 * Check to see if the site and license has been configured
	 * @return boolean
	 */
	public static function isConfigured() {
		return ( \WholeSite\Utility::getSetting( 'site_id' ) && \WholeSite\Utility::getSetting( 'license_key' ) );
	}

	/**
	 * Send a confirmation email based on the provided key
	 * This will look for a corresponding template that has been assigned to handle this key
	 * @param  string $key    
	 * @param  array  $params 
	 */
	private function sendConfirmationEmail( $key, $params = array() ) {
		if ( !isset( $params['billEmail'] ) ) {
			return false;
		}

		$templateConfig = \WholeSite\Utility::getSetting( 'templates', 'wholesite_templates' );
				
		$templates = $this->get_available_email_templates();

		$confirmationTemplate = null;	

		foreach( $templateConfig as $hash => $templateKey ) {
			if ( $key == $templateKey ) {
				if ( file_exists( $templates[$hash]['path'] ) ) {
					$confirmationTemplate = file_get_contents( $templates[$hash]['path'] );

					// substitute placeholders with params
					$confirmationTemplate = preg_replace_callback( '/({{\$(?P<key>.*?)}})/im', function($m) use ($params) {
						$replaceKey = strtolower( trim( $m['key'] ) );

						$replacement = '~NOT FOUND: ' . $replaceKey . '~';

						if ( isset( $params[$replaceKey] ) ) {
							$replacement = trim( $params[$replaceKey] );
						}

						return $replacement;
					}, $confirmationTemplate );

					$settingsConfig = \WholeSite\Utility::getSetting( 'settings', 'wholesite_templates' );
			
					// send email
					add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );

					$headers = 'From: ' . $settingsConfig['from-name'] . ' <' . $settingsConfig['from-email'] . ">\r\n";

					wp_mail( $params['billEmail'], $settingsConfig['subject'], $confirmationTemplate, $headers );

					// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
					remove_filter( 'wp_mail_content_type', 'set_html_content_type' );

					return true;
				}
			}
		}

		return false;
	}

	function set_html_content_type() {
		return 'text/html';
	}
	
	
	/** FUNCTIONS **/
	
	/**
	 * Process a payment transaction
	 * @param array $params 
	 * @return object Response from transaction
	 */
	public function processTransaction( $params = array() ) {
		if( self::isConfigured() ) {
			$t = new \WholeSite\Transaction( $params );
			$response = $t->process();

			// If key provided send confirmation email if configured
			if( isset( $params['confirmationKey'] ) && $response->success == 1 ) {
				$confirmationConfig = \WholeSite\Utility::getSetting( 'confirmations', 'wholesite_templates' );
				if ( isset( $confirmationConfig['success-send'] ) && $confirmationConfig['success-send'] ) {
					$this->sendConfirmationEmail( $params['confirmationKey'], $params );
				}
			}

			return $response;
		}
		else {
			return new \WP_Error( 'error', __( 'Please configure WholeSite plugin. Go to \'WholeSite > Settings' ) );
		}
	}

	/**
	 * Register a new user
	 * @param  array  $params
	 * @return object Response
	 */
	public function registerUser( $params = array() ) {
		if( self::isConfigured() ) {
			$u = new \WholeSite\User( $params );
			$response = $u->register();

			return $response;
		}
		else {
			return new \WP_Error( 'error', __( 'Please configure WholeSite plugin. Go to \'WholeSite > Settings\'' ) );
		}
	}
}

$wholesite = new WholeSite();
